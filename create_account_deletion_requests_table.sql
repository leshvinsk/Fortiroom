CREATE TABLE IF NOT EXISTS public.account_deletion_requests (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
    status VARCHAR(50) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'declined', 'in_progress', 'cancelled', 'completed')),
    requested_at TIMESTAMPTZ DEFAULT NOW(),
    reviewed_at TIMESTAMPTZ,
    reviewed_by UUID REFERENCES auth.users(id) ON DELETE SET NULL,
    deletion_started_at TIMESTAMPTZ,
    deletion_scheduled_at TIMESTAMPTZ, -- 30 days from approval
    reason TEXT,
    admin_notes TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Create indexes for faster queries
CREATE INDEX IF NOT EXISTS idx_account_deletion_requests_user_id ON public.account_deletion_requests(user_id);
CREATE INDEX IF NOT EXISTS idx_account_deletion_requests_status ON public.account_deletion_requests(status);
CREATE INDEX IF NOT EXISTS idx_account_deletion_requests_requested_at ON public.account_deletion_requests(requested_at);
CREATE INDEX IF NOT EXISTS idx_account_deletion_requests_deletion_scheduled_at ON public.account_deletion_requests(deletion_scheduled_at);

-- Add comments
COMMENT ON TABLE public.account_deletion_requests IS 'Stores account deletion requests from users. Status flow: pending -> approved/declined -> in_progress (if approved) -> completed (after 30 days) or cancelled (if user logs in during 30-day period)';
COMMENT ON COLUMN public.account_deletion_requests.status IS 'Status of the deletion request: pending (awaiting admin review), approved (admin approved), declined (admin declined), in_progress (30-day deletion period), cancelled (user logged in during 30-day period), completed (account deleted after 30 days)';
COMMENT ON COLUMN public.account_deletion_requests.deletion_scheduled_at IS 'The date and time when the account will be deleted (30 days after approval). If user logs in before this date, the deletion is cancelled.';
COMMENT ON COLUMN public.account_deletion_requests.deletion_started_at IS 'The date and time when the deletion process started (when admin approved the request).';

-- Trigger to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_account_deletion_requests_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_account_deletion_requests_updated_at
    BEFORE UPDATE ON public.account_deletion_requests
    FOR EACH ROW
    EXECUTE FUNCTION update_account_deletion_requests_updated_at();

-- Row Level Security (RLS) Policies
ALTER TABLE public.account_deletion_requests ENABLE ROW LEVEL SECURITY;

-- Policy: Users can view their own deletion requests
CREATE POLICY "Users can view their own deletion requests"
    ON public.account_deletion_requests
    FOR SELECT
    USING (auth.uid() = user_id);

-- Policy: Users can insert their own deletion requests
CREATE POLICY "Users can insert their own deletion requests"
    ON public.account_deletion_requests
    FOR INSERT
    WITH CHECK (auth.uid() = user_id);

-- Helper function to check if current user is admin or staff
-- This function accesses the JWT token to check user_metadata.role
-- Note: This function checks the JWT claims directly, not the auth.users table
CREATE OR REPLACE FUNCTION public.is_admin_or_staff()
RETURNS BOOLEAN AS $$
DECLARE
    jwt_claims JSONB;
    user_role TEXT;
BEGIN
    -- Get JWT claims
    jwt_claims := auth.jwt();
    
    -- Check if JWT is available
    IF jwt_claims IS NULL THEN
        RETURN FALSE;
    END IF;
    
    -- Extract role from user_metadata in JWT
    -- JWT structure: { "user_metadata": { "role": "admin" } }
    user_role := jwt_claims->'user_metadata'->>'role';
    
    -- Return true if role is admin or staff
    RETURN (user_role = 'admin' OR user_role = 'staff');
EXCEPTION
    WHEN OTHERS THEN
        -- If any error occurs (e.g., JWT not available, invalid structure), return false
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER STABLE;

-- Policy: Admin and staff can view all deletion requests
-- Note: If RLS policies don't work, use the PHP endpoint get_deletion_requests.php instead
CREATE POLICY "Admin and staff can view all deletion requests"
    ON public.account_deletion_requests
    FOR SELECT
    USING (public.is_admin_or_staff());

-- Policy: Admin and staff can update deletion requests
CREATE POLICY "Admin and staff can update deletion requests"
    ON public.account_deletion_requests
    FOR UPDATE
    USING (public.is_admin_or_staff());

-- Policy: Admin and staff can delete deletion requests (for cleanup)
CREATE POLICY "Admin and staff can delete deletion requests"
    ON public.account_deletion_requests
    FOR DELETE
    USING (public.is_admin_or_staff());

