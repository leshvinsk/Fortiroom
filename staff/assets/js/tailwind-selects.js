(function (window, document, $) {
    if (!$) return;

    function closeAll(exceptMenu) {
        $('.tw-select-menu').each(function () {
            if (exceptMenu && this === exceptMenu) return;
            this.classList.add('hidden');
        });
    }

    function syncFromSelect(select) {
        var wrapper = select._twWrapper;
        if (!wrapper) return;
        var label = wrapper.querySelector('.tw-select-label');
        var option = select.options[select.selectedIndex];
        label.textContent = option ? option.text : '';
    }

    function buildOptionItem(select, option, menu) {
        var li = document.createElement('li');
        li.className = 'px-3 py-2 text-sm text-gray-700 cursor-pointer hover:bg-green-50';
        li.textContent = option.text;
        if (option.disabled) {
            li.className += ' opacity-50 cursor-not-allowed';
            return li;
        }
        li.addEventListener('click', function () {
            select.value = option.value;
            $(select).trigger('change');
            menu.classList.add('hidden');
        });
        return li;
    }

    function renderMenu(select, menu) {
        menu.innerHTML = '';
        for (var i = 0; i < select.options.length; i++) {
            menu.appendChild(buildOptionItem(select, select.options[i], menu));
        }
    }

    function enhanceSelect(select) {
        if (!select || select.dataset.twSelectEnhanced === '1') return;
        if (select.multiple) return;
        if (select.closest && select.closest('.flatpickr-calendar')) return;
        if (select.classList && select.classList.contains('flatpickr-monthDropdown-months')) return;

        var wrapper = document.createElement('div');
        wrapper.className = 'tw-select-root relative inline-block align-middle';

        var measuredWidth = select.getBoundingClientRect().width;
        if (measuredWidth > 0) {
            wrapper.style.width = measuredWidth + 'px';
        } else if (select.style.width) {
            wrapper.style.width = select.style.width;
        } else {
            wrapper.style.width = '100%';
        }

        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        select.classList.add('hidden');
        select.style.display = 'none';

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'w-full h-[38px] px-3 text-sm text-left text-gray-700 bg-white border border-gray-200 rounded-lg flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500';
        button.innerHTML = '<span class="tw-select-label truncate"></span><svg class="w-4 h-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"></path></svg>';

        var menu = document.createElement('ul');
        menu.className = 'tw-select-menu absolute left-0 right-0 mt-1 max-h-60 overflow-auto bg-white border border-gray-200 rounded-lg shadow-lg z-[1000] hidden';

        wrapper.appendChild(button);
        wrapper.appendChild(menu);

        select._twWrapper = wrapper;
        renderMenu(select, menu);
        syncFromSelect(select);

        button.addEventListener('click', function (e) {
            e.preventDefault();
            var opening = menu.classList.contains('hidden');
            if (opening) {
                closeAll(menu);
                menu.classList.remove('hidden');
            } else {
                menu.classList.add('hidden');
            }
        });

        $(select).on('change tw-select:sync', function () {
            syncFromSelect(select);
            renderMenu(select, menu);
        });

        select.dataset.twSelectEnhanced = '1';
    }

    function init(root) {
        var scope = root || document;
        var selects = scope.querySelectorAll ? scope.querySelectorAll('select') : [];
        for (var i = 0; i < selects.length; i++) {
            enhanceSelect(selects[i]);
        }
        if (scope.tagName === 'SELECT') {
            enhanceSelect(scope);
        }
    }

    if (!$.fn.__twSelectValPatchApplied) {
        var originalVal = $.fn.val;
        $.fn.val = function (value) {
            if (arguments.length === 0) return originalVal.call(this);
            var result = originalVal.call(this, value);
            this.filter('select[data-tw-select-enhanced="1"]').trigger('tw-select:sync');
            return result;
        };
        $.fn.__twSelectValPatchApplied = true;
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.tw-select-root')) {
            closeAll(null);
        }
    });

    if (window.MutationObserver) {
        var observer = new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                for (var j = 0; j < mutations[i].addedNodes.length; j++) {
                    var node = mutations[i].addedNodes[j];
                    if (!node || node.nodeType !== 1) continue;
                    if (node.tagName === 'OPTION' && node.parentElement && node.parentElement.tagName === 'SELECT') {
                        $(node.parentElement).trigger('tw-select:sync');
                        continue;
                    }
                    if (node.tagName === 'SELECT' || (node.querySelector && node.querySelector('select'))) {
                        init(node);
                    }
                }
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    window.initTailwindSelects = init;
    $(function () { init(document); });
})(window, document, window.jQuery);
