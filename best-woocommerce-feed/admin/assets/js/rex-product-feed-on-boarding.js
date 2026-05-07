(function ($) {
    "use strict";

    $(document).ready(function () {
        if (typeof Shepherd === 'undefined') {
            return;
        }

        var customStyles = `
            .rex-shepherd-theme {
                border-radius: 8px !important;
                box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
                border: 0 !important;
                max-width: 420px;
                background: #ffffff !important;
            }
            .rex-shepherd-theme .shepherd-header {
                background: #ffffff;
                border-radius: 8px 8px 0 0;
                padding: 24px 24px 0 24px;
                align-items: flex-start;
                border-bottom: none !important;
            }
            .rex-shepherd-theme .shepherd-cancel-icon {
                position: absolute;
                top: 15px;
                right: 15px;
            }
            .rex-shepherd-theme .shepherd-content {
                padding: 0;
                text-align: left;
            }
            .rex-shepherd-theme .shepherd-title {
                color: #206DEF !important;
                font-weight: 700 !important;
                font-size: 18px !important;
                text-align: left;
                width: 100%;
                margin: 0;
                display: block !important;
                line-height: 1.4 !important;
            }
            .rex-shepherd-theme .shepherd-text {
                padding: 12px 24px 24px 24px;
                font-size: 14px;
                line-height: 1.6;
                color: #333;
                text-align: left;
            }
            .rex-shepherd-theme .shepherd-footer {
                padding: 0 24px 24px 24px;
                background: #ffffff;
                border-radius: 0 0 8px 8px;
                display: flex;
                justify-content: flex-start;
                gap: 12px;
            }
            .rex-shepherd-theme .shepherd-button-primary {
                background: #206DEF !important;
                color: #fff !important;
                border-radius: 8px !important;
                border: none !important;
                padding: 6px 16px !important;
                font-weight: 500;
                font-size: 13px !important;
                transition: opacity 0.3s;
                margin: 0 !important;
                line-height: 2 !important;
            }
            .rex-shepherd-theme .shepherd-button-primary:hover {
                opacity: 0.9;
            }
            .rex-shepherd-theme .shepherd-button-primary:disabled {
                background: #90abc0 !important;
                cursor: not-allowed !important;
            }
            .rex-shepherd-theme .shepherd-button-secondary {
                background: #fff !important;
                color: #206DEF !important;
                border: 1px solid #206DEF !important;
                border-radius: 8px !important;
                padding: 5px 15px !important;
                font-weight: 500;
                font-size: 13px !important;
                margin: 0 !important;
                line-height: 2 !important;
            }
            body.pfm-tour-active > .select2-container,
            body.pfm-tour-active .select2-container.select2-container--open {
                z-index: 9999999 !important;
                pointer-events: auto !important;
            }
            body.pfm-tour-active #rex_feed_product_settings,
            body.pfm-tour-active #rex-feed-settings-save-changes, 
            body.pfm-tour-active .rex-contnet-filter__cross-icon,
            body.pfm-tour-active #rex_feed_product_filters,
            body.pfm-tour-active #rex_feed_filter_modal_close_btn {
                z-index: 9999999 !important;
                pointer-events: auto !important;
            }
            .rex-shepherd-subhead {
                font-size: 11px; 
                font-weight: 600; 
                text-transform: uppercase; 
                color: #999; 
                margin-bottom: 6px; 
                letter-spacing: 0.5px; 
                line-height: 1;
                display: block;
            }
        `;
        $('<style>').text(customStyles).appendTo('head');


        var nextBtnTitle = (typeof rexOnboardingJs !== 'undefined' && rexOnboardingJs.next_button) ? rexOnboardingJs.next_button.title : 'Next';
        var prevBtnTitle = (typeof rexOnboardingJs !== 'undefined' && rexOnboardingJs.prev_button) ? rexOnboardingJs.prev_button.title : 'Previous';
        var doneBtnTitle = (typeof rexOnboardingJs !== 'undefined' && rexOnboardingJs.done_button) ? rexOnboardingJs.done_button.title : 'Done';
        var step1BgImage = (typeof rexOnboardingJs !== 'undefined' && rexOnboardingJs.step1_bg_image) ? rexOnboardingJs.step1_bg_image : '';

        const tour = new Shepherd.Tour({
            useModalOverlay: true,
            defaultStepOptions: {
                classes: 'rex-shepherd-theme',
                scrollTo: {
                    behavior: 'smooth',
                    block: 'center'
                },
                cancelIcon: {
                    enabled: true
                }
            }
        });

        // Step 1: Welcome
        tour.addStep({
            id: 'welcome',
            title: '<span class="rex-shepherd-subhead">STEP 1 OF 7</span>Create Your First Feed',
            text: '<p style="margin:0;">We\'ll walk you through the essential settings. This usually takes less than 2 minutes.</p>',
            buttons: [
                {
                    text: nextBtnTitle,
                    action: tour.next,
                    classes: 'shepherd-button-primary'
                }
            ]
        });

        // Step 2: Feed Title
        tour.addStep({
            id: 'feed-title',
            title: '<span class="rex-shepherd-subhead">STEP 2 OF 7</span>Name Your Feed',
            text: '<p style="margin:0;">First, give your feed a clear and recognizable name.</p>',
            attachTo: {
                element: '#title',
                on: 'bottom'
            },
            buttons: [
                {
                    text: prevBtnTitle,
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: nextBtnTitle,
                    action: tour.next,
                    classes: 'shepherd-button-primary'
                }
            ],
            when: {
                show: function () {
                    let nextBtn = this.getElement().querySelector('.shepherd-button-primary');
                    let targetInput = $('#title');

                    function checkTitle() {
                        if (targetInput.val() && targetInput.val().trim() !== '') {
                            nextBtn.disabled = false;
                        } else {
                            nextBtn.disabled = true;
                        }
                    }

                    checkTitle();
                    targetInput.on('keyup.shepherd paste.shepherd', checkTitle);
                },
                hide: function () {
                    $('#title').off('keyup.shepherd paste.shepherd');
                }
            }
        });

        // Step 3: Merchant Config
        tour.addStep({
            id: 'feed-merchant',
            title: '<span class="rex-shepherd-subhead">STEP 3 OF 7</span>Select Merchant & Format',
            text: '<p style="margin:0;">Choose the merchant (e.g. Google, Facebook) you want to generate the feed for, and set the appropriate feed file format.</p>',
            attachTo: {
                element: '#rex_feed_conf', // The metabox wrapper
                on: 'bottom'
            },
            buttons: [
                {
                    text: prevBtnTitle,
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: nextBtnTitle,
                    action: tour.next,
                    classes: 'shepherd-button-primary'
                }
            ],
            when: {
                show: function () {
                    let nextBtn = this.getElement().querySelector('.shepherd-button-primary');
                    let targetSelect = $('#rex_feed_merchant');

                    function checkMerchant() {
                        const val = targetSelect.val();
                        if (val && val !== '' && val !== '-1') {
                            nextBtn.disabled = false;
                        } else {
                            nextBtn.disabled = true;
                        }
                    }

                    checkMerchant();
                    targetSelect.on('change.shepherd', checkMerchant);
                },
                hide: function () {
                    $('#rex_feed_merchant').off('change.shepherd');
                }
            }
        });

        // Step 4: Attribute Mapping
        tour.addStep({
            id: 'feed-attributes',
            title: '<span class="rex-shepherd-subhead">STEP 4 OF 7</span>Configure Attributes',
            text: '<p style="margin:0;">Here, the plugin automatically maps standard attributes for you. You can change them or map custom attributes if needed.</p>',
            attachTo: {
                element: '#config-table',
                on: 'top'
            },
            buttons: [
                {
                    text: prevBtnTitle,
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: nextBtnTitle,
                    action: tour.next,
                    classes: 'shepherd-button-primary'
                }
            ]
        });

        // Step 5: Product Filters
        tour.addStep({
            id: 'feed-product-filters',
            title: '<span class="rex-shepherd-subhead">STEP 5 OF 7</span>Product Filters',
            text: '<p style="margin:0;">Click here to add product filters. From there you can filter what products you need to create or exclude.</p>',
            attachTo: {
                element: '#rex-pr-filter-btn',
                on: 'bottom'
            },
            buttons: [
                {
                    text: prevBtnTitle,
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: nextBtnTitle,
                    action: tour.next,
                    classes: 'shepherd-button-primary'
                }
            ],
            when: {
                hide: function () {
                    if ($('#rex_feed_product_filters').is(':visible')) {
                        $('#rex_feed_filter_modal_close_btn').trigger('click');
                    }
                }
            }
        });

        // Step 6: Product Settings & Filters
        tour.addStep({
            id: 'feed-settings',
            title: '<span class="rex-shepherd-subhead">STEP 6 OF 7</span>Set Update Schedule',
            text: '<p style="margin:0;">From here you can change settings of the feed like schedule, exclude out-of-stock products, exclude hidden products, and other settings.</p>',
            attachTo: {
                element: '#rex-feed-settings-btn',
                on: 'bottom'
            },
            buttons: [
                {
                    text: prevBtnTitle,
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: nextBtnTitle,
                    action: tour.next,
                    classes: 'shepherd-button-primary'
                }
            ],
            when: {
                hide: function () {
                    if ($('#rex_feed_product_settings').is(':visible')) {
                        $('#rex_feed_settings_modal_close_btn').trigger('click');
                    }
                }
            }
        });

        // Step 7: Publish
        tour.addStep({
            id: 'feed-publish',
            title: '<span class="rex-shepherd-subhead">STEP 7 OF 7</span>Publish & Generate',
            text: '<p style="margin:0;">Once everything is configured, click here to publish and generate your product feed!</p>',
            attachTo: {
                element: '#publish',
                on: 'left'
            },
            buttons: [
                {
                    text: prevBtnTitle,
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: doneBtnTitle,
                    action: tour.complete,
                    classes: 'shepherd-button-primary'
                }
            ]
        });

        // Save status to db so tour is not shown again
        function markTourAsDone() {
            $('body').removeClass('pfm-tour-active');
            if (typeof rex_wpfm_ajax !== 'undefined' && rex_wpfm_ajax.ajax_url) {
                $.post(rex_wpfm_ajax.ajax_url, {
                    action: 'pfm_tour_update_status'
                });
            }
        }
        function teardownTour() {
            $('body').removeClass('pfm-tour-active');
        }

        tour.on('cancel', markTourAsDone);
        tour.on('complete', markTourAsDone);
        tour.on('hide', teardownTour);
        tour.on('start', function() {
            $('body').addClass('pfm-tour-active');
        });

        tour.start();

        // Enhance UX by stripping the URL param so a page refresh doesn't replay the tour instantly
        if (window.history && window.history.replaceState) {
            var url = new URL(window.location.href);
            if (url.searchParams.has('tour_guide')) {
                url.searchParams.delete('tour_guide');
                window.history.replaceState({ path: url.href }, '', url.href);
            }
        }
    });

})(jQuery);
