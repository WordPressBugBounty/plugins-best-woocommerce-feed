jQuery(document).ready(function ($) {
    var LinnoOnboarding = window.LinnoOnboarding;
    var registerOnboarding = LinnoOnboarding.registerOnboarding;
    var engine = LinnoOnboarding.engine;
    var tracker = LinnoOnboarding.tracker;

    // Get assets URL from PHP
    var assetsUrl = typeof pfmMerchantsData !== 'undefined' ? pfmMerchantsData.assetsUrl : '';
    
    // Popular merchants with images from local folder
    var popularMerchants = [
        { id: 'google', name: 'Google Shopping', img: assetsUrl + 'google.webp', isPro: false },
        { id: 'facebook', name: 'Facebook Catalog', img: assetsUrl + 'facebook.webp', isPro: false },
        { id: 'tiktok', name: 'TikTok Ads Catalog', img: assetsUrl + 'tiktok.webp', isPro: false },
        { id: 'instagram', name: 'Instagram (by Facebook)', img: assetsUrl + 'instagram.webp', isPro: false },
        { id: 'custom', name: 'Create Custom Feed', img: assetsUrl + 'custom.webp', isPro: false }
    ];

    // All other merchants (pre-loaded from PHP via wp_localize_script)
    var allMerchants = typeof pfmMerchantsData !== 'undefined' ? pfmMerchantsData.merchants : [];
    var isPremiumUser = typeof pfmMerchantsData !== 'undefined' ? pfmMerchantsData.isPremium : false;
    
    // Generate monogram color based on merchant name
    function getMonogramColor(name) {
        var hash = 0;
        for (var i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        return Math.abs(hash % 10) + 1;
    }

    // Telemetry state & helpers
    var telemetryData = { plugin: 'product-feed-manager', version: '1.0.0' };
    var firedFirstStrike = false;
    var firedSetupCompleted = false;

    // State
    var selectedMerchantId = null;
    var selectedMerchantName = null;
    var feedData = {};
    var isWelcomeStepSubmitting = false;

    // Helper function to convert mappings array to serialized form string
    // The form expects fields named fc[index][field]
    function serializeMappings(mappings) {
        var params = [];

        // Force-enable parent products and variations in generated feed config
        params.push('rex_feed_parent_product=' + encodeURIComponent('yes'));
        params.push('rex_feed_variations=' + encodeURIComponent('yes'));
        params.push('rex_feed_variable_product=' + encodeURIComponent('yes'));

        if (!mappings || !Array.isArray(mappings) || mappings.length === 0) {
            return params.join('&');
        }
        
        // Convert each mapping to form field format using 'fc' structure
        mappings.forEach(function(mapping, index) {
            params.push('fc[' + index + '][attr]=' + encodeURIComponent(mapping.attr || ''));
            params.push('fc[' + index + '][type]=' + encodeURIComponent(mapping.type || ''));
            
            if (mapping.type === 'meta') {
                params.push('fc[' + index + '][meta_key]=' + encodeURIComponent(mapping.meta_key || ''));
            } else if (mapping.type === 'static') {
                params.push('fc[' + index + '][st_value]=' + encodeURIComponent(mapping.st_value || ''));
            }
            
            // Add optional fields
            if (mapping.prefix) {
                params.push('fc[' + index + '][prefix]=' + encodeURIComponent(mapping.prefix));
            }
            if (mapping.suffix) {
                params.push('fc[' + index + '][suffix]=' + encodeURIComponent(mapping.suffix));
            }
            if (mapping.escape) {
                params.push('fc[' + index + '][escape]=' + encodeURIComponent(mapping.escape));
            }
            if (mapping.limit) {
                params.push('fc[' + index + '][limit]=' + encodeURIComponent(mapping.limit));
            }
        });
        
        return params.join('&');
    }

    // Feed generation batch helper
    function generateFeedBatch(feedId, totalProducts, offset, currentBatch, perBatch, totalBatches, successCallback, errorCallback) {
        
        // Serialize the feed config to match form structure (fc[index][field])
        var serializedConfig = serializeMappings(feedData.mappings || []);
        
        var payload = {
            merchant: feedData.merchant_slug || selectedMerchantId,
            feed_format: feedData.format || 'xml',
            feed_config: serializedConfig,
            info: {
                post_id: feedId,
                title: feedData.name || '',
                desc: feedData.name || '',
                offset: offset,
                batch: currentBatch,
                per_batch: perBatch,
                total_batch: totalBatches
            },
            products: {
                products_scope: 'all',
                tags: [],
                cats: [],
                brands: [],
                data: ''
            }
        };
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'rexfeed-generate-feed',
                payload: payload,
                security: pfmNonce
            },
            success: function(response) {
                console.log('Batch', currentBatch, 'response:', response);
                
                if (response && response.msg === 'finish') {
                    // Generation complete
                    console.log('Feed generation complete!');
                    if (successCallback) successCallback();
                } else if (response && response.msg === 'failForEmptyProduct') {
                    console.log('No products available');
                    if (errorCallback) errorCallback('No products available to generate feed');
                } else if (currentBatch < totalBatches) {
                    // Continue with next batch
                    var newOffset = offset + perBatch;
                    var newBatch = currentBatch + 1;
                    
                    // Recursive call for next batch
                    setTimeout(function() {
                        generateFeedBatch(feedId, totalProducts, newOffset, newBatch, perBatch, totalBatches, successCallback, errorCallback);
                    }, 100);
                } else {
                    console.log('All batches processed but no finish signal');
                    if (successCallback) successCallback();
                }
            },
            error: function(xhr, status, error) {
                console.error('Batch generation error:', error);
                if (errorCallback) errorCallback(error);
            }
        });
    }

    function fireFirstStrikeCompleted() {
        if (firedFirstStrike) return;
        firedFirstStrike = true;        
        // Track first feed creation via AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pfm_track_first_strike',
                feed_data: {
                    name: feedData.name || '',
                    merchant: selectedMerchantName || '',
                    format: feedData.format || '',
                    frequency: feedData.frequency || ''
                },
                security: pfmNonce
            },
            success: function(response) {
                console.log('First strike tracked:', response);
            },
            error: function(xhr, status, error) {
                console.log('Failed to track first strike:', error);
            }
        });
    }

    function fireSetupCompleted() {
        if (firedSetupCompleted) return;
        firedSetupCompleted = true;
        console.log('Telemetry: setup_completed', telemetryData);
        
        // Track setup completion via AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pfm_track_setup_completed'
            },
            success: function(response) {
                console.log('Setup completed tracked:', response);
            },
            error: function(xhr, status, error) {
                console.log('Failed to track setup completed:', error);
            }
        });
    }

    // Navigation helper function
    function navigateTo(stepId) {
        // Add step- prefix to stepId for consistency
        var fullStepId = 'step-' + stepId;
        var $wizardExit = $('#wizardExit');

        // Show/Hide Sidebar
        if (fullStepId === 'step-welcome') {
            $('#main-sidebar').hide();
            $('#onboarding-app').removeClass('sidebar-visible');
        } else {
            $('#main-sidebar').css('display', 'flex');
            $('#onboarding-app').addClass('sidebar-visible');
        }

        if (fullStepId === 'step-welcome' || fullStepId === 'step-complete') {
            $wizardExit.hide();
        } else {
            $wizardExit.show();
        }

        // Toggle Active Step
        $('.step').removeClass('active');
        $('#' + fullStepId).addClass('active');

        // Update Sidebar Navigation UI
        $('.nav-item').removeClass('active completed');
        $('.nav-item[data-step="' + fullStepId + '"]').addClass('active');

        // Mark previous steps as completed (checkmark)
        var stepOrder = ['step-welcome', 'step-select-merchant', 'step-feed-settings', 'step-attribute-mapping', 'step-complete'];
        var currentIndex = stepOrder.indexOf(fullStepId);

        var checkmarkSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="11" height="7" viewBox="0 0 11 7" fill="none"><path d="M9.86725 0.315308C9.55785 0.0282705 9.05543 0.0284515 8.74561 0.315308L3.69748 4.9925L1.4538 2.91379C1.14399 2.62675 0.641783 2.62675 0.33197 2.91379C0.0221559 3.20082 0.0221559 3.66611 0.33197 3.95314L3.13645 6.55144C3.29126 6.69487 3.49425 6.76676 3.69726 6.76676C3.90028 6.76676 4.10347 6.69505 4.25828 6.55144L9.86725 1.35465C10.1771 1.06781 10.1771 0.602326 9.86725 0.315308Z" fill="white" stroke="white" stroke-width="0.2"></path></svg>';

        stepOrder.forEach(function(step, index) {
            var $navItem = $('.nav-item[data-step="' + step + '"]');
            var $circle = $navItem.find('.nav-circle');

            if (index < currentIndex) {
                $navItem.addClass('completed');
                $circle.html(checkmarkSvg);
            } else {
                $navItem.removeClass('completed');
                // Restore number if not completed
                $circle.text(index + 1);
            }
        });
    }

    // Register onboarding
    registerOnboarding({
        plugin: 'product-feed-manager',
        version: '1.0.0',
        telemetry: {
            onSetupCompleted: function () {
                fireSetupCompleted();
            },
            onFirstStrikeCompleted: function () {
                fireFirstStrikeCompleted();
            }
        },
        steps: [
            // =====================
            // STEP 1: Welcome
            // =====================
            {
                id: 'welcome',
                title: 'Welcome to Product Feed Manager',
                description: 'Create powerful product feeds for major shopping platforms in just a few steps.',
                canSkip: false,
                canGoBack: false,
                mount: function (container, context) {
                    navigateTo('welcome');
                    $('#getStartedBtn').off('click').on('click', function () {
                        if (isWelcomeStepSubmitting) {
                            return;
                        }

                        isWelcomeStepSubmitting = true;

                        var $getStartedBtn = $(this);
                        var originalBtnText = $getStartedBtn.text();
                        var loadingText = $getStartedBtn.data('loading-text') || 'Please wait...';

                        $getStartedBtn
                            .prop('disabled', true)
                            .addClass('is-loading')
                            .text(loadingText);

                        function goToNextStep() {
                            try {
                                context.goNext();
                            } catch (e) {
                                isWelcomeStepSubmitting = false;
                                $getStartedBtn
                                    .prop('disabled', false)
                                    .removeClass('is-loading')
                                    .text(originalBtnText);
                                console.log('Failed to move to next step:', e);
                            }
                        }

                        var consentChecked = $('#consentCheckbox').is(':checked');
                        
                        // Save consent preference
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'pfm_save_consent',
                                consent: consentChecked ? '1' : '0',
                                security: pfmNonce
                            },
                            success: function(response) {                                
                                if (consentChecked) {
                                    $.ajax({
                                        url: ajaxurl,
                                        type: 'POST',
                                        data: {
                                            action: 'pfm_track_setup_start'
                                        },
                                        success: function(response) {
                                            console.log('Setup start tracked:', response);
                                        },
                                        error: function(xhr, status, error) {
                                            console.log('Failed to track setup start:', error);
                                        }
                                    });
                                }
                                
                                goToNextStep();
                            },
                            error: function(xhr, status, error) {
                                console.log('Failed to save consent:', error);
                                // Continue anyway
                                goToNextStep();
                            }
                        });
                    });
                }
            },

            // =====================
            // STEP 2: Select Merchant
            // =====================
            {
                id: 'select-merchant',
                title: 'Select Merchant',
                canSkip: false,
                canGoBack: true,
                mount: function (container, context) {
                    navigateTo('select-merchant');

                    var $popularGrid = $('#popularGrid');
                    var $searchResults = $('#searchResults');
                    var $search = $('#merchantSearch');
                    var $continueBtn = $('#merchantContinueBtn');

                    // Render popular merchants
                    function renderPopularMerchants() {
                        $popularGrid.empty();
                        popularMerchants.forEach(function (m) {
                            var isSelected = m.id === selectedMerchantId;
                            var $card = $('<div class="popular-card' + (isSelected ? ' selected' : '') + '" data-id="' + m.id + '"></div>');

                            var $img = $('<img class="merchant-logo" src="' + m.img + '" alt="' + m.name + '">');
                            var $name = $('<div class="merchant-name">' + m.name + '</div>');
                            var $check = $('<div class="merchant-check">' +
                                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                                '<polyline points="20 6 9 17 4 12"/>' +
                                '</svg>' +
                                '</div>');

                            $card.append($img, $name, $check);
                            $popularGrid.append($card);

                            $card.on('click', function () {
                                selectedMerchantId = m.id;
                                selectedMerchantName = m.name;
                                $('.popular-card, .result-card').removeClass('selected');
                                $(this).addClass('selected');
                                $continueBtn.prop('disabled', false);
                            });
                        });
                    }

                    // Search other merchants
                    function searchMerchants(query) {
                        var $searchResultsContainer = $('#searchResultsContainer');
                        var $popularSection = $('#popularSection');
                        
                        if (query.length < 3) {
                            $searchResults.empty();
                            $searchResultsContainer.hide();
                            $popularSection.show();
                            
                            // Clear selection and disable continue button
                            selectedMerchantId = null;
                            selectedMerchantName = null;
                            $('.popular-card, .result-card').removeClass('selected');
                            $continueBtn.prop('disabled', true);
                            return;
                        }

                        var searchTerm = query.toLowerCase();
                        var results = allMerchants.filter(function (m) {
                            return m.name.toLowerCase().includes(searchTerm);
                        });

                        // Always add custom feed to search results
                        var customFeed = popularMerchants.find(function(m) { return m.id === 'custom'; });
                        if (customFeed) {
                            // Prevent duplicate custom feed
                            var alreadyIncluded = results.some(function(m) { return m.id === 'custom'; });
                            if (!alreadyIncluded) {
                                results.push(customFeed);
                            }
                        }

                        $searchResults.empty();
                        $searchResultsContainer.show();
                        $popularSection.hide();

                        if (results.length === 0) {
                            // Show custom feed card if nothing else found
                            if (customFeed) {
                                var cardClasses = 'result-card';
                                var isSelected = customFeed.id === selectedMerchantId;
                                if (isSelected) cardClasses += ' selected';
                                var $card = $('<div class="' + cardClasses + '" data-id="' + customFeed.id + '"></div>');
                                var $img = $('<img class="merchant-logo" src="' + customFeed.img + '" alt="' + customFeed.name + '">');
                                var $name = $('<div class="merchant-name">' + customFeed.name + '</div>');
                                var $check = $('<div class="merchant-check">' +
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                                    '<polyline points="20 6 9 17 4 12"/>' +
                                    '</svg>' +
                                    '</div>');
                                $card.append($img, $name, $check);
                                $searchResults.append($card);
                                $card.on('click', function () {
                                    selectedMerchantId = customFeed.id;
                                    selectedMerchantName = customFeed.name;
                                    $('.popular-card, .result-card').removeClass('selected');
                                    $(this).addClass('selected');
                                    $continueBtn.prop('disabled', false);
                                });
                            }
                            return;
                        }

                        results.forEach(function (m) {
                            var isSelected = m.id === selectedMerchantId;
                            var isDisabled = m.isPro && !isPremiumUser;
                            var cardClasses = 'result-card';
                            if (isSelected) cardClasses += ' selected';
                            if (isDisabled) cardClasses += ' disabled';
                            var $card = $('<div class="' + cardClasses + '" data-id="' + m.id + '"></div>');
                            if (m.id === 'custom') {
                                var $img = $('<img class="merchant-logo" src="' + m.img + '" alt="' + m.name + '">');
                                var $name = $('<div class="merchant-name">' + m.name + '</div>');
                                var $check = $('<div class="merchant-check">' +
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                                    '<polyline points="20 6 9 17 4 12"/>' +
                                    '</svg>' +
                                    '</div>');
                                $card.append($img, $name, $check);
                            } else {
                                // Create monogram avatar
                                var firstLetter = m.name.charAt(0).toUpperCase();
                                var colorClass = 'monogram-color-' + getMonogramColor(m.name);
                                var $monogram = $('<div class="merchant-monogram ' + colorClass + '">' + firstLetter + '</div>');
                                var $name = $('<div class="merchant-name">' + m.name + '</div>');
                                $card.append($monogram, $name);
                                // Add pro badge only if user doesn't have premium and merchant is pro
                                if (m.isPro && !isPremiumUser) {
                                    var $badge = $('<span class="merchant-badge">Pro</span>');
                                    $card.append($badge);
                                }
                                // Add check mark for selection (only if not disabled)
                                if (!isDisabled) {
                                    var $check = $('<div class="merchant-check">' +
                                        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                                        '<polyline points="20 6 9 17 4 12"/>' +
                                        '</svg>' +
                                        '</div>');
                                    $card.append($check);
                                }
                            }
                            $searchResults.append($card);
                            // Only allow click if not disabled
                            if (!isDisabled) {
                                $card.on('click', function () {
                                    selectedMerchantId = m.id;
                                    selectedMerchantName = m.name;
                                    $('.popular-card, .result-card').removeClass('selected');
                                    $(this).addClass('selected');
                                    $continueBtn.prop('disabled', false);
                                });
                            }
                        });
                    }

                    // Search input handler
                    $search.off('input').on('input', function () {
                        searchMerchants($(this).val());
                    });

                    // Initial render
                    $continueBtn.prop('disabled', !selectedMerchantId);
                    renderPopularMerchants();

                    $('#merchantBackBtn').off('click').on('click', function () {
                        context.goBack();
                    });

                    $continueBtn.off('click').on('click', function () {
                        if (selectedMerchantId) {
                            context.goNext();
                        }
                    });

                    $('.exit').off('click').on('click', function () {
                        window.location.href = 'edit.php?post_type=product-feed';
                    });
                },
                onNext: function () {
                    return !!selectedMerchantId;
                }
            },

            // =====================
            // STEP 3: Feed Settings (Renamed from Create Feed)
            // =====================
            {
                id: 'feed-settings',
                title: 'Feed Settings',
                canSkip: false,
                canGoBack: true,
                mount: function (container, context) {
                    navigateTo('feed-settings');

                    var $feedName = $('#feedName');
                    var $continueBtn = $('#feedContinueBtn');

                    function validateForm() {
                        var isValid = $feedName.val().trim() !== '';
                        $continueBtn.prop('disabled', !isValid);
                    }

                    $feedName.off('input').on('input', validateForm);
                    validateForm();

                    $('#feedBackBtn').off('click').on('click', function () {
                        context.goBack();
                    });

                    $continueBtn.off('click').on('click', function () {
                        if ($feedName.val().trim()) {
                            // Save feed settings to feedData
                            feedData.name = $feedName.val().trim();
                            feedData.format = $('#feedFormat').val();
                            feedData.frequency = $('#updateFrequency').val();
                            feedData.merchant = selectedMerchantName;
                            feedData.merchant_slug = selectedMerchantId;
                            
                            context.goNext();
                        }
                    });

                    $('.exit').off('click').on('click', function () {
                        window.location.href = 'edit.php?post_type=product-feed';
                    });
                },
                onNext: function () {
                    var name = $('#feedName').val();
                    return name && name.trim() !== '';
                }
            },

            // =====================
            // STEP 4: Attribute Mapping
            // =====================
            {
                id: 'attribute-mapping',
                title: 'Attribute Mapping',
                canSkip: false,
                canGoBack: true,
                mount: function (container, context) {
                    navigateTo('attribute-mapping');

                    var $publishBtn = $('#publishBtn');
                    var $mappingTableBody = $('#mappingTableBody');
                    var currentMappings = [];
                    var merchantAttributes = {};
                    var wcAttributes = {};

                    // Reset custom dropdown artifacts on each mount
                    $('.custom-attr-dropdown-container').remove();
                    $('.mapping-table-wrapper').css('margin-bottom', '32px');

                    // Custom feed dropdown logic
                    if (feedData.merchant_slug === 'custom') {
                        // Add native dropdown UI for custom feed
                        var $customAttrDropdown = $('<div class="custom-attr-dropdown-container" style="display:flex;align-items:center;justify-content:flex-start;width:auto;margin-top:0;margin-bottom:12px;"></div>');
                        var $dropdown = $('<select class="mapping-select custom-attr-dropdown" id="addCustomAttrBtn" aria-label="Add New Attribute" style="width:auto;min-width:220px;max-width:280px;"></select>');
                        $dropdown.append('<option value="" selected disabled>Add New Attribute</option>');
                        $dropdown.append('<option value="attribute">New Attribute</option>');
                        $dropdown.append('<option value="custom">Custom Attribute</option>');
                        $customAttrDropdown.append($dropdown);

                        // Keep dropdown outside wrapper with controlled spacing
                        var $wrapper = $('.mapping-table-wrapper');
                        if ($wrapper.length) {
                            $wrapper.css('margin-bottom', '8px');
                            $wrapper.after($customAttrDropdown);
                        }

                        // Add row on selection
                        $dropdown.on('change', function() {
                            var type = $(this).val();
                            if (!type) {
                                return;
                            }
                            var newRow;
                            if (type === 'attribute') {
                                // Prepopulate with last row's data if exists
                                var metaKey = '';
                                if (currentMappings.length > 0) {
                                    var lastRow = currentMappings[currentMappings.length - 1];
                                    metaKey = lastRow.meta_key || '';
                                }
                                newRow = {
                                    label: 'New Attribute',
                                    type: 'meta', // ensure type is meta for select
                                    meta_key: metaKey,
                                    filter: 'Default',
                                    char_limit: '',
                                };
                            } else if (type === 'custom') {
                                // Custom: mimic admin.js approach
                                var filterVal = 'Default';
                                var charLimitVal = '255';
                                var stValue = '';
                                if (currentMappings.length > 0) {
                                    var lastRow = currentMappings[currentMappings.length - 1];
                                    filterVal = lastRow.filter || 'Default';
                                    charLimitVal = lastRow.char_limit || '255';
                                    stValue = lastRow.st_value || '';
                                }
                                newRow = {
                                    label: '', // user will enter custom attribute name
                                    type: 'Custom',
                                    st_value: stValue,
                                    filter: filterVal,
                                    char_limit: charLimitVal,
                                };
                            }
                            currentMappings.push(newRow);
                            renderMappingsTable();
                            $(this).val('');
                        });
                    }

                    // Load template mappings
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'pfm_get_template_mappings',
                            merchant: selectedMerchantId
                        },
                        success: function(response) {
                            console.log('Template mappings response:', response);
                            if (response.success && response.data && response.data.mappings) {
                                currentMappings = response.data.mappings;
                                merchantAttributes = response.data.merchant_attributes || {};
                                wcAttributes = response.data.wc_attributes || {};
                                
                                // Store mappings in feedData
                                feedData.mappings = currentMappings;
                                
                                renderMappingsTable();

                                // Enable publish button
                                $publishBtn.prop('disabled', false);
                            } else {
                                $mappingTableBody.html('<tr><td colspan="4" style="text-align: center; padding: 20px; color: #d63638;">Failed to load mappings</td></tr>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Failed to load mappings:', error);
                            $mappingTableBody.html('<tr><td colspan="4" style="text-align: center; padding: 20px; color: #d63638;">Error loading mappings</td></tr>');
                        }
                    });

                    function renderMappingsTable() {
                        $mappingTableBody.empty();
                        
                        currentMappings.forEach(function(mapping, index) {
                            var $row = $('<tr data-index="' + index + '"></tr>');
                            // Attribute column
                            var $attrCell = $('<td></td>');
                            if (mapping.type === 'Custom') {
                                var $attrInput = $('<input type="text" class="mapping-input attr-input" data-field="label" value="' + (mapping.label || '') + '" placeholder="Attribute Name">');
                                $attrCell.append($attrInput);
                            } else {
                                var $attrSelect = $('<select class="mapping-select attr-select" data-field="attr">');
                                if (typeof merchantAttributes === 'object') {
                                    var hasGroups = false;
                                    $.each(merchantAttributes, function(key, value) {
                                        if (typeof value === 'object' && value !== null) {
                                            hasGroups = true;
                                            return false; // break
                                        }
                                    });
                                    if (hasGroups) {
                                        $.each(merchantAttributes, function(group, attrs) {
                                            if (typeof attrs === 'object' && attrs !== null) {
                                                var $optgroup = $('<optgroup label="' + group + '">');
                                                $.each(attrs, function(key, label) {
                                                    var selected = key === mapping.attr ? ' selected' : '';
                                                    $optgroup.append('<option value="' + key + '"' + selected + '>' + label + '</option>');
                                                });
                                                $attrSelect.append($optgroup);
                                            }
                                        });
                                    } else {
                                        $.each(merchantAttributes, function(key, label) {
                                            var selected = key === mapping.attr ? ' selected' : '';
                                            $attrSelect.append('<option value="' + key + '"' + selected + '>' + label + '</option>');
                                        });
                                    }
                                }
                                $attrCell.append($attrSelect);
                            }
                            // Type column
                            var $typeCell = $('<td></td>');
                            var $typeSelect = $('<select class="mapping-select type-select" data-field="type">');
                            $typeSelect.append('<option value="meta"' + (mapping.type === 'meta' ? ' selected' : '') + '>Attribute</option>');
                            $typeSelect.append('<option value="static"' + (mapping.type === 'static' ? ' selected' : '') + '>Static</option>');
                            $typeCell.append($typeSelect);
                            // Value column
                            var $valueCell = $('<td></td>');
                            if (mapping.type === 'meta' || (mapping.type === 'Custom' && (!mapping.st_value || mapping.st_value === '') ) ) {
                                // Show dropdown for meta or for custom row if type is meta (default)
                                var $valueSelect = $('<select class="mapping-select value-select" data-field="meta_key">');
                                $.each(wcAttributes, function(group, attrs) {
                                    var $optgroup = $('<optgroup label="' + group + '">');
                                    $.each(attrs, function(key, label) {
                                        var selected = key === mapping.meta_key ? ' selected' : '';
                                        $optgroup.append('<option value="' + key + '"' + selected + '>' + label + '</option>');
                                    });
                                    $valueSelect.append($optgroup);
                                });
                                $valueCell.append($valueSelect);
                            } else if (mapping.type === 'static' || (mapping.type === 'Custom' && mapping.st_value && mapping.st_value !== '')) {
                                // Show text input for static or for custom row if type is static
                                var $valueInput = $('<input type="text" class="mapping-input value-input" data-field="st_value" value="' + (mapping.st_value || '') + '" placeholder="Enter static value" autocomplete="off">');
                                $valueCell.append($valueInput);
                            }
                            $row.append($attrCell, $typeCell, $valueCell);
                            $mappingTableBody.append($row);
                        });
                    }

                    // Handle type change - switch between dropdown and input
                    $mappingTableBody.on('change', '.type-select', function() {
                        var $row = $(this).closest('tr');
                        var index = $row.data('index');
                        var newType = $(this).val();
                        var $valueCell = $row.find('td').eq(2);

                        // Update mapping
                        currentMappings[index].type = newType;

                        // Re-render value cell for custom attribute row
                        $valueCell.empty();
                        if (currentMappings[index].type === 'Custom') {
                            if (newType === 'meta') {
                                var $valueSelect = $('<select class="mapping-select value-select" data-field="meta_key">');
                                $.each(wcAttributes, function(group, attrs) {
                                    var $optgroup = $('<optgroup label="' + group + '">');
                                    $.each(attrs, function(key, label) {
                                        $optgroup.append('<option value="' + key + '">' + label + '</option>');
                                    });
                                    $valueSelect.append($optgroup);
                                });
                                $valueCell.append($valueSelect);
                                currentMappings[index].meta_key = $valueSelect.val();
                                currentMappings[index].st_value = '';
                            } else {
                                var $valueInput = $('<input type="text" class="mapping-input value-input" data-field="st_value" placeholder="Enter static value">');
                                $valueCell.append($valueInput);
                                currentMappings[index].st_value = '';
                                currentMappings[index].meta_key = '';
                            }
                        } else {
                            // Non-custom row logic (unchanged)
                            if (newType === 'meta') {
                                var $valueSelect = $('<select class="mapping-select value-select" data-field="meta_key">');
                                $.each(wcAttributes, function(group, attrs) {
                                    var $optgroup = $('<optgroup label="' + group + '">');
                                    $.each(attrs, function(key, label) {
                                        $optgroup.append('<option value="' + key + '">' + label + '</option>');
                                    });
                                    $valueSelect.append($optgroup);
                                });
                                $valueCell.append($valueSelect);
                                currentMappings[index].meta_key = $valueSelect.val();
                                currentMappings[index].st_value = '';
                            } else {
                                var $valueInput = $('<input type="text" class="mapping-input value-input" data-field="st_value" placeholder="Enter static value">');
                                $valueCell.append($valueInput);
                                currentMappings[index].st_value = '';
                                currentMappings[index].meta_key = '';
                            }
                        }

                        feedData.mappings = currentMappings;
                    });

                    // Handle field changes
                    $mappingTableBody.on('change', '.attr-select, .value-select', function() {
                        var $row = $(this).closest('tr');
                        var index = $row.data('index');
                        var field = $(this).data('field');
                        var value = $(this).val();
                        
                        currentMappings[index][field] = value;
                        feedData.mappings = currentMappings;
                    });

                    $mappingTableBody.on('input', '.value-input', function() {
                        var $row = $(this).closest('tr');
                        var index = $row.data('index');
                        var field = $(this).data('field');
                        var value = $(this).val();
                        
                        currentMappings[index][field] = value;
                        feedData.mappings = currentMappings;
                    });

                    $('#mappingBackBtn').off('click').on('click', function () {
                        context.goBack();
                    });

                    $publishBtn.off('click').on('click', function () {
                        // Ensure mappings are in feedData
                        feedData.mappings = currentMappings;
                        
                        console.log('Creating feed with data:', {
                            name: feedData.name,
                            merchant: feedData.merchant_slug,
                            format: feedData.format,
                            frequency: feedData.frequency,
                            mappings_count: currentMappings.length
                        });
                        
                        // Show loading state
                        $publishBtn.prop('disabled', true).html('<span style="display: inline-flex; align-items: center; gap: 8px;"><svg class="spinner" width="16" height="16" viewBox="0 0 50 50" style="animation: rotate 1s linear infinite;"><circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="5" stroke-dasharray="31.4 31.4" stroke-linecap="round" style="animation: dash 1.5s ease-in-out infinite;"></circle></svg>Creating Feed...</span>');
                        
                        // Add spinner animation styles if not already present
                        if (!$('#spinner-styles').length) {
                            $('<style id="spinner-styles">@keyframes rotate { 100% { transform: rotate(360deg); } } @keyframes dash { 0% { stroke-dasharray: 1 31.4; stroke-dashoffset: 0; } 50% { stroke-dasharray: 15.7 15.7; stroke-dashoffset: -15.7; } 100% { stroke-dasharray: 1 31.4; stroke-dashoffset: -31.4; } }</style>').appendTo('head');
                        }
                        
                        // Create the feed post via AJAX
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'pfm_create_feed',
                                feed_name: feedData.name,
                                merchant: feedData.merchant_slug,
                                feed_format: feedData.format,
                                update_frequency: feedData.frequency,
                                mappings: JSON.stringify(currentMappings),
                                security: pfmNonce
                            },
                            success: function(response) {
                                console.log('Feed creation response:', response);
                                if (response.success && response.data && response.data.batch_info) {
                                    // Update feed data
                                    feedData.feed_id = response.data.feed_id;
                                    feedData.feed_url = response.data.feed_url;
                                    feedData.edit_url = response.data.edit_url;
                                    
                                    // Start batch feed generation
                                    $publishBtn.html('<span style="display: inline-flex; align-items: center; gap: 8px;"><svg class="spinner" width="16" height="16" viewBox="0 0 50 50" style="animation: rotate 1s linear infinite;"><circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="5" stroke-dasharray="31.4 31.4" stroke-linecap="round" style="animation: dash 1.5s ease-in-out infinite;"></circle></svg>Generating Feed...</span>');
                                    
                                    generateFeedBatch(
                                        response.data.feed_id,
                                        response.data.batch_info.total_products,
                                        0, // offset
                                        1, // current batch
                                        response.data.batch_info.per_batch,
                                        response.data.batch_info.total_batch,
                                        function() {
                                            fireFirstStrikeCompleted();
                                            // Success callback - move to next step
                                            $publishBtn.prop('disabled', false).html('Create Feed');
                                            context.goNext();
                                        },
                                        function(error) {
                                            // Error callback
                                            console.error('Feed generation failed:', error);
                                            alert('Feed created but generation failed: ' + error);
                                            $publishBtn.prop('disabled', false).html('Create Feed');
                                        }
                                    );
                                } else {
                                    console.error('Feed creation failed:', response);
                                    var errorMsg = response.data && response.data.message ? response.data.message : 'Failed to create feed. Please try again.';
                                    alert(errorMsg);
                                    $publishBtn.prop('disabled', false).html('Create Feed');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX Error:', {
                                    status: status,
                                    error: error,
                                    responseText: xhr.responseText,
                                    xhr: xhr
                                });
                                var errorMsg = 'An error occurred while creating the feed.';
                                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                                    errorMsg = xhr.responseJSON.data.message;
                                }
                                alert(errorMsg + ' Please check the browser console for more details.');
                                $publishBtn.prop('disabled', false).html('Create Feed');
                            }
                        });
                    });

                    $('.exit').off('click').on('click', function () {
                        window.location.href = 'edit.php?post_type=product-feed';
                    });
                },
                onNext: function () {
                    return !!feedData.feed_id;
                }
            },

            // =====================
            // STEP 5: Complete
            // =====================
            {
                id: 'complete',
                title: 'Complete',
                canSkip: false,
                canGoBack: false,
                mount: function (container, context) {
                    navigateTo('complete');
                    fireSetupCompleted();

                    // Use the actual feed URL returned from the server
                    var feedUrl = feedData.feed_url || '';

                    $('#feedUrl').val(feedUrl);

                    // Copy button - copies to clipboard and opens in new tab
                    $('#copyBtn').off('click').on('click', function () {
                        var $input = $('#feedUrl');
                        var url = $input.val();

                        // Copy to clipboard
                        $input[0].select();
                        $input[0].setSelectionRange(0, 99999); // For mobile devices

                        try {
                            document.execCommand('copy');
                            // Show feedback
                            var $btn = $(this);
                            var originalTitle = $btn.attr('title');
                            $btn.attr('title', 'Copied!');
                            setTimeout(function() {
                                $btn.attr('title', originalTitle);
                            }, 2000);
                        } catch (err) {
                            console.error('Failed to copy:', err);
                        }

                        // Also open in new tab
                        window.open(url, '_blank');
                    });

                    // Download button
                    $('#downloadBtn').off('click').on('click', function () {
                        var url = $('#feedUrl').val();
                        if (url) {
                            var feedId = feedData.feed_id || 'feed';
                            var feedFormat = feedData.format || 'xml';
                            var a = document.createElement('a');
                            a.href = url;
                            a.download = 'feed-' + feedId + '.' + feedFormat;
                            a.click();
                        }
                    });

                    // Edit Feed link
                    $('#editFeedLink').off('click').on('click', function (e) {
                        e.preventDefault();
                        if (feedData.edit_url) {
                            window.location.href = feedData.edit_url;
                        } else {
                            console.log('Edit feed URL not available');
                        }
                    });

                    // Create Another Feed button
                    $('#createAnotherBtn').off('click').on('click', function () {
                        // Navigate to create new feed page
                        window.location.href = 'post-new.php?post_type=product-feed';
                    });

                    // Go to Dashboard button
                    $('#dashboardBtn').off('click').on('click', function () {
                        // Navigate to the feeds list page
                        window.location.href = 'edit.php?post_type=product-feed';
                    });

                    $('.exit').off('click').on('click', function () {
                        window.location.href = 'edit.php?post_type=product-feed';
                    });
                }
            }
        ],
        firstStrike: {
            label: 'Product Feed Created',
            verify: function () {
                return !!feedData.name;
            }
        }
    });

    // Start the onboarding engine
    engine.start();

    // Listen to step changes and update UI
    tracker.on('step_changed', (data) => {
        const currentStep = engine.getCurrentStep();
        if (currentStep && currentStep.mount) {
            const container = document.getElementById('onboarding-app');
            if (container) {
                currentStep.mount(container, engine.getStepContext());
            }
        }
    });

    // Initial render - show welcome step
    const currentStep = engine.getCurrentStep();
    if (currentStep && currentStep.mount) {
        const container = document.getElementById('onboarding-app');
        if (container) {
            currentStep.mount(container, engine.getStepContext());
        }
    }
});
