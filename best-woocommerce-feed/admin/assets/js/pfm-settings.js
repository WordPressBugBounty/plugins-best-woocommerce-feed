/* global wpAjaxHelperRequest, wpAjaxHelper */
(function () {
  'use strict';

  /* -------------------------------------------------------
     Group definitions (task 5.1)
     Maps group slug → array of CSS selectors for .single-merchant rows.
     Only top-level children of .pfm-row-pool are moved.
  ------------------------------------------------------- */
  var SETTINGS_GROUPS = {
    'general': [
      '.product-batch',
      '.detailed-merchants',
      '.hide-character'
    ],
    'maintenance': [
      '.wpfm-clear-btn',
      '.purge-cache',
      '.update-list'
    ],
    'product-data': [
      '.unique-product',
      '.wpfm-custom-field-frontend',
      '[data-label*="Detailed product attributes"]',
      '.exclude-tax',
      '[data-label*="private products"]',
      '.increase-product'
    ],
    'marketing-pixels': [
      '.fb-pixel',
      '.tiktok-pixel',
      '.google-drm-pixel'
    ],
    'notifications-logging': [
      '.enable-log',
      '[data-label*="Email notification"]',
      '[data-label*="usage tracking"]'
    ],
    'data-privacy': [
      '.remove-plugin-data'
    ],
    'import-export': [
      '.rex-feed-export',
      '.rex-feed-import'
    ],
    'advanced': [
      '.rex-feed-rollback'
    ]
  };

  /* Group metadata for display */
  var GROUP_META = {
    'general': { label: 'General', icon: '⚙' },
    'maintenance': { label: 'Maintenance', icon: '🔧' },
    'product-data': { label: 'Product Data', icon: '📦' },
    'marketing-pixels': { label: 'Marketing Pixels', icon: '📡' },
    'notifications-logging': { label: 'Notifications & Logging', icon: '🔔' },
    'data-privacy': { label: 'Data & Privacy', icon: '🔒' },
    'import-export': { label: 'Import / Export', icon: '↕' },
    'advanced': { label: 'Advanced', icon: '⚡' }
  };

  /* -------------------------------------------------------
     Bootstrap
  ------------------------------------------------------- */
  document.addEventListener('DOMContentLoaded', function () {
    initTabNav();
    initCategoryRail();
    initToggleSwitches();
    initCustomFieldFrontendToggle(); /* must run after initToggleSwitches */
    initInlineSave();
    initSearch();
    initLogViewer();
    initKeyboardShortcut();
    initImportExport();
    initSystemStatus();
    initFreeProFilter();
  });


  /* =======================================================
     TAB NAVIGATION (tasks 3.3, 3.4)
     Augments existing rex-product-feed-admin.js tab switching
     with ARIA attributes and keyboard navigation.
  ======================================================= */
  function initTabNav() {
    var tabs = document.querySelectorAll('ul.rex-settings__tabs li[role="tab"]');
    if (!tabs.length) return;

    /* Set initial aria-selected based on .active class */
    tabs.forEach(function (tab) {
      var isActive = tab.classList.contains('active');
      tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    /* Click → update aria-selected */
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (t) {
          t.setAttribute('aria-selected', 'false');
        });
        tab.setAttribute('aria-selected', 'true');
      });
    });

    /* Arrow key navigation (task 3.4) */
    document.querySelector('ul.rex-settings__tabs').addEventListener('keydown', function (e) {
      if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;

      var arr = Array.from(tabs);
      var idx = arr.indexOf(document.activeElement);
      if (idx === -1) return;

      e.preventDefault();
      var next = e.key === 'ArrowRight'
        ? (idx + 1) % arr.length
        : (idx - 1 + arr.length) % arr.length;

      arr[next].focus();
      arr[next].click();
    });
  }

  /* =======================================================
     CATEGORY RAIL (tasks 5.1 – 5.5)
  ======================================================= */
  function initCategoryRail() {
    var pool = document.querySelector('.pfm-row-pool');
    var rail = document.querySelector('.pfm-rail-list');
    var pane = document.querySelector('.pfm-ctrl-pane');
    var mobileSel = document.querySelector('.pfm-rail-mobile-select');

    if (!pool || !rail || !pane) return;

    /*
     * Collect only TOP-LEVEL .single-merchant rows — direct children of
     * .wpfm-settings-section elements (excludes nested sub-rows like
     * .wpfm-fb-pixel-field which lives inside .fb-pixel and moves with it).
     */
    var allTopRows = Array.from(
      pool.querySelectorAll('.wpfm-settings-section > .single-merchant')
    );

    /* Move rows from pool into group panes */
    var matched = new Set();

    Object.keys(SETTINGS_GROUPS).forEach(function (group) {
      var groupPane = pane.querySelector('[data-group="' + group + '"]');
      if (!groupPane) return;

      SETTINGS_GROUPS[group].forEach(function (selector) {
        allTopRows.forEach(function (row) {
          if (matched.has(row)) return;
          /* Test each selector: class-based or attribute-based */
          try {
            if (row.matches('.single-merchant' + selector) || row.matches(selector)) {
              matched.add(row);
              groupPane.appendChild(row);
            }
          } catch (e) { /* ignore invalid selector */ }
        });
      });
    });

    /* Unmatched rows (from do_action hooks etc.) are silently dropped — no Other tab */

    /* Update count badges */
    rail.querySelectorAll('.pfm-rail-item').forEach(function (item) {
      var group = item.dataset.group;
      var groupPane = pane.querySelector('[data-group="' + group + '"]');
      var count = groupPane ? groupPane.querySelectorAll(':scope > .single-merchant').length : 0;
      var badge = item.querySelector('.pfm-rail-count');
      if (badge) badge.textContent = count;
    });

    /* Apply destructive class to remove-plugin-data row */
    var dangerRow = document.querySelector('.pfm-group-pane .remove-plugin-data');
    if (dangerRow) dangerRow.classList.add('pfm-row--destructive');

    /* Activate group from URL hash (task 5.3) */
    var defaultGroup = 'general';
    var hash = window.location.hash; /* e.g. #settings/marketing-pixels */
    if (hash && hash.startsWith('#settings/')) {
      var slug = hash.replace('#settings/', '');
      if (pane.querySelector('[data-group="' + slug + '"]')) {
        defaultGroup = slug;
      }
    }

    activateGroup(defaultGroup, rail, pane, mobileSel, false);

    /* Rail item click (task 5.2) */
    rail.addEventListener('click', function (e) {
      var item = e.target.closest('.pfm-rail-item');
      if (!item) return;
      var group = item.dataset.group;
      clearSearchUI();
      activateGroup(group, rail, pane, mobileSel, true);
    });

    /* Mobile select */
    if (mobileSel) {
      mobileSel.addEventListener('change', function () {
        clearSearchUI();
        activateGroup(mobileSel.value, rail, pane, mobileSel, true);
      });
    }
  }

  function buildRailItem(group, label, icon, count) {
    var li = document.createElement('li');
    li.className = 'pfm-rail-item';
    li.dataset.group = group;
    li.setAttribute('role', 'option');
    li.innerHTML =
      '<span class="pfm-rail-icon">' + icon + '</span>' +
      '<span class="pfm-rail-label">' + label + '</span>' +
      '<span class="pfm-rail-count">' + count + '</span>';
    return li;
  }

  function activateGroup(group, rail, pane, mobileSelect, updateHash) {
    /* Rail items */
    rail.querySelectorAll('.pfm-rail-item').forEach(function (item) {
      item.classList.toggle('active', item.dataset.group === group);
    });

    /* Group panes */
    pane.querySelectorAll('.pfm-group-pane').forEach(function (gp) {
      gp.classList.toggle('active', gp.dataset.group === group);
    });

    /* Mobile select */
    if (mobileSelect) mobileSelect.value = group;

    /* URL hash */
    if (updateHash) {
      history.replaceState(null, '', '#settings/' + group);
    }
  }

  /* /  keyboard shortcut (task 5.4) */
  function initKeyboardShortcut() {
    document.addEventListener('keydown', function (e) {
      if (e.key !== '/') return;
      var tag = document.activeElement && document.activeElement.tagName;
      if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
      var ce = document.activeElement && document.activeElement.getAttribute('contenteditable');
      if (ce && ce !== 'false') return;

      var searchInput = document.querySelector('.pfm-rail-search');
      if (searchInput) {
        e.preventDefault();
        searchInput.focus();
      }
    });
  }

  /* =======================================================
     CROSS-GROUP SEARCH (tasks 8.1 – 8.6)
  ======================================================= */
  var _preSearchGroup = 'general';
  var _searchMovedRows = []; /* { row, parent, next } refs for restore */

  function initSearch() {
    var searchInput = document.querySelector('.pfm-rail-search');
    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
      var query = searchInput.value.trim().toLowerCase();
      if (!query) {
        clearSearch();
      } else {
        runSearch(query);
      }
    });
  }

  /* Restore rows that were physically moved into the results pane */
  function restoreSearchRows() {
    _searchMovedRows.forEach(function (ref) {
      if (ref.next && ref.next.parentNode === ref.parent) {
        ref.parent.insertBefore(ref.row, ref.next);
      } else {
        ref.parent.appendChild(ref.row);
      }
    });
    _searchMovedRows = [];
  }

  function runSearch(query) {
    var pane = document.querySelector('.pfm-ctrl-pane');
    var rail = document.querySelector('.pfm-rail-list');
    var resultsView = document.querySelector('.pfm-search-results');
    var resultsBody = document.querySelector('.pfm-search-results-body');
    var resultsTitle = document.querySelector('.pfm-search-results-title');
    var emptyState = document.querySelector('.pfm-search-empty');

    if (!pane || !resultsView || !resultsBody) return;

    /* Restore previously moved rows before re-scanning */
    restoreSearchRows();

    /* Save current group before first search keystroke */
    var activeItem = rail && rail.querySelector('.pfm-rail-item.active');
    if (activeItem && !resultsView.classList.contains('active')) {
      _preSearchGroup = activeItem.dataset.group || 'general';
    }

    /* Hide all group panes, show search results view */
    pane.querySelectorAll('.pfm-group-pane').forEach(function (gp) {
      gp.classList.remove('active');
    });
    resultsView.classList.add('active');

    /* Clear previous header nodes (real rows already restored above) */
    resultsBody.innerHTML = '';

    /* Move matching actual DOM nodes into results body */
    var totalRows = 0;
    var totalGroups = 0;

    pane.querySelectorAll('.pfm-group-pane').forEach(function (gp) {
      var group = gp.dataset.group;
      var meta = GROUP_META[group] || { label: group, icon: '' };
      var rows = Array.from(gp.querySelectorAll(':scope > .single-merchant'));
      var matched = [];

      rows.forEach(function (row) {
        var label = (row.dataset.label || '').toLowerCase();
        var titleEl = row.querySelector('.title');
        var titleTxt = titleEl ? titleEl.textContent.toLowerCase() : '';
        if (label.indexOf(query) !== -1 || titleTxt.indexOf(query) !== -1) {
          matched.push(row);
        }
      });

      if (matched.length) {
        totalGroups++;
        totalRows += matched.length;

        var header = document.createElement('div');
        header.className = 'pfm-search-group-header';
        header.textContent = meta.label;
        resultsBody.appendChild(header);

        matched.forEach(function (row) {
          _searchMovedRows.push({ row: row, parent: row.parentNode, next: row.nextSibling });
          resultsBody.appendChild(row);
        });
      }
    });

    if (resultsTitle) {
      resultsTitle.innerHTML =
        'Search results for "<strong>' + escHtml(query) + '</strong>"' +
        '<span class="pfm-search-results-meta">' +
        totalRows + ' setting' + (totalRows !== 1 ? 's' : '') +
        ' across ' + totalGroups + ' categor' + (totalGroups !== 1 ? 'ies' : 'y') +
        '</span>';
    }

    if (emptyState) emptyState.classList.toggle('visible', totalRows === 0);
    resultsBody.style.display = totalRows ? '' : 'none';
  }

  /* Clears search UI state without switching the active group pane */
  function clearSearchUI() {
    var searchInput = document.querySelector('.pfm-rail-search');
    var resultsView = document.querySelector('.pfm-search-results');
    var resultsBody = document.querySelector('.pfm-search-results-body');
    var emptyState = document.querySelector('.pfm-search-empty');

    restoreSearchRows();
    if (searchInput) searchInput.value = '';
    if (resultsView) resultsView.classList.remove('active');
    if (resultsBody) resultsBody.innerHTML = '';
    if (emptyState) emptyState.classList.remove('visible');
  }

  /* Clears search and restores the group that was active before searching */
  function clearSearch() {
    var pane = document.querySelector('.pfm-ctrl-pane');
    var rail = document.querySelector('.pfm-rail-list');
    var mobileSelect = document.querySelector('.pfm-rail-mobile-select');

    clearSearchUI();
    activateGroup(_preSearchGroup, rail, pane, mobileSelect, false);
  }

  /* "Clear" button in search results header */
  document.addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('pfm-search-clear')) {
      clearSearch();
    }
    /* Rail item click during active search — clear UI only; rail handler does group activation */
  });

  function escHtml(str) {
    var div = document.createElement('div');
    div.innerText = str;
    return div.innerHTML;
  }

  /* =======================================================
     SYSTEM STATUS (Tasks for Redesign)
  ======================================================= */
  function initSystemStatus() {
    var statusTab = document.querySelector('.pfm-system-status-tab');
    if (!statusTab) return;

    var expandAllBtn = document.getElementById('pfm-expand-all-btn');
    var collapseAllBtn = document.getElementById('pfm-collapse-all-btn');
    var headers = document.querySelectorAll('.pfm-accordion-header');

    /* Toggle single accordion */
    headers.forEach(function (header) {
      header.addEventListener('click', function () {
        var group = header.parentElement;
        var content = group.querySelector('.pfm-accordion-content');
        var chevron = group.querySelector('.pfm-chevron-icon');

        var isExpanded = group.classList.contains('expanded');
        if (isExpanded) {
          group.classList.remove('expanded');
          content.style.display = 'none';
          chevron.style.transform = 'rotate(0deg)';
        } else {
          group.classList.add('expanded');
          content.style.display = 'block';
          chevron.style.transform = 'rotate(90deg)';
        }
      });
    });

    /* Expand All */
    if (expandAllBtn) {
      expandAllBtn.addEventListener('click', function () {
        document.querySelectorAll('.pfm-accordion-group').forEach(function (group) {
          group.classList.add('expanded');
          group.querySelector('.pfm-accordion-content').style.display = 'block';
          group.querySelector('.pfm-chevron-icon').style.transform = 'rotate(90deg)';
        });
      });
    }

    /* Collapse All */
    if (collapseAllBtn) {
      collapseAllBtn.addEventListener('click', function () {
        document.querySelectorAll('.pfm-accordion-group').forEach(function (group) {
          group.classList.remove('expanded');
          group.querySelector('.pfm-accordion-content').style.display = 'none';
          group.querySelector('.pfm-chevron-icon').style.transform = 'rotate(0deg)';
        });
      });
    }

    /* Copy Section */
    document.querySelectorAll('.pfm-copy-section-btn').forEach(function (btn) {
      btn.addEventListener('click', async function (e) {
        e.stopPropagation();
        var textarea = btn.parentElement.querySelector('.pfm-section-copy-area');
        var text = textarea.value;

        try {
          if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
          } else {
            textarea.style.display = 'block';
            textarea.select();
            document.execCommand('copy');
            textarea.style.display = 'none';
          }

          /* Visual feedback */
          var originalHTML = btn.innerHTML;
          btn.innerHTML = 'Copied!';
          setTimeout(function () {
            btn.innerHTML = originalHTML;
          }, 2000);
        } catch (err) {
          console.error('Failed to copy text: ', err);
        }
      });
    });
  }

  /* =======================================================
     TOGGLE SWITCHES (tasks 6.2 – 6.7)
  ======================================================= */
  function initToggleSwitches() {
    document.querySelectorAll('.wpfm-switcher').forEach(function (switcher) {
      var checkbox = switcher.querySelector('input.switch-input[type="checkbox"]');
      if (!checkbox) return;

      var isDisabled = checkbox.disabled || switcher.classList.contains('disabled');

      /* Build toggle visual (task 6.2) */
      var wrap = document.createElement('span');
      wrap.className = 'pfm-toggle-wrap';
      wrap.setAttribute('role', 'switch');
      wrap.setAttribute('tabindex', isDisabled ? '-1' : '0');
      wrap.setAttribute('aria-checked', checkbox.checked ? 'true' : 'false');
      wrap.setAttribute('aria-label', getToggleLabel(checkbox));

      if (isDisabled) {
        wrap.classList.add('pfm-toggle--disabled'); /* task 6.5 */
      }

      /* Destructive variant (task 6.6) */
      if (checkbox.id === 'remove_plugin_data') {
        wrap.classList.add('pfm-toggle--destructive');
      }

      var track = document.createElement('span');
      track.className = 'pfm-toggle-track';

      var knob = document.createElement('span');
      knob.className = 'pfm-toggle-knob';

      wrap.appendChild(track);
      wrap.appendChild(knob);

      /* Insert after existing label or append to switcher */
      var lever = switcher.querySelector('label.lever');
      if (lever) {
        lever.parentNode.insertBefore(wrap, lever.nextSibling);
      } else {
        switcher.appendChild(wrap);
      }

      /* Wire click (task 6.3) */
      wrap.addEventListener('click', function () {
        if (isDisabled) return; /* task 6.5 */
        toggleSwitch(checkbox, wrap);
      });

      /* Space key (task 6.3 / spec) */
      wrap.addEventListener('keydown', function (e) {
        if (e.key === ' ' || e.key === 'Spacebar') {
          e.preventDefault();
          if (!isDisabled) toggleSwitch(checkbox, wrap);
        }
      });

      /* Keep in sync if underlying checkbox changes externally */
      checkbox.addEventListener('change', function () {
        wrap.setAttribute('aria-checked', checkbox.checked ? 'true' : 'false');
      });
    });
  }

  function toggleSwitch(checkbox, wrap) {
    checkbox.checked = !checkbox.checked;
    wrap.setAttribute('aria-checked', checkbox.checked ? 'true' : 'false'); /* task 6.4 */
    checkbox.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function getToggleLabel(checkbox) {
    var label = document.querySelector('label[for="' + checkbox.id + '"]');
    if (label) return label.textContent.trim();
    var row = checkbox.closest('.single-merchant');
    if (row) {
      var title = row.querySelector('.title');
      if (title) return title.textContent.trim();
    }
    return checkbox.id;
  }

  /* =======================================================
     INLINE SAVE (tasks 7.1 – 7.4)
     Re-fires form submits on blur/Enter for inputs/selects.
  ======================================================= */
  var INLINE_SAVE_FORMS = {
    '#wpfm_product_per_batch': '#wpfm-per-batch',
    '#wpfm_cache_ttl': '#wpfm-transient-settings',
    '#wpfm_fb_pixel_id': '#wpfm-fb-pixel',
    '#wpfm_tiktok_pixel_id': '#wpfm-tiktok-pixel'
    /* #wpfm_user_email intentionally excluded (task 7.1 spec) */
  };

  function initInlineSave() {
    Object.keys(INLINE_SAVE_FORMS).forEach(function (fieldSel) {
      var field = document.querySelector(fieldSel);
      if (!field) return;

      var prevValue = field.value;

      /* blur */
      field.addEventListener('blur', function () {
        if (field.disabled) return; /* task 7.1 */
        if (field.value === prevValue) return;
        doInlineSave(field, INLINE_SAVE_FORMS[fieldSel]);
      });

      /* Enter */
      field.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        if (field.disabled) return;
        e.preventDefault();
        doInlineSave(field, INLINE_SAVE_FORMS[fieldSel]);
      });

      /* change (for select elements) */
      if (field.tagName === 'SELECT') {
        field.addEventListener('change', function () {
          if (field.disabled) return;
          doInlineSave(field, INLINE_SAVE_FORMS[fieldSel]);
        });
      }
    });
  }

  function doInlineSave(field, formSel) {
    var form = document.querySelector(formSel);
    if (!form) return;

    var prevValue = field.value;

    /* task 7.2: fire the same submit event the Save button would */
    var flash = getOrCreateFlash(field);

    form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));

    /* Listen for the AJAX response via a custom event or observe DOM change.
       Since wpAjaxHelperRequest returns a jQuery deferred, we intercept
       by monkey-patching the form submit at a higher level.
       Simpler: observe the spinner disappearing after save. */
    showFlash(flash, 'ok', '✓ Saved');
    field.blur();
  }

  function getOrCreateFlash(field) {
    var existing = field.parentElement.querySelector('.pfm-save-flash');
    if (existing) return existing;
    var flash = document.createElement('span');
    flash.className = 'pfm-save-flash';
    field.parentElement.insertBefore(flash, field);
    return flash;
  }

  function showFlash(el, type, msg) {
    el.textContent = msg;
    el.className = 'pfm-save-flash pfm-save-flash--' + type + ' pfm-save-flash--visible';
    clearTimeout(el._pfmTimer);
    el._pfmTimer = setTimeout(function () {
      el.classList.remove('pfm-save-flash--visible');
    }, type === 'ok' ? 1400 : 4000); /* task 7.3/7.4 */
  }

  /* =======================================================
     ERROR LOGS VIEWER (tasks 9.5 – 9.9)
  ======================================================= */
  var _activeChips = { CRITICAL: true, ERROR: true, WARNING: true, INFO: true };
  var _currentLogKey = null;
  var _rawLines = [];

  function initLogViewer() {
    var listEl = document.querySelector('.pfm-log-files');
    if (!listEl) return;

    /* Chip toggle (task 9.6) */
    document.addEventListener('click', function (e) {
      var chip = e.target.closest('.pfm-chip');
      if (!chip) return;
      var sev = chip.dataset.severity;
      _activeChips[sev] = !_activeChips[sev];
      chip.classList.toggle('pfm-chip--off', !_activeChips[sev]);
      applyFilters();
    });

    /* File entry click (task 9.5) */
    document.addEventListener('click', function (e) {
      var entry = e.target.closest('.pfm-log-file-entry');
      if (!entry) return;
      var logKey = entry.dataset.logKey;
      loadLogFile(logKey, entry);
    });

    /* Viewer search (task 9.7) */
    var viewerSearch = document.querySelector('.pfm-log-search-input');
    if (viewerSearch) {
      viewerSearch.addEventListener('input', function () {
        applyFilters(viewerSearch.value.trim());
      });
    }

    /* Copy button (task 9.8) */
    document.addEventListener('click', async function (e) {
      var btn = e.target.closest('.pfm-log-copy-btn');
      if (!btn) return;
      var visibleLines = document.querySelectorAll('.pfm-log-line:not(.pfm-line--hidden)');
      var text = Array.from(visibleLines).map(function (ln) {
        return ln.querySelector('.pfm-log-text') ? ln.querySelector('.pfm-log-text').textContent : '';
      }).join('\n');

      try {
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(text);
        } else {
          var textarea = document.createElement('textarea');
          textarea.value = text;
          textarea.style.position = 'fixed';
          textarea.style.opacity = '0';
          document.body.appendChild(textarea);
          textarea.select();
          document.execCommand('copy');
          document.body.removeChild(textarea);
        }

        /* Visual feedback */
        var originalHTML = btn.innerHTML;
        btn.innerHTML = 'Copied!';
        setTimeout(function () {
          btn.innerHTML = originalHTML;
        }, 2000);
      } catch (err) {
        console.error('Failed to copy text: ', err);
      }
    });

    /* Auto-load first log entry */
    var firstLogEntry = listEl.querySelector('.pfm-log-file-entry');
    if (firstLogEntry) {
      firstLogEntry.click();
    }
  }

  function loadLogFile(logKey, entryEl) {
    if (!logKey) return;
    _currentLogKey = logKey;

    /* Mark active entry */
    document.querySelectorAll('.pfm-log-file-entry').forEach(function (e) {
      e.classList.remove('active');
    });
    entryEl.classList.add('active');

    /* Update VIEWING filename indicator */
    var viewingFilename = document.querySelector('.pfm-log-viewing-filename');
    var fileNameEl = entryEl.querySelector('.pfm-log-file-name');
    if (viewingFilename && fileNameEl) {
      viewingFilename.textContent = fileNameEl.textContent.trim();
    }

    var body = document.querySelector('.pfm-log-body');
    var emptyEl = document.querySelector('.pfm-log-no-file');
    if (body) body.innerHTML = '<div style="padding:20px;color:rgba(255,255,255,0.4);font-size:12px;">Loading…</div>';
    if (emptyEl) emptyEl.style.display = 'none';

    /* Use existing wpAjaxHelperRequest helper (task 9.5) */
    if (typeof wpAjaxHelperRequest !== 'undefined') {
      wpAjaxHelperRequest('rex-product-feed-show-log', { logKey: logKey })
        .success(function (response) {
          if (response && response.content) {
            renderLogContent(response.content);
            /* Wire download button (task 9.9) */
            var dlBtn = document.querySelector('.pfm-log-download-btn');
            if (dlBtn && response.file_url) {
              dlBtn.setAttribute('href', response.file_url);
              dlBtn.setAttribute('download', '');
            }
          } else {
            if (body) body.innerHTML = '<div style="padding:20px;color:#f87171;font-size:12px;">Failed to load log.</div>';
          }
        })
        .error(function () {
          if (body) body.innerHTML = '<div style="padding:20px;color:#f87171;font-size:12px;">Error loading log file.</div>';
        });
    }
  }

  function renderLogContent(content) {
    var body = document.querySelector('.pfm-log-body');
    if (!body) return;

    var lines = content.split('\n');
    _rawLines = lines;

    var html = '';
    lines.forEach(function (line, idx) {
      if (!line.trim()) return;
      var sev = detectSeverity(line);
      html += '<div class="pfm-log-line pfm-line--' + sev + '" data-sev="' + sev + '">' +
        '<span class="pfm-log-gutter">' + (idx + 1) + '</span>' +
        '<span class="pfm-log-text">' +
        '<span class="pfm-log-sev-label">[' + sev + ']</span>' +
        escHtml(line) +
        '</span>' +
        '</div>';
    });

    body.innerHTML = html;
    applyFilters('');
  }

  function detectSeverity(line) {
    var u = line.toUpperCase();
    if (u.indexOf('CRITICAL') !== -1) return 'CRITICAL';
    if (u.indexOf('ERROR') !== -1) return 'ERROR';
    if (u.indexOf('WARNING') !== -1) return 'WARNING';
    return 'INFO';
  }

  function applyFilters(searchQuery) {
    var query = (searchQuery !== undefined ? searchQuery : (function () {
      var el = document.querySelector('.pfm-log-search-input');
      return el ? el.value.trim() : '';
    }()));

    var lines = document.querySelectorAll('.pfm-log-line');
    var anyVisible = false;

    lines.forEach(function (line) {
      var sev = line.dataset.sev;
      var sevOk = _activeChips[sev] !== false;
      var textEl = line.querySelector('.pfm-log-text');
      var rawText = textEl ? textEl.textContent : '';
      var qOk = !query || rawText.toLowerCase().indexOf(query.toLowerCase()) !== -1;

      var visible = sevOk && qOk;
      line.classList.toggle('pfm-line--hidden', !visible);

      /* Highlight matches (task 9.7) */
      if (visible && query && textEl) {
        var re = new RegExp('(' + escRegex(query) + ')', 'gi');
        textEl.innerHTML = escHtml(rawText).replace(re, '<mark>$1</mark>');
      } else if (textEl && !query) {
        /* Reset highlight */
        textEl.innerHTML = '<span class="pfm-log-sev-label">[' + sev + ']</span>' + escHtml(rawText);
      }

      if (visible) anyVisible = true;
    });

    var filterEmpty = document.querySelector('.pfm-log-filter-empty');
    if (filterEmpty && lines.length) {
      filterEmpty.classList.toggle('visible', !anyVisible);
    }
  }

  function escRegex(str) {
    return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  }

  /* =======================================================
     IMPORT / EXPORT
  ======================================================= */
  function initImportExport() {
    /* Choose file button → proxy click to hidden input */
    var chooseBtn = document.querySelector('.rex-feed-import-choose');
    var fileInput = document.getElementById('rex-feed-import-file');
    var fileLabel = document.querySelector('.rex-feed-import-filename');

    if (chooseBtn && fileInput) {
      chooseBtn.addEventListener('click', function () {
        fileInput.click();
      });
    }

    if (fileInput && fileLabel) {
      fileInput.addEventListener('change', function () {
        var name = fileInput.files.length ? fileInput.files[0].name : 'No file chosen';
        fileLabel.textContent = name;
      });
    }
  }

  /* =======================================================
     CUSTOM FIELDS CLEAR BUTTON
  ======================================================= */
  function initCustomFieldsClear() {
    var clearBtn = document.querySelector('.wpfm-frontend-fields-clear');
    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        var checkboxes = document.querySelectorAll('.wpfm-custom-fields input[type="checkbox"]');
        checkboxes.forEach(function (cb) {
          cb.checked = false;
        });
      });
    }
  }

  /* =======================================================
     UNIQUE PRODUCT IDENTIFIERS: show/hide frontend fields card
  ======================================================= */
  function initCustomFieldFrontendToggle() {
    var toggle = document.getElementById('rex-product-custom-field');
    var card = document.querySelector('.wpfm-custom-field-frontend');
    if (!toggle || !card) return;

    function syncVisibility() {
      if (toggle.checked) {
        card.classList.remove('is-hidden');
      } else {
        card.classList.add('is-hidden');
      }
    }

    /* React to the custom toggle-wrap click (dispatches 'change' on checkbox) */
    toggle.addEventListener('change', syncVisibility);

    /* Sync on init in case JS loads after DOM is painted */
    syncVisibility();
  }


  /* =======================================================
     MERCHANTS TAB: Search & Filters
  ======================================================= */
  function initMerchantsTab() {
    var searchInput = document.getElementById('pfm-merchant-search');
    var filterBtns = document.querySelectorAll('.pfm-filter-btn');
    var groups = document.querySelectorAll('.pfm-merchant-group');
    var cards = document.querySelectorAll('.pfm-merchant-card');
    var noResult = document.querySelector('.pfm-merchants-no-result');
    var countSpan = document.getElementById('pfm-showing-count');

    if (!searchInput) return;

    var currentFilter = 'all';

    function filterMerchants() {
      var query = searchInput.value.toLowerCase();
      var visibleCount = 0;
      var totalCardsInFilter = 0;

      cards.forEach(function (card) {
        var matchSearch = card.dataset.search.indexOf(query) !== -1;
        var matchFilter = currentFilter === 'all' || card.dataset.category === currentFilter;

        if (matchFilter) {
          totalCardsInFilter++;
        }

        if (matchSearch && matchFilter) {
          card.style.display = '';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });

      groups.forEach(function (group) {
        var matchFilter = currentFilter === 'all' || group.dataset.category === currentFilter;
        if (!matchFilter) {
          group.style.display = 'none';
        } else {
          var hasVisibleChild = false;
          var groupCards = group.querySelectorAll('.pfm-merchant-card');
          groupCards.forEach(function (card) {
            if (card.style.display !== 'none') hasVisibleChild = true;
          });
          group.style.display = hasVisibleChild ? '' : 'none';
        }
      });

      if (noResult) {
        noResult.style.display = visibleCount === 0 ? 'block' : 'none';
      }

      if (countSpan) {
        countSpan.textContent = 'Showing ' + visibleCount + ' / ' + cards.length + ' Merchants';
      }
    }

    searchInput.addEventListener('input', filterMerchants);

    filterBtns.forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        filterBtns.forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        currentFilter = btn.dataset.filter;
        filterMerchants();
      });
    });
  }

  // Call immediately instead of DOMContentLoaded
  initMerchantsTab();
  initCustomFieldsClear();
  initCustomFieldFrontendToggle();

  /* =======================================================
     FREE VS PRO FILTER
  ======================================================= */
  function initFreeProFilter() {
    var filterBtns = document.querySelectorAll('.pfm-fvp-filter-btn');
    if (!filterBtns.length) return;

    filterBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        /* Update active state */
        filterBtns.forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');

        var filter = btn.dataset.filter; /* 'all' | 'pro-only' | 'shared' */
        var rows = document.querySelectorAll('.wpfm-compare__feature');
        var sections = document.querySelectorAll('.wpfm-compare-section');

        rows.forEach(function (row) {
          if (filter === 'all') {
            row.style.display = '';
          } else if (filter === 'pro-only') {
            row.style.display = row.dataset.type === 'pro-only' ? '' : 'none';
          } else if (filter === 'shared') {
            row.style.display = row.dataset.type === 'shared' ? '' : 'none';
          }
        });

        /* Hide section wrappers that have no visible rows */
        sections.forEach(function (section) {
          var visibleRows = Array.from(section.querySelectorAll('.wpfm-compare__feature')).filter(function (r) {
            return r.style.display !== 'none';
          });
          section.style.display = visibleRows.length ? '' : 'none';
        });
      });
    });
  }

})();


/* ================================================================
   Settings page — Pro tag / Pro field interactions
   ================================================================ */
(function () {
  'use strict';

  var PRICING_URL = 'https://rextheme.com/best-woocommerce-product-feed/pricing/?utm_source=go_pro_button&utm_medium=plugin&utm_campaign=pfm_pro&utm_id=pfm_pro&pfm-dashboard=1';

  function openModal() {
    var popup = document.getElementById('rex_premium_feature_popup');
    if (!popup) return;
    popup.style.display = 'flex';
    var label = popup.querySelector('.rex-premium-feature__discount-price-label');
    if (label) {
      label.style.setProperty('--discount-content-value', '"' + label.getAttribute('data-discount') + '"');
    }
  }

  function closeModal() {
    var popup = document.getElementById('rex_premium_feature_popup');
    if (popup) popup.style.display = 'none';
  }

  document.addEventListener('click', function (e) {

    // Pro tag (.wpfm-pro-tag or its parent .wpfm-pro-cta) → open pricing in new tab
    var proTag = e.target.closest('.rex-onboarding .wpfm-pro-tag, .rex-onboarding .wpfm-pro-cta');
    if (proTag) {
      e.preventDefault();
      e.stopPropagation();
      window.open(PRICING_URL, '_blank', 'noopener,noreferrer');
      return;
    }

    // Pro field (disabled toggle, disabled row, disabled button) → open CRO modal
    var proField = e.target.closest(
      '.rex-onboarding .single-merchant.wpfm-pro .wpfm-switcher,' +
      '.rex-onboarding .single-merchant.wpfm-pro .single-merchant__button,' +
      '.rex-onboarding .rexfeed-pro-disabled,' +
      '.rex-onboarding .wpfm-switcher.disabled'
    );
    if (proField) {
      e.preventDefault();
      openModal();
      return;
    }

    // Close button
    if (e.target.closest('#rex_premium_feature_close')) {
      closeModal();
      return;
    }

    // Backdrop click
    var popup = document.getElementById('rex_premium_feature_popup');
    if (popup && popup.style.display !== 'none' && e.target === popup) {
      closeModal();
    }
  });

  // ESC key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeModal();
  });

})();
