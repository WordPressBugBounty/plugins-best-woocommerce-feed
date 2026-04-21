jQuery(document).ready(function ($) {
    var LinnoOnboarding  = window.LinnoOnboarding;
    var registerOnboarding = LinnoOnboarding.registerOnboarding;
    var engine           = LinnoOnboarding.engine;
    var tracker          = LinnoOnboarding.tracker;

    var assetsUrl      = typeof pfmMerchantsData !== 'undefined' ? pfmMerchantsData.assetsUrl      : '';
    var storeName      = typeof pfmMerchantsData !== 'undefined' ? pfmMerchantsData.storeName      : '';
    var allMerchants   = typeof pfmMerchantsData !== 'undefined' ? pfmMerchantsData.merchants      : [];
    var isPremiumUser  = typeof pfmMerchantsData !== 'undefined' ? pfmMerchantsData.isPremium      : false;
    var companionPlugins = typeof pfmMerchantsData !== 'undefined' ? pfmMerchantsData.companionPlugins : {};

    // Merchant → feed format map (default xml)
    var merchantFormatMap = {
        google   : 'xml',
        facebook : 'xml',
        tiktok   : 'xml',
        bing     : 'xml',
        pinterest: 'xml',
        snapchat : 'csv',
        idealo   : 'csv',
    };

    // Top-5 popular merchants (images must exist in assetsUrl folder)
    var popularMerchants = [
        { id: 'google',   name: 'Google Shopping',      img: assetsUrl + 'google.webp'   },
        { id: 'facebook', name: 'Meta Product Catalog', img: assetsUrl + 'facebook.webp' },
        { id: 'idealo',   name: 'Idealo',               img: assetsUrl + 'Idealo.svg'    },
        { id: 'tiktok',   name: 'TikTok Shop',          img: assetsUrl + 'tiktok.webp'   },
        { id: 'pinterest',name: 'Pinterest',            img: assetsUrl + 'pinterest.webp' },
    ];

    // State
    var selectedMerchantId   = null;
    var selectedMerchantName = null;
    var feedData             = {};
    var mappingData          = { count: 0, mappings: [] };
    var installedCount       = 0;

    // ─── Helpers ──────────────────────────────────────────────────────────────

    function getMonogramColor(name) {
        var hash = 0;
        for (var i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        return Math.abs(hash % 10) + 1;
    }

    function getMerchantFormat(id) {
        return merchantFormatMap[id] || 'xml';
    }

    function spinnerHtml(text) {
        return '<span style="display:inline-flex;align-items:center;gap:8px;">' +
            '<svg class="pfm-spin" width="16" height="16" viewBox="0 0 50 50">' +
            '<circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="5" stroke-dasharray="31.4 31.4" stroke-linecap="round"></circle>' +
            '</svg>' + text + '</span>';
    }

    function injectSpinnerCSS() {
        if (!$('#pfm-spin-css').length) {
            $('<style id="pfm-spin-css">@keyframes pfm-rotate{100%{transform:rotate(360deg)}}.pfm-spin{animation:pfm-rotate 1s linear infinite}</style>').appendTo('head');
        }
    }

    function merchantImgWithFallback(m) {
        var $img = $('<img class="pfm-merchant-logo" src="' + m.img + '" alt="' + m.name + '">');
        $img.on('error', function () {
            var cc = 'monogram-color-' + getMonogramColor(m.name);
            $(this).replaceWith($('<div class="pfm-merchant-monogram ' + cc + '">' + m.name.charAt(0).toUpperCase() + '</div>'));
        });
        return $img;
    }

    function checkmarkSvg() {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
    }

    function showStep(id) {
        $('.pfm-wizard-step').removeClass('active');
        $('#' + id).addClass('active');
    }

    function serializeMappings(mappings) {
        var p = ['rex_feed_parent_product=yes', 'rex_feed_variations=yes', 'rex_feed_variable_product=yes'];
        if (!mappings || !Array.isArray(mappings)) return p.join('&');
        mappings.forEach(function (m, i) {
            p.push('fc[' + i + '][attr]=' + encodeURIComponent(m.attr || ''));
            p.push('fc[' + i + '][type]=' + encodeURIComponent(m.type || ''));
            if (m.type === 'meta')   p.push('fc[' + i + '][meta_key]=' + encodeURIComponent(m.meta_key || ''));
            if (m.type === 'static') p.push('fc[' + i + '][st_value]=' + encodeURIComponent(m.st_value || ''));
            if (m.prefix) p.push('fc[' + i + '][prefix]=' + encodeURIComponent(m.prefix));
            if (m.suffix) p.push('fc[' + i + '][suffix]=' + encodeURIComponent(m.suffix));
            if (m.escape) p.push('fc[' + i + '][escape]=' + encodeURIComponent(m.escape));
            if (m.limit)  p.push('fc[' + i + '][limit]='  + encodeURIComponent(m.limit));
        });
        return p.join('&');
    }

    function generateFeedBatch(feedId, totalProds, offset, batch, perBatch, totalBatches, onDone, onError) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'rexfeed-generate-feed',
                security: pfmNonce,
                payload: {
                    merchant: feedData.merchant_slug,
                    feed_format: feedData.format,
                    feed_config: serializeMappings(feedData.mappings || []),
                    info: { post_id: feedId, title: feedData.name, desc: feedData.name, offset: offset, batch: batch, per_batch: perBatch, total_batch: totalBatches },
                    products: { products_scope: 'all', tags: [], cats: [], brands: [], data: '' }
                }
            },
            success: function (r) {
                if (r && r.msg === 'finish') {
                    onDone && onDone();
                } else if (r && r.msg === 'failForEmptyProduct') {
                    onDone && onDone(); // still proceed
                } else if (batch < totalBatches) {
                    setTimeout(function () {
                        generateFeedBatch(feedId, totalProds, offset + perBatch, batch + 1, perBatch, totalBatches, onDone, onError);
                    }, 100);
                } else {
                    onDone && onDone();
                }
            },
            error: function () { onError && onError(); }
        });
    }

    // ─── Step 1 helpers ───────────────────────────────────────────────────────

    function renderPopularGrid() {
        var $grid = $('#pfmPopularGrid').empty();
        popularMerchants.forEach(function (m) {
            var selected = m.id === selectedMerchantId;
            var $card = $('<div>', { class: 'pfm-popular-card' + (selected ? ' selected' : ''), 'data-id': m.id, 'data-name': m.name });
            $card.append(merchantImgWithFallback(m));
            $card.append($('<div class="pfm-merchant-name">').text(m.name));
            $card.append($('<div class="pfm-merchant-check">').html(checkmarkSvg()));
            $card.on('click', function () { selectMerchant(m.id, m.name, $(this), 'popular'); });
            $grid.append($card);
        });
    }

    function selectMerchant(id, name, $card, type) {
        var wasSelected = $card.hasClass('selected');
        $('.pfm-popular-card, .pfm-result-card').removeClass('selected');
        if (!wasSelected) {
            $card.addClass('selected');
            selectedMerchantId   = id;
            selectedMerchantName = name;
            $('#pfmStep1ContinueBtn').prop('disabled', false);
        } else {
            selectedMerchantId   = null;
            selectedMerchantName = null;
            $('#pfmStep1ContinueBtn').prop('disabled', true);
        }
    }

    function renderSearchResults(query) {
        var $wrap = $('#pfmSearchResultsWrap');
        var $grid = $('#pfmSearchResults').empty();
        if (!query) { $wrap.hide(); return; }

        // Combine popular + all other merchants, deduplicate
        var seen    = {};
        var combined = popularMerchants.map(function (m) {
            return { id: m.id, name: m.name, isPro: false, isAvailable: true, img: m.img };
        }).concat(allMerchants).filter(function (m) {
            if (seen[m.id]) return false;
            seen[m.id] = true;
            return true;
        });

        var term    = query.toLowerCase();
        var results = combined.filter(function (m) { return m.name.toLowerCase().indexOf(term) !== -1; });

        $wrap.show();
        if (!results.length) { $grid.html('<div class="pfm-search-empty">No merchants found</div>'); return; }

        results.forEach(function (m) {
            var isSelected = m.id === selectedMerchantId;
            var isDisabled = m.isPro && !isPremiumUser;
            var $card = $('<div>', { class: 'pfm-result-card' + (isSelected ? ' selected' : '') + (isDisabled ? ' disabled' : ''), 'data-id': m.id });

            if (m.img) {
                $card.append(merchantImgWithFallback(m));
            } else {
                var cc = 'monogram-color-' + getMonogramColor(m.name);
                $card.append($('<div class="pfm-merchant-monogram ' + cc + '">').text(m.name.charAt(0).toUpperCase()));
            }
            $card.append($('<div class="pfm-merchant-name">').text(m.name));

            if (m.isPro && !isPremiumUser) {
                $card.append('<span class="pfm-merchant-badge">Pro</span>');
            }
            if (!isDisabled) {
                $card.append($('<div class="pfm-merchant-check">').html(checkmarkSvg()));
                $card.on('click', function () { selectMerchant(m.id, m.name, $(this), 'search'); });
            }
            $grid.append($card);
        });
    }

    // ─── Step 2 helpers ───────────────────────────────────────────────────────

    function mountStep2() {
        var fmt     = getMerchantFormat(selectedMerchantId);
        var display = selectedMerchantName || 'your feed';

        feedData.format       = fmt;
        feedData.frequency    = 'daily';
        feedData.merchant     = selectedMerchantName;
        feedData.merchant_slug = selectedMerchantId;

        $('#pfmConfigHeading').text('Setting up your ' + display + ' feed');

        var fmtLabel = fmt.toUpperCase();
        if (fmt === 'csv') {
            $('#pfmFormatConfirmText').text('Feed format — CSV (required by ' + display + ')');
        } else {
            $('#pfmFormatConfirmText').text('Feed format — ' + fmtLabel);
        }

        var autoName = (storeName ? storeName + ' - ' : '') + display + ' Feed';
        $('#pfmFeedNameInput').val(autoName);
        feedData.name = autoName;

        $('#pfmFeedNameInput').off('input').on('input', function () {
            feedData.name = $(this).val().trim() || autoName;
        });

        startMappingAnimation();
    }

    function startMappingAnimation() {
        var $bar    = $('#pfmMappingBarFill');
        var $status = $('#pfmMappingStatus');
        var $detail = $('#pfmMappingDetail');
        var done    = false;

        $bar.css({ transition: 'none', width: '0%' });
        $status.text('Mapping your product attributes...');
        $detail.text('Analyzing products...');

        // Animate to 80 % over 1.5–2.5 s
        var duration  = 1500 + Math.random() * 1000;
        var startTime = Date.now();
        (function tick() {
            if (done) return;
            var pct = Math.min((Date.now() - startTime) / duration * 80, 80);
            $bar.css('width', pct + '%');
            if (pct < 80) requestAnimationFrame(tick);
        })();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'pfm_get_template_mappings', merchant: selectedMerchantId, security: pfmNonce },
            success: function (r) {
                done = true;
                if (r.success && r.data && r.data.mappings) {
                    mappingData.mappings = r.data.mappings;
                    mappingData.count    = r.data.mappings.length;
                    feedData.mappings    = r.data.mappings;
                } else {
                    mappingData.mappings = [];
                    mappingData.count    = 0;
                    feedData.mappings    = [];
                }
                finishMappingAnimation($bar, $status, $detail);
            },
            error: function () {
                done = true;
                mappingData.mappings = [];
                mappingData.count    = 0;
                feedData.mappings    = [];
                finishMappingAnimation($bar, $status, $detail);
            }
        });
    }

    function finishMappingAnimation($bar, $status, $detail) {
        $bar.css({ transition: 'width 0.4s ease', width: '100%' });
        $detail.text('');
        setTimeout(function () {
            var n = mappingData.count;
            if (n >= 5) {
                $status.html('<span style="margin-right:6px;">✅</span>' + n + ' attributes mapped automatically');
            } else if (n > 0) {
                $status.html('<span style="margin-right:6px;">⚠️</span>' + n + ' attributes mapped. You can add more after your feed is created.');
            } else {
                $status.html('<span style="margin-right:6px;">⚠️</span>We couldn\'t find product data yet. You can map attributes manually after your feed is created.');
            }
        }, 450);
    }

    // ─── Step 3 helpers ───────────────────────────────────────────────────────

    function mountStep3() {
        var display = selectedMerchantName || 'Your';

        $('#pfmAhaTitle').text('Your ' + display + ' Feed is Ready!');
        $('#pfmAhaFeedName').text(feedData.name || '');
        $('#pfmFeedUrlInput').val(feedData.feed_url || '');

        var $openBtn = $('#pfmOpenUrlBtn');
        if ( feedData.format === 'csv' ) {
            $openBtn.attr('href', feedData.feed_url || '#')
                    .attr('download', '')
                    .text('Download');
        } else {
            $openBtn.attr('href', feedData.feed_url || '#')
                    .removeAttr('download')
                    .html('Open &#8599;');
        }
        $('#pfmPropagationText').text('Your feed URL is ready. Submit to your ' + display + ' account to start listing your products. The feed file refreshes automatically every day.');

        $('#pfmWpfDesc').html(
            'Replace your default checkout with a high-converting page. Add order bumps and upsells so every visitor from <strong>' + display + '</strong> is worth more.'
        );

        // Copy button
        $('#pfmCopyUrlBtn').off('click').on('click', function () {
            var url  = $('#pfmFeedUrlInput').val();
            var $btn = $(this);
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function () {
                    $btn.text('Copied!');
                    setTimeout(function () { $btn.text('Copy'); }, 2000);
                });
            } else {
                $('#pfmFeedUrlInput').select();
                document.execCommand('copy');
                $btn.text('Copied!');
                setTimeout(function () { $btn.text('Copy'); }, 2000);
            }
        });

        // Consent checkbox — checked by default, fire consent immediately on mount
        $('#pfmConsentCheckbox').prop('checked', true);
        saveConsent(true);
        $('#pfmConsentCheckbox').off('change').on('change', function () {
            saveConsent(this.checked);
        });

        // Dashboard / skip
        function goToDashboard() {
            markCompleted(function () { window.location.href = 'edit.php?post_type=product-feed'; });
        }
        $('#pfmGoToDashboardBtn').off('click').on('click', goToDashboard);
        $('#pfmSkipForNowBtn').off('click').on('click', goToDashboard);

        applyCompanionStatuses();
    }

    function saveConsent(isGiven) {
        $.ajax({
            url: ajaxurl, type: 'POST',
            data: { action: 'pfm_save_consent', consent: isGiven ? '1' : '0', feed_id: feedData.feed_id || 0, security: pfmNonce }
        });
    }

    function markCompleted(cb) {
        $.ajax({
            url: ajaxurl, type: 'POST',
            data: { action: 'pfm_wizard_mark_completed', security: pfmNonce },
            complete: function () { cb && cb(); }
        });
    }

    // ─── Companion plugins ────────────────────────────────────────────────────

    function applyCompanionStatuses() {
        installedCount = 0;
        applyPluginStatus($('#pfmInstallWpfBtn'), companionPlugins['wpfunnels'] || 'not_installed', 'wpfunnels',
            'Install WPFunnels (Free) →', 'Activate WPFunnels →', 'Already Activated ✓');
        applyPluginStatus($('#pfmInstallClBtn'), companionPlugins['cart-lift'] || 'not_installed', 'cart-lift',
            'Install Cart Lift (Free) →', 'Activate Cart Lift →', 'Already Activated ✓');

        if ((companionPlugins['wpfunnels'] || 'not_installed') === 'not_installed') {
            $('#pfmInstallWpfBtn').off('click').on('click', function () {
                handlePluginInstall($(this), 'wpfunnels', 'Install WPFunnels (Free) →', 'Installed ✓');
            });
        }
        if ((companionPlugins['cart-lift'] || 'not_installed') === 'not_installed') {
            $('#pfmInstallClBtn').off('click').on('click', function () {
                handlePluginInstall($(this), 'cart-lift', 'Install Cart Lift (Free) →', 'Installed ✓');
            });
        }
    }

    function applyPluginStatus($btn, status, slug, installLabel, activateLabel, activeLabel) {
        if (status === 'active') {
            $btn.addClass('is-installed').prop('disabled', true).text(activeLabel);
            $btn.closest('.pfm-upsell-card').addClass('is-already-active');
            installedCount++;
        } else if (status === 'installed') {
            $btn.text(activateLabel).off('click').on('click', function () {
                handlePluginInstall($(this), slug, activateLabel, 'Activated ✓');
            });
        }
    }

    function handlePluginInstall($btn, slug, origLabel, successLabel) {
        if ($btn.hasClass('is-installing') || $btn.hasClass('is-installed')) return;
        injectSpinnerCSS();
        $btn.addClass('is-installing').prop('disabled', true).html(spinnerHtml('Installing...'));
        $.ajax({
            url: ajaxurl, type: 'POST',
            data: { action: 'pfm_install_activate_plugin', plugin_slug: slug, security: pfmNonce },
            success: function (r) {
                $btn.removeClass('is-installing');
                if (r && r.success) {
                    $btn.addClass('is-installed').prop('disabled', true).text(successLabel);
                    do_action && do_action('product-feed-manager_telemetry_track',
                        'wpfunnels' === slug ? 'pfm_wizard_companion_install_wpfunnels' : 'pfm_wizard_companion_install_cartlift',
                        { plugin: slug });
                } else {
                    $btn.prop('disabled', false).text(origLabel);
                }
            },
            error: function () { $btn.removeClass('is-installing').prop('disabled', false).text(origLabel); }
        });
    }

    // ─── Register onboarding ──────────────────────────────────────────────────

    registerOnboarding({
        plugin  : 'product-feed-manager',
        version : '1.0.0',
        telemetry: {
            onSetupCompleted      : function () {},
            onFirstStrikeCompleted: function () {}
        },
        steps: [
            // ── Step 1: Merchant ──────────────────────────────────────────────
            {
                id: 'merchant', title: 'Select a Channel', canSkip: false, canGoBack: false,
                mount: function (container, ctx) {
                    showStep('pfm-step-1');
                    renderPopularGrid();

                    $('#pfmMerchantSearch').off('input').on('input', function () {
                        renderSearchResults($(this).val().trim());
                    });

                    $('#pfmStep1ContinueBtn').off('click').on('click', function () {
                        if (selectedMerchantId) ctx.goNext();
                    });

                    // "remind me later" dismiss
                    $('#pfmRemindLaterBtn').off('click').on('click', function (e) {
                        e.preventDefault();
                        $.ajax({
                            url: ajaxurl, type: 'POST',
                            data: { action: 'pfm_wizard_dismiss', security: pfmNonce, step_index: 0, step_id: 'merchant' },
                            complete: function () { window.location.href = 'index.php'; }
                        });
                    });
                },
                onNext: function () { return !!selectedMerchantId; }
            },

            // ── Step 2: Smart Config ──────────────────────────────────────────
            {
                id: 'configure', title: 'Smart Configuration', canSkip: false, canGoBack: true,
                mount: function (container, ctx) {
                    showStep('pfm-step-2');
                    mountStep2();

                    $('#pfmStep2BackBtn').off('click').on('click', function () { ctx.goBack(); });

                    $('#pfmCreateFeedBtn').off('click').on('click', function () {
                        var name = $('#pfmFeedNameInput').val().trim() || feedData.name;
                        feedData.name = name;

                        injectSpinnerCSS();
                        var $btn = $(this).prop('disabled', true).html(spinnerHtml('Creating...'));

                        $.ajax({
                            url: ajaxurl, type: 'POST',
                            data: {
                                action: 'pfm_create_feed',
                                feed_name: feedData.name,
                                merchant: feedData.merchant_slug,
                                feed_format: feedData.format,
                                update_frequency: feedData.frequency,
                                mappings: JSON.stringify(feedData.mappings || []),
                                security: pfmNonce
                            },
                            success: function (r) {
                                if (r.success && r.data) {
                                    feedData.feed_id  = r.data.feed_id;
                                    feedData.feed_url = r.data.feed_url;
                                    feedData.edit_url = r.data.edit_url;

                                    $btn.html(spinnerHtml('Generating...'));
                                    var bi = r.data.batch_info;
                                    generateFeedBatch(feedData.feed_id, bi.total_products, 0, 1, bi.per_batch, bi.total_batch,
                                        function () {
                                            $btn.prop('disabled', false).text('Create My Feed →');
                                            ctx.goNext();
                                        },
                                        function () {
                                            $btn.prop('disabled', false).text('Create My Feed →');
                                            ctx.goNext(); // proceed even on generation error
                                        }
                                    );
                                } else {
                                    $btn.prop('disabled', false).text('Create My Feed →');
                                    var msg = (r.data && r.data.message) ? r.data.message : 'Failed to create feed. Please try again.';
                                    alert(msg);
                                }
                            },
                            error: function () {
                                $btn.prop('disabled', false).text('Create My Feed →');
                                alert('An error occurred. Please try again.');
                            }
                        });
                    });
                },
                onNext: function () { return !!feedData.feed_id; }
            },

            // ── Step 3: Aha + Consent ─────────────────────────────────────────
            {
                id: 'aha', title: 'Feed Ready', canSkip: false, canGoBack: false,
                mount: function (container, ctx) {
                    showStep('pfm-step-3');
                    mountStep3();
                }
            }
        ],
        firstStrike: {
            label : 'Product Feed Created',
            verify: function () { return !!feedData.feed_id; }
        }
    });

    engine.start();

    tracker.on('step_changed', function () {
        var step = engine.getCurrentStep();
        if (step && step.mount) {
            step.mount(document.getElementById('pfm-wizard-app'), engine.getStepContext());
        }
    });

    // Initial render
    var step = engine.getCurrentStep();
    if (step && step.mount) {
        step.mount(document.getElementById('pfm-wizard-app'), engine.getStepContext());
    }
});
