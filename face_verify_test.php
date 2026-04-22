<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'esp32_cam_registry.php';

$envPath = __DIR__ . DIRECTORY_SEPARATOR . '.env';
$env = cameraRegistryLoadEnv($envPath);

$defaultCaptureUrl = '';
$cameraDeviceId = trim((string) ($env['ESP32_CAM_DEVICE_ID'] ?? 'fortiroom-cam'));
$cameraRegistryEndpoint = 'esp32_cam_registry.php';
$registryResult = cameraRegistryLoadSelectedEntry($env, $cameraDeviceId);
$registryEntry = $registryResult['entry'];
if (cameraRegistryIsFresh($registryEntry)) {
    $captureUrl = trim((string) ($registryEntry['capture_url'] ?? ''));
    if ($captureUrl !== '') {
        $defaultCaptureUrl = $captureUrl;
    }
}
$threshold = (string) ($env['COMPRE_FACE_SIMILARITY_THRESHOLD'] ?? ($env['COMPREFACE_SIMILARITY_THRESHOLD'] ?? '0.75'));
$comprefaceReady = (($env['COMPRE_FACE_BASE_URL'] ?? $env['COMPREFACE_BASE_URL'] ?? '') !== '') && (($env['COMPRE_FACE_API_KEY'] ?? $env['COMPREFACE_API_KEY'] ?? '') !== '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORTIROOM Face Verify Test</title>
    <link rel="icon" href="images/FYP_Logo_small.png" type="image/icon type">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #eef3ea;
            --card: rgba(255, 255, 255, 0.9);
            --ink: #183126;
            --muted: #5c7465;
            --line: rgba(24, 49, 38, 0.12);
            --primary: #24513e;
            --primary-strong: #16392b;
            --danger: #a63f3f;
            --danger-soft: #f6e4e1;
            --success: #1f6a48;
            --success-soft: #e2f3e8;
            --shadow: 0 28px 80px rgba(22, 57, 43, 0.14);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Manrope', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(103, 157, 120, 0.35), transparent 34%),
                radial-gradient(circle at bottom right, rgba(36, 81, 62, 0.18), transparent 28%),
                linear-gradient(145deg, #f7fbf6 0%, #edf3eb 45%, #e2ebe0 100%);
        }

        .shell {
            width: min(1120px, calc(100% - 32px));
            margin: 32px auto;
            padding: 28px;
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(12px);
            box-shadow: var(--shadow);
        }

        .hero {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 24px;
            align-items: stretch;
        }

        .panel {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 18px 45px rgba(28, 55, 43, 0.08);
        }

        h1, h2, p {
            margin: 0;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(36, 81, 62, 0.08);
            color: var(--primary);
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .title {
            margin-top: 18px;
            font-size: clamp(32px, 5vw, 50px);
            line-height: 0.98;
            letter-spacing: -0.04em;
        }

        .subtitle {
            margin-top: 14px;
            max-width: 620px;
            font-size: 16px;
            line-height: 1.7;
            color: var(--muted);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 24px;
        }

        .stat {
            padding: 16px;
            border-radius: 18px;
            background: linear-gradient(180deg, #f8fbf8 0%, #eff5ef 100%);
            border: 1px solid var(--line);
        }

        .stat strong {
            display: block;
            font-size: 15px;
            margin-bottom: 6px;
        }

        .stat span {
            color: var(--muted);
            font-size: 14px;
            word-break: break-word;
        }

        form {
            display: grid;
            gap: 16px;
        }

        .field {
            display: grid;
            gap: 8px;
        }

        label {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--primary);
        }

        input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid var(--line);
            background: #fbfdfb;
            color: var(--ink);
            font: inherit;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        input:focus {
            outline: none;
            border-color: rgba(36, 81, 62, 0.5);
            box-shadow: 0 0 0 4px rgba(36, 81, 62, 0.12);
            transform: translateY(-1px);
        }

        .help {
            font-size: 13px;
            line-height: 1.6;
            color: var(--muted);
        }

        .btn {
            appearance: none;
            border: 0;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-strong) 100%);
            color: #fff;
            font: inherit;
            font-weight: 800;
            padding: 16px 18px;
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
            box-shadow: 0 14px 30px rgba(22, 57, 43, 0.24);
        }

        .btn:hover:not(:disabled) {
            transform: translateY(-1px);
        }

        .btn:disabled {
            opacity: 0.65;
            cursor: wait;
            box-shadow: none;
        }

        .result-wrap {
            margin-top: 24px;
            display: grid;
            gap: 20px;
        }

        .result-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 18px 20px;
            border-radius: 22px;
            background: #f8fbf7;
            border: 1px solid var(--line);
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 999px;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.01em;
        }

        .status.idle {
            background: #eef3ee;
            color: #365545;
        }

        .status.success {
            background: var(--success-soft);
            color: var(--success);
        }

        .status.fail {
            background: var(--danger-soft);
            color: var(--danger);
        }

        .meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .pill {
            padding: 10px 12px;
            border-radius: 999px;
            background: rgba(24, 49, 38, 0.06);
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .preview-card {
            overflow: hidden;
            border-radius: 22px;
            border: 1px solid var(--line);
            background: #f6faf6;
        }

        .preview-card header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--primary);
        }

        .preview-card img {
            display: block;
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            background: linear-gradient(135deg, #edf3ee, #dfeae1);
        }

        .preview-actions {
            display: flex;
            gap: 10px;
            padding: 14px 16px 16px;
            border-top: 1px solid var(--line);
            background: #f9fbf9;
        }

        .preview-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--line);
            color: var(--primary);
            background: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
        }

        .json-box {
            padding: 18px;
            border-radius: 22px;
            border: 1px solid var(--line);
            background: #122219;
            color: #d8ede1;
            font-size: 13px;
            line-height: 1.65;
            overflow: auto;
            max-height: 340px;
            white-space: pre-wrap;
            word-break: break-word;
        }

        @media (max-width: 900px) {
            .hero,
            .preview-grid {
                grid-template-columns: 1fr;
            }

            .shell {
                width: min(100% - 20px, 1120px);
                margin: 10px auto;
                padding: 16px;
                border-radius: 20px;
            }

            .panel {
                padding: 18px;
                border-radius: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="hero">
            <section class="panel">
                <span class="eyebrow">Face Verification Test</span>
                <h1 class="title">Take a photo from ESP32-CAM and compare it with the Supabase avatar.</h1>
                <p class="subtitle">
                    Click the button, the server will request a fresh image from the ESP32-CAM, fetch the latest avatar for the username from Supabase Storage, and ask Compreface if both faces match.
                </p>
                <div class="stats">
                    <div class="stat">
                        <strong>Supabase avatar source</strong>
                        <span>Bucket: <?php echo htmlspecialchars((string) ($env['AVATARS_BUCKET'] ?? 'avatars'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="stat">
                        <strong>Default ESP32 capture URL</strong>
                        <span><?php echo htmlspecialchars($defaultCaptureUrl !== '' ? $defaultCaptureUrl : 'Not set in .env', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="stat">
                        <strong>Compreface status</strong>
                        <span><?php echo $comprefaceReady ? 'Configured in .env' : 'Missing COMPRE_FACE_BASE_URL or COMPRE_FACE_API_KEY'; ?></span>
                    </div>
                    <div class="stat">
                        <strong>Similarity threshold</strong>
                        <span><?php echo htmlspecialchars($threshold, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>
            </section>

            <section class="panel">
                <form id="verifyForm">
                    <div class="field">
                        <label for="username">Username</label>
                        <input id="username" name="username" type="text" placeholder="Enter the user's username" required>
                        <div class="help">The API will look for the newest image inside the user's folder in Supabase Storage.</div>
                    </div>

                    <div class="field">
                        <label for="esp32_capture_url">ESP32 Capture URL</label>
                        <input id="esp32_capture_url" name="esp32_capture_url" type="url" value="<?php echo htmlspecialchars($defaultCaptureUrl, ENT_QUOTES, 'UTF-8'); ?>" placeholder="http://esp32.local/capture">
                        <div class="help">This now auto-updates from the live ESP32 registration when available, so you usually do not need to edit it manually.</div>
                    </div>

                    <div class="field">
                        <label for="avatar_url">Avatar URL Override</label>
                        <input id="avatar_url" name="avatar_url" type="url" placeholder="Optional: use a specific avatar URL instead of latest Supabase avatar">
                        <div class="help">Optional. Keep blank to automatically use the latest avatar in the user's Supabase folder.</div>
                    </div>

                    <button class="btn" id="verifyBtn" type="submit">Take Photo and Verify</button>
                </form>
            </section>
        </div>

        <div class="result-wrap">
            <div class="result-bar">
                <div id="statusBadge" class="status idle">Waiting for test run</div>
                <div class="meta">
                    <div class="pill">Similarity: <span id="similarityValue">-</span></div>
                    <div class="pill">Threshold: <span id="thresholdValue"><?php echo htmlspecialchars($threshold, ENT_QUOTES, 'UTF-8'); ?></span></div>
                    <div class="pill">User: <span id="userValue">-</span></div>
                </div>
            </div>

            <div class="preview-grid">
                <section class="preview-card">
                    <header>Supabase Avatar</header>
                    <img id="avatarPreview" alt="Avatar preview">
                    <div class="preview-actions">
                        <a id="avatarLink" class="preview-link" href="#" target="_blank" rel="noopener noreferrer">Open Avatar</a>
                    </div>
                </section>
                <section class="preview-card">
                    <header>ESP32 Captured Photo</header>
                    <img id="capturePreview" alt="Captured preview">
                    <div class="preview-actions">
                        <a id="captureLink" class="preview-link" href="#" target="_blank" rel="noopener noreferrer">Open Capture URL</a>
                    </div>
                </section>
            </div>

            <pre id="jsonOutput" class="json-box">No result yet.</pre>
        </div>
    </div>

    <script>
        const cameraRegistryConfig = {
            endpoint: <?php echo json_encode($cameraRegistryEndpoint, JSON_UNESCAPED_SLASHES); ?>,
            deviceId: <?php echo json_encode($cameraDeviceId, JSON_UNESCAPED_SLASHES); ?>
        };

        const form = document.getElementById('verifyForm');
        const verifyBtn = document.getElementById('verifyBtn');
        const statusBadge = document.getElementById('statusBadge');
        const similarityValue = document.getElementById('similarityValue');
        const thresholdValue = document.getElementById('thresholdValue');
        const userValue = document.getElementById('userValue');
        const avatarPreview = document.getElementById('avatarPreview');
        const capturePreview = document.getElementById('capturePreview');
        const avatarLink = document.getElementById('avatarLink');
        const captureLink = document.getElementById('captureLink');
        const jsonOutput = document.getElementById('jsonOutput');
        const captureUrlInput = document.getElementById('esp32_capture_url');

        let discoveryInFlight = null;
        let lastResolvedCaptureUrl = captureUrlInput.value.trim();

        function setStatus(type, message) {
            statusBadge.className = 'status ' + type;
            statusBadge.textContent = message;
        }

        function prettyResult(data) {
            const safe = { ...data };

            if (safe.avatar_preview) {
                safe.avatar_preview = '[inline image data omitted]';
            }

            if (safe.capture_preview) {
                safe.capture_preview = '[inline image data omitted]';
            }

            return JSON.stringify(safe, null, 2);
        }

        function withCacheBuster(url) {
            if (!url) {
                return '#';
            }
            const separator = url.includes('?') ? '&' : '?';
            return url + separator + '_ts=' + Date.now();
        }

        function isLikelyMdnsUrl(url) {
            return /\.local(?::\d+)?(?:\/|$)/i.test(url || '');
        }

        async function tryReachHealth(baseUrl, timeoutMs = 1500) {
            if (!baseUrl) {
                return false;
            }

            const controller = new AbortController();
            const timer = setTimeout(() => controller.abort(), timeoutMs);

            try {
                const response = await fetch(withCacheBuster(baseUrl + '/health'), {
                    cache: 'no-store',
                    signal: controller.signal
                });
                if (!response.ok) {
                    return false;
                }
                const data = await response.json();
                return data && data.status === 'ok';
            } catch (error) {
                return false;
            } finally {
                clearTimeout(timer);
            }
        }

        async function discoverEsp32CaptureUrl(force = false) {
            const currentCaptureUrl = captureUrlInput.value.trim();
            const currentBaseUrl = getEsp32BaseUrl();

            if (!force && discoveryInFlight) {
                return discoveryInFlight;
            }

            discoveryInFlight = (async () => {
                const registryUrl = cameraRegistryConfig.endpoint
                    + '?_ts=' + Date.now()
                    + '&device_id=' + encodeURIComponent(cameraRegistryConfig.deviceId);
                const response = await fetch(registryUrl, {
                    cache: 'no-store'
                });

                if (!response.ok) {
                    throw new Error('Could not load the ESP32 camera registry');
                }

                const data = await response.json();
                const registryCaptureUrl = data && data.success && data.found && data.registration
                    ? String(data.registration.capture_url || '').trim()
                    : '';

                if (registryCaptureUrl) {
                    captureUrlInput.value = registryCaptureUrl;
                    captureLink.href = withCacheBuster(registryCaptureUrl);
                    lastResolvedCaptureUrl = registryCaptureUrl;
                    return registryCaptureUrl;
                }

                if (!force && currentCaptureUrl && !isLikelyMdnsUrl(currentCaptureUrl)) {
                    lastResolvedCaptureUrl = currentCaptureUrl;
                    return currentCaptureUrl;
                }

                if (!force && currentBaseUrl && await tryReachHealth(currentBaseUrl)) {
                    lastResolvedCaptureUrl = currentCaptureUrl;
                    return currentCaptureUrl;
                }

                if (currentCaptureUrl) {
                    lastResolvedCaptureUrl = currentCaptureUrl;
                    return currentCaptureUrl;
                }

                throw new Error('Could not automatically determine the ESP32-CAM capture URL');
            })();

            try {
                return await discoveryInFlight;
            } finally {
                discoveryInFlight = null;
            }
        }

        function blobToDataUrl(blob) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = () => reject(new Error('Failed to read captured image'));
                reader.readAsDataURL(blob);
            });
        }

        async function fetchCaptureImageData(captureUrl) {
            if (!captureUrl) {
                throw new Error('ESP32 capture URL is required');
            }

            const response = await fetch(withCacheBuster(captureUrl), {
                cache: 'no-store'
            });

            if (!response.ok) {
                throw new Error('Failed to capture image from ESP32-CAM');
            }

            const blob = await response.blob();
            return blobToDataUrl(blob);
        }

        let isVerifying = false;

        async function runVerification(triggerSource = 'manual', captureUrlOverride = '') {
            if (isVerifying) {
                return;
            }

            const payload = {
                username: form.username.value.trim(),
                esp32_capture_url: captureUrlOverride || form.esp32_capture_url.value.trim(),
                avatar_url: form.avatar_url.value.trim()
            };

            if (!payload.username) {
                setStatus('fail', 'Username is required');
                return;
            }

            isVerifying = true;
            verifyBtn.disabled = true;
            setStatus('idle', triggerSource === 'physical'
                ? 'Physical button pressed. Taking photo and verifying...'
                : 'Requesting photo from ESP32-CAM and verifying...');
            similarityValue.textContent = '-';
            userValue.textContent = payload.username;
            jsonOutput.textContent = 'Running verification...';
            avatarPreview.removeAttribute('src');
            capturePreview.removeAttribute('src');
            avatarLink.href = '#';
            captureLink.href = withCacheBuster(payload.esp32_capture_url);

            try {
                payload.esp32_capture_url = captureUrlOverride || await discoverEsp32CaptureUrl();
                captureLink.href = withCacheBuster(payload.esp32_capture_url);
                payload.capture_image_data = await fetchCaptureImageData(payload.esp32_capture_url);

                const response = await fetch('face_verify_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                jsonOutput.textContent = prettyResult(data);

                if (data.avatar_preview) {
                    avatarPreview.src = data.avatar_preview;
                }

                if (data.capture_preview) {
                    capturePreview.src = data.capture_preview;
                }

                if (data.avatar_url) {
                    avatarLink.href = data.avatar_url;
                }

                if (data.esp32_capture_url) {
                    captureLink.href = withCacheBuster(data.esp32_capture_url);
                }

                if (!response.ok || !data.success) {
                    setStatus('fail', data.error || data.message || 'Photo not verified');
                    similarityValue.textContent = data.similarity ?? '-';
                    thresholdValue.textContent = data.threshold ?? thresholdValue.textContent;
                    return;
                }

                setStatus(data.verified ? 'success' : 'fail', data.message || (data.verified ? 'Photo verified' : 'Photo not verified'));
                similarityValue.textContent = data.similarity !== null && data.similarity !== undefined
                    ? Number(data.similarity).toFixed(4)
                    : '-';
                thresholdValue.textContent = data.threshold ?? thresholdValue.textContent;
                userValue.textContent = data.username || payload.username;

            } catch (error) {
                setStatus('fail', 'Request failed. Check PHP, network access, and endpoint configuration.');
                jsonOutput.textContent = error && error.message ? error.message : String(error);
            } finally {
                isVerifying = false;
                verifyBtn.disabled = false;
            }
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            runVerification();
        });

        function getEsp32BaseUrl() {
            const captureUrl = form.esp32_capture_url.value.trim();

            if (!captureUrl) {
                return '';
            }

            try {
                return new URL(captureUrl).origin;
            } catch (error) {
                return '';
            }
        }

        let buttonPollInFlight = false;

        async function pollPhysicalButton() {
            if (buttonPollInFlight || isVerifying) {
                return;
            }

            try {
                await discoverEsp32CaptureUrl();
            } catch (error) {
                return;
            }

            const esp32BaseUrl = getEsp32BaseUrl();

            if (!esp32BaseUrl) {
                return;
            }

            buttonPollInFlight = true;

            try {
                const response = await fetch(esp32BaseUrl + '/button?_ts=' + Date.now(), {
                    cache: 'no-store'
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();

                if (data.pressed) {
                    await runVerification('physical', data.capture_url || '');
                }
            } catch (error) {
                // Keep polling quiet so the manual software button still works if the ESP32 is offline.
            } finally {
                buttonPollInFlight = false;
            }
        }

        discoverEsp32CaptureUrl().catch(() => {});
        setInterval(pollPhysicalButton, 250);
    </script>
</body>
</html>
