/**
 * Rex Product Feed - Validation JavaScript
 *
 * Handles the feed validation UI interactions.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/assets/js
 * @since      7.4.58
 */

(function($) {
    'use strict';

    /**
     * Feed Validation Handler
     */
    var RexFeedValidation = {

        /**
         * Initialize the validation module.
         * @since 7.4.58
         */
        init: function() {
            this.wrapper = $('.rex-feed-validation-wrapper');
            
            if (!this.wrapper.length) {
                return;
            }

            this.feedId = this.wrapper.data('feed-id');
            this.validationDisabled = this.wrapper.attr('data-validation-disabled') === '1';
            this.isPremium = this.wrapper.attr('data-is-premium') === '1' || (
                typeof rex_wpfm_ajax !== 'undefined' && (rex_wpfm_ajax.is_premium === true || rex_wpfm_ajax.is_premium === '1' || rex_wpfm_ajax.is_premium === 1)
            );
            this.currentPage = 1;
            this.perPage = parseInt($('#rex-validation-per-page').val(), 10) || 5;
            this.filters = {
                severity: $('#rex-validation-severity-filter').val() || 'error',
                attribute: '',
                search: ''
            };
            this.isLoading = false;
            this.fullAttributeSummary = []; // Store full attribute summary
            this.quickFixRules = {};

            this.mountFixModal();
            this.mountProductsModal();
            this.positionValidationLink();
            this.bindEvents();
            this.updateFixFeedButtonState();
            this.toggleFixIssuesColumn(this.filters.severity);
            this.loadInitialResults();
            this.checkAutoTriggerValidation();
        },

        /**
         * Keep validation link directly before feed attribute configuration.
         * @since 7.4.58
         */
        positionValidationLink: function() {
            var link = $('#rex_feed_validation_link');
            var config = $('#rex_feed_config_heading');

            if (link.length && config.length) {
                link.insertBefore(config);
            }
        },

        /**
         * Keep the fixed modal relative to the browser viewport.
         * @since 7.4.58
         */
        mountFixModal: function() {
            var modal = $('#rex-validation-fix-modal');

            if (modal.length && !modal.parent().is('body')) {
                modal.appendTo(document.body);
            }
        },

        /**
         * Keep the products modal relative to the browser viewport.
         * @since 7.4.58
         */
        mountProductsModal: function() {
            var modal = $('#rex-validation-products-modal');

            if (modal.length && !modal.parent().is('body')) {
                modal.appendTo(document.body);
            }
        },

        /**
         * Remember page position without changing document layout.
         * @since 7.4.58
         */
        lockFixModalScroll: function() {
            this.fixModalScrollPosition = window.pageYOffset || document.documentElement.scrollTop || 0;
        },

        /**
         * Restore page position after modal closes.
         * @since 7.4.58
         */
        unlockFixModalScroll: function() {
            var scrollPosition = parseInt(this.fixModalScrollPosition, 10) || 0;

            window.scrollTo(0, scrollPosition);
        },

        /**
         * Check if we need to auto-trigger validation after feed generation.
         * @since 7.4.58
         */
        checkAutoTriggerValidation: function() {
            var self = this;
            
            if (typeof(sessionStorage) === "undefined") {
                return;
            }
            
            var flagKey = 'rex_feed_just_generated_' + this.feedId;
            var shouldAutoValidate = sessionStorage.getItem(flagKey);

            if (this.validationDisabled) {
                sessionStorage.removeItem(flagKey);
                return;
            }
            
            if (shouldAutoValidate === 'true') {
                // Clear the flag immediately to prevent re-triggering on subsequent refreshes
                sessionStorage.removeItem(flagKey);
                
                console.log('Auto-triggering feed validation after feed generation...');
                
                // Small delay to ensure page is fully loaded
                setTimeout(function() {
                    self.validateFeed(true); // Pass true to indicate auto-validation
                }, 500);
            }
        },

        /**
         * Bind event handlers.
         * @since 7.4.58
         */
        bindEvents: function() {
            var self = this;

            // Validate Feed button: free users refresh/validate feed, pro users apply added rules & regenerate feed.
            $(document).on('click', '.rex-feed-validate-btn, .rex-feed-fix-btn', function(e) {
                var btn = $(this);

                e.preventDefault();

                if (btn.prop('disabled')) {
                    return;
                }

                if (!self.isPremium) {
                    self.validateFeed();
                    return;
                }

                if (btn.attr('data-pro-required') === 'true') {
                    $('#rex_premium_feature_popup').show();
                    return;
                }

                self.fixFeed(btn);
            });

            // Scroll from feed configuration to validation summary.
            $(document).on('click', '.rex-feed-validation-link-button', function(e) {
                var target = $('#rex_feed_validation');

                e.preventDefault();

                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 42
                    }, 400);
                }
            });

            // Export button
            $(document).on('click', '.rex-feed-export-validation-btn', function(e) {
                e.preventDefault();
                self.closeOptionsMenu();
                self.showExportModal();
            });

            // Find a Quick Fix first; configured rows open the rule modal.
            $(document).on('click', '.rex-validation-fix-link', function(e) {
                var trigger = $(this);

                e.preventDefault();

                if (trigger.hasClass('is-configured') || !self.isPremium) {
                    self.openFixModal(trigger);
                    return;
                }

                self.requestQuickFix(trigger);
            });

            $(document).on('click', '.rex-validation-applied-rule__delete', function(e) {
                e.preventDefault();
                self.deleteQuickFixRule($(this));
            });

            $(document).on('click', '.rex-validation-view-products-link', function(e) {
                e.preventDefault();
            });

            $(document).on('click', '.rex-validation-fix-modal__close, .rex-validation-fix-modal__cancel', function(e) {
                e.preventDefault();
                self.closeFixModal();
            });

            $(document).on('click', '#rex-validation-fix-modal', function(e) {
                if (e.target === this) {
                    self.closeFixModal();
                }
            });

            $(document).on('change', '#rex-validation-fix-static', function() {
                self.toggleFixRuleStatic($(this).prop('checked'));
            });

            $(document).on('change', '#rex-validation-mapping-type', function() {
                self.toggleMappingStatic($(this).val() === 'static');
            });

            // Clear modal alert on input change.
            $(document).on('change input', '#rex-validation-fix-condition, #rex-validation-fix-find, #rex-validation-fix-replace, #rex-validation-fix-static, #rex-validation-fix-static-value, #rex-validation-mapping-type, #rex-validation-mapping-value, #rex-validation-mapping-static-value', function() {
                self.hideFixModalAlert();
            });

            $(document).on('click', '.rex-validation-fix-modal__submit', function(e) {
                var submitButton = $(this);

                e.preventDefault();

                if (submitButton.prop('disabled') || !self.isPremium) {
                    return;
                }

                self.applyFixRule(submitButton);
            });

            // Close premium feature popup on backdrop click
            $(document).on('click', '#rex_premium_feature_popup', function(e) {
                if (e.target === this) {
                    $(this).hide();
                }
            });

            // Validation options menu.
            $(document).on('click', '.rex-feed-validation-menu-toggle', function(e) {
                var toggle = $(this);
                var menu = $('#rex-feed-validation-menu');
                var isOpen = toggle.attr('aria-expanded') === 'true';

                e.preventDefault();
                e.stopPropagation();
                toggle.attr('aria-expanded', String(!isOpen));
                menu.prop('hidden', isOpen);
            });

            $(document).on('click', '.rex-feed-validation-menu', function(e) {
                e.stopPropagation();
            });

            $(document).on('change', '.rex-feed-validation-setting', function() {
                self.saveValidationSettings($(this));
            });

            $(document).on('click', function() {
                self.closeOptionsMenu();
            });

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    if ($('#rex_premium_feature_popup').is(':visible')) {
                        $('#rex_premium_feature_popup').hide();
                    } else if (!$('#rex-validation-products-modal').prop('hidden')) {
                        self.closeProductsModal();
                    } else if (!$('#rex-validation-fix-modal').prop('hidden')) {
                        self.closeFixModal();
                    } else {
                        self.closeOptionsMenu();
                    }
                }
            });

            // Filter changes
            $(document).on('change', '.rex-validation-filter', function() {
                self.applyFilters();
            });

            // Search with debounce
            var searchTimeout;
            $(document).on('input', '#rex-validation-search', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    self.applyFilters();
                }, 500);
            });

            // Pagination
            $(document).on('click', '.rex-validation-prev-page', function() {
                if (self.currentPage > 1) {
                    self.currentPage--;
                    self.loadResults();
                }
            });

            $(document).on('click', '.rex-validation-next-page', function() {
                self.currentPage++;
                self.loadResults();
            });

            $(document).on('click', '.rex-validation-first-page', function() {
                self.currentPage = 1;
                self.loadResults();
            });

            $(document).on('click', '.rex-validation-last-page', function() {
                var totalPages = parseInt($(this).data('total-pages'), 10) || 1;
                self.currentPage = totalPages;
                self.loadResults();
            });

            // Per page change
            $(document).on('change', '#rex-validation-per-page', function() {
                self.perPage = parseInt($(this).val(), 10);
                self.currentPage = 1;
                self.loadResults();
            });

            // Severity tabs mirror the hidden severity filter used by AJAX.
            $(document).on('click', '.rex-validation-severity-tab', function() {
                var severity = $(this).data('severity');

                $('#rex-validation-severity-filter').val(severity);
                self.updateSeverityControls(severity);
                self.applyFilters();
            });

            // Summary card click to filter
            $(document).on('click', '.rex-feed-validation-card--clickable', function() {
                var severity = $(this).data('filter-severity');
                $('#rex-validation-severity-filter').val(severity);
                self.updateSeverityControls(severity);
                self.applyFilters();
            });

            // Export modal
            $(document).on('click', '.rex-modal__close, .rex-modal__cancel', function() {
                self.hideExportModal();
            });

            $(document).on('click', '.rex-modal__export', function() {
                self.exportResults();
            });

            // Products modal
            $(document).on('click', '.rex-validation-view-products-link', function(e) {
                e.preventDefault();
                self.openProductsModal($(this));
            });

            $(document).on('click', '.rex-validation-products-page-btn, .rex-validation-products-page-number', function(e) {
                e.preventDefault();
                var btn = $(this);
                if (btn.is(':disabled') || btn.hasClass('is-active')) {
                    return;
                }
                var targetPage = parseInt(btn.attr('data-page'), 10);
                if (targetPage && targetPage > 0) {
                    self.loadProductsModalPage(targetPage);
                }
            });

            $(document).on('click', '.rex-validation-products-modal__close, .rex-validation-products-modal__cancel', function(e) {
                e.preventDefault();
                self.closeProductsModal();
            });

            $(document).on('click', '#rex-validation-products-modal', function(e) {
                if (e.target === this) {
                    self.closeProductsModal();
                }
            });
        },

        /**
         * Close validation options menu.
         * @since 7.4.58
         */
        closeOptionsMenu: function() {
            $('.rex-feed-validation-menu-toggle').attr('aria-expanded', 'false');
            $('#rex-feed-validation-menu').prop('hidden', true);
        },

        /**
         * Open fix-rule modal with issue data from clicked attribute row.
         * @since 7.4.58
         * @param {jQuery} trigger Clicked Configure button.
         */
        openFixModal: function(trigger) {
            this.hideFixModalAlert();
            var modal = $('#rex-validation-fix-modal');
            var rawAttribute = String(trigger.attr('data-attribute') || 'attribute');
            var attributeLabel = this.formatAttributeLabel(rawAttribute);
            var targetAttribute = String(trigger.attr('data-fix-target') || rawAttribute);
            var targetLabel = String(trigger.attr('data-fix-target-label') || this.formatAttributeLabel(targetAttribute));
            var productCount = parseInt(trigger.attr('data-product-count'), 10) || 0;
            var severity = String(trigger.attr('data-severity') || 'error');
            var ruleType = String(trigger.attr('data-rule') || '');
            var isMappingAnomaly = trigger.attr('data-is-mapping') === 'true' || /wrongly_assigned|wrong_assigned|anomaly|invalid_mapping/i.test(ruleType);
            var issueMessage = String(trigger.attr('data-message') || '').replace(/[.\s]+$/, '');
            var suggestedCondition = String(trigger.attr('data-fix-condition') || (isMappingAnomaly ? 'equal_to' : ''));
            var suggestedFind = String(trigger.attr('data-fix-find') || '');
            var suggestedReplace = String(trigger.attr('data-fix-replace') || '');
            var suggestedStaticValue = String(trigger.attr('data-fix-static-value') || '');
            var suggestedIsStatic = trigger.attr('data-fix-static') === 'true';
            var manualMessage = String(trigger.attr('data-manual-message') || '');
            var productNoun = productCount === 1 ? 'product' : 'products';
            var verb = productCount === 1 ? 'is' : 'are';
            var haveVerb = productCount === 1 ? 'has' : 'have';
            var lowerAttribute = attributeLabel.toLowerCase();
            var productAttributeLabel = /^product\s/i.test(attributeLabel) ? attributeLabel : 'Product ' + attributeLabel;
            var isMissing = /missing|required|should have|must have|empty/i.test(issueMessage);
            var currentTargetLabel = targetLabel || targetAttribute || attributeLabel;
            var suggestedLabel = suggestedReplace ? this.formatAttributeLabel(suggestedReplace) : attributeLabel;
            var subtitle;
            var modalMessage;

            if (isMappingAnomaly) {
                subtitle = 'Change default mapping in this feed';
                modalMessage = 'Attribute "' + attributeLabel + '" is currently assigned to "' + currentTargetLabel + '".';
            } else if (isMissing) {
                subtitle = 'Update the missing product ' + lowerAttribute;
                modalMessage = productCount.toLocaleString() + ' ' + productNoun + ' ' + verb + ' missing ' + lowerAttribute + '.';
            } else if (severity === 'warning') {
                subtitle = 'Review the product ' + lowerAttribute + ' warning';
                modalMessage = productCount.toLocaleString() + ' ' + productNoun + ' ' + haveVerb + ' a warning for ' + lowerAttribute + '.';
            } else if (severity === 'info') {
                subtitle = 'Improve the product ' + lowerAttribute;
                modalMessage = productCount.toLocaleString() + ' ' + productNoun + ' ' + haveVerb + ' a suggestion for ' + lowerAttribute + '.';
            } else {
                subtitle = 'Update the affected product ' + lowerAttribute;
                modalMessage = productCount.toLocaleString() + ' ' + productNoun + ' ' + haveVerb + ' an issue with ' + lowerAttribute + '.';
            }

            var lowerAttrPlural = lowerAttribute ? (lowerAttribute.endsWith('s') ? lowerAttribute : lowerAttribute + 's') : 'descriptions';
            var tooltipMessage = isMappingAnomaly
                ? 'Change the default mapped attribute or static value for this feed field.'
                : 'Map a product field or use a static value to fill in the missing ' + lowerAttrPlural + '.';

            if (isMappingAnomaly) {
                $('#rex-validation-fix-modal-title').text('Update Feed Mapping');
                modal.find('.rex-validation-fix-modal__subtitle').text(subtitle);
                modal.find('.rex-validation-fix-modal__message-text').text(modalMessage);
                var fullModalMessage = modalMessage + ' ' + (modal.find('.rex-validation-fix-modal__message-static').text() || '').trim();
                modal.find('.rex-validation-fix-modal__message').attr('title', fullModalMessage.trim());
                modal.find('.rex-validation-fix-modal__help p').text(tooltipMessage);
                modal.find('.rex-validation-fix-modal__help').removeAttr('title');

                modal.find('.rex-validation-fix-rule').hide();
                modal.find('.rex-validation-fix-mapping').show();

                $('#rex-validation-mapping-attribute').val(attributeLabel);
                $('#rex-validation-mapping-type').val(suggestedIsStatic ? 'static' : 'meta');
                $('#rex-validation-mapping-static-value').val(suggestedStaticValue);
                $('#rex-validation-mapping-value').val(suggestedReplace);
                if ($.fn.select2 && $('#rex-validation-mapping-value').hasClass('select2-hidden-accessible')) {
                    $('#rex-validation-mapping-value').trigger('change.select2');
                }
                
                modal.find('.rex-validation-fix-modal__submit-label').text('Update Mapping');
            } else {
                $('#rex-validation-fix-modal-title').text(productAttributeLabel);
                modal.find('.rex-validation-fix-modal__subtitle').text(subtitle);
                modal.find('.rex-validation-fix-modal__message-text').text(modalMessage);
                var fullModalMessage = modalMessage + ' ' + (modal.find('.rex-validation-fix-modal__message-static').text() || '').trim();
                modal.find('.rex-validation-fix-modal__message').attr('title', fullModalMessage.trim());
                modal.find('.rex-validation-fix-modal__help p').text(tooltipMessage);
                modal.find('.rex-validation-fix-modal__help').removeAttr('title');

                modal.find('.rex-validation-fix-mapping').hide();
                modal.find('.rex-validation-fix-rule').show();

                $('#rex-validation-fix-if, #rex-validation-fix-then')
                    .empty()
                    .append($('<option></option>').val(targetAttribute).text(targetLabel));
                $('#rex-validation-fix-condition').val(suggestedCondition);
                $('#rex-validation-fix-find').val(suggestedFind);
                $('#rex-validation-fix-static-value').val(suggestedStaticValue);
                $('#rex-validation-fix-replace').val(suggestedReplace);
                $('#rex-validation-fix-static').prop('checked', suggestedIsStatic);
                modal.find('.rex-validation-fix-modal__submit-label').text(trigger.hasClass('is-configured') ? 'Update Rule' : 'Add Rule');
            }

            var hasAutoSuggestion = !manualMessage && !!(suggestedReplace || suggestedStaticValue);
            var noteEl = modal.find('.rex-validation-fix-modal__note');
            var noteContent = modal.find('.rex-validation-fix-modal__note-content');

            var upgradeBanner = modal.find('.rex-validation-fix-modal__upgrade-banner');

            if (!this.isPremium) {
                noteEl.hide();
                upgradeBanner.show();
            } else {
                upgradeBanner.hide();
                noteEl.show();

                if (hasAutoSuggestion) {
                    noteEl.removeClass('rex-validation-fix-modal__note--warning');
                    noteContent.text('A potential fix has been auto-suggested and prefilled below. You can review or adjust it before submitting.');
                } else {
                    noteEl.addClass('rex-validation-fix-modal__note--warning');
                    var failText = manualMessage
                        ? manualMessage
                        : 'The system was unable to auto-suggest a fix for this issue. Please configure the fields below manually.';
                    noteContent.text(failText);
                }
            }

            window.clearTimeout(this.fixModalCloseTimer);
            this.fixModalTrigger = trigger;
            modal
                .removeClass('is-open is-closing')
                .prop('hidden', false)
                .attr('aria-hidden', 'false');
            this.lockFixModalScroll();
            this.initializeFixRuleSelects();
            if (isMappingAnomaly) {
                this.toggleMappingStatic(suggestedIsStatic);
            } else {
                this.toggleFixRuleStatic(suggestedIsStatic);
            }

            if (!this.isPremium) {
                var ruleFields = modal.find('.rex-validation-fix-rule__fields');
                ruleFields.addClass('rex-validation-fix-rule__fields--disabled');
                ruleFields.find(':input').prop('disabled', true);
                ruleFields.find('select').each(function() {
                    if ($.fn.select2 && $(this).hasClass('select2-hidden-accessible')) {
                        $(this).prop('disabled', true).trigger('change.select2');
                    }
                });
                modal.find('.rex-validation-fix-modal__submit').prop('disabled', true);
            } else {
                var ruleFields = modal.find('.rex-validation-fix-rule__fields');
                ruleFields.removeClass('rex-validation-fix-rule__fields--disabled');
                ruleFields.find(':input').not('#rex-validation-fix-if, #rex-validation-fix-then').prop('disabled', false);
                ruleFields.find('select').each(function() {
                    if ($.fn.select2 && $(this).hasClass('select2-hidden-accessible')) {
                        $(this).prop('disabled', false).trigger('change.select2');
                    }
                });
                modal.find('.rex-validation-fix-modal__submit').prop('disabled', false);
            }

            // Force the initial animation state before revealing the modal.
            modal.get(0).offsetWidth;
            modal.addClass('is-open');

            var closeButton = modal.find('.rex-validation-fix-modal__close').get(0);
            if (closeButton) {
                try {
                    closeButton.focus({ preventScroll: true });
                } catch (e) {
                    closeButton.focus();
                }
            }

            window.scrollTo(0, this.fixModalScrollPosition);
        },

        /**
         * Initialize Feed Rule Select2 behavior inside fix modal.
         * @since 7.4.58
         */
        initializeFixRuleSelects: function() {
            var modal = $('#rex-validation-fix-modal');
            var self = this;

            if (!$.fn.select2) {
                return;
            }

            modal.find('.rex-validation-fix-rule__select2').each(function() {
                var select = $(this);

                if (!select.hasClass('select2-hidden-accessible')) {
                    select.select2({
                        width: '100%',
                        dropdownParent: modal
                    });

                    select.on('select2:open.rexValidationFix', function() {
                        window.setTimeout(function() {
                            self.positionFixRuleDropdown(select);
                        }, 0);
                    });
                }

                select.trigger('change.select2');
            });
        },

        /**
         * Keep modal Select2 menu below its field.
         * @since 7.4.58
         * @param {jQuery} select Open Select2 field.
         */
        positionFixRuleDropdown: function(select) {
            var modal = $('#rex-validation-fix-modal');
            var selection = select.next('.select2-container');
            var dropdownContainer = modal.children('.select2-container--open').has('.select2-dropdown').last();

            if (!selection.length || !dropdownContainer.length) {
                return;
            }

            var modalRect = modal[0].getBoundingClientRect();
            var selectionRect = selection[0].getBoundingClientRect();

            selection.removeClass('select2-container--above').addClass('select2-container--below');
            dropdownContainer
                .css({
                    top: (selectionRect.bottom - modalRect.top) + 'px',
                    left: (selectionRect.left - modalRect.left) + 'px',
                    width: selectionRect.width + 'px'
                })
                .find('.select2-dropdown')
                .removeClass('select2-dropdown--above')
                .addClass('select2-dropdown--below');
        },

        /**
         * Find and stage next confident rule for one grouped issue.
         * @since 7.4.58
         * @param {jQuery} trigger Row Quick Fix button.
         */
        requestQuickFix: function(trigger) {
            var self = this;
            var finishRequest = function() {
                trigger.prop('disabled', false).removeClass('is-loading');
            };

            if (trigger.prop('disabled')) {
                return;
            }

            trigger.prop('disabled', true).addClass('is-loading');

            $.ajax({
                url: rex_wpfm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'rex_feed_get_quick_fix',
                    feed_id: this.feedId,
                    attribute: String(trigger.attr('data-attribute') || ''),
                    rule: String(trigger.attr('data-rule') || ''),
                    severity: String(trigger.attr('data-severity') || ''),
                    security: rex_wpfm_ajax.ajax_nonce
                },
                success: function(response) {
                    if (!response.success) {
                        self.showNotice('error', response.data.message || self.getTranslation('error_occurred'));
                        finishRequest();
                        return;
                    }

                    if (!response.data.found) {
                        trigger.attr({
                            'data-is-mapping': response.data.is_mapping_anomaly ? 'true' : 'false',
                            'data-fix-target': response.data.target_attribute || '',
                            'data-fix-target-label': response.data.target_label || '',
                            'data-fix-condition': response.data.condition || '',
                            'data-fix-find': response.data.find || '',
                            'data-fix-replace': response.data.suggested_replace || response.data.candidate || '',
                            'data-fix-static': 'false',
                            'data-fix-static-value': '',
                            'data-manual-message': response.data.manual_message || ''
                        });
                        finishRequest();
                        self.openFixModal(trigger);
                        return;
                    }

                    self.refreshFeedRulesPanel()
                        .done(function() {
                            self.setConfiguredQuickFix(trigger, response.data);
                            finishRequest();
                        })
                        .fail(function() {
                            self.showNotice('error', 'Quick Fix was saved, but Feed Rules panel could not refresh. Reload the page before fixing the feed.');
                            finishRequest();
                        });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var errorMessage = jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message
                        ? jqXHR.responseJSON.data.message
                        : (errorThrown || self.getTranslation('error_occurred'));
                    self.showNotice('error', errorMessage);
                    finishRequest();
                }
            });
        },

        /**
         * Get inner HTML content for Quick Fix button depending on pro status and configured state.
         * @since 7.4.58
         * @param {boolean} isConfigured Whether rule is configured.
         * @return {string} Button inner HTML.
         */
        getQuickFixButtonContent: function(isConfigured) {
            if (isConfigured) {
                return '<span class="rex-validation-fix-icon" aria-hidden="true"></span>' + this.getTranslation('configure');
            }
            if (!this.isPremium) {
                return '<span class="rex-validation-fix-icon" aria-hidden="true"></span>' +
                    this.getTranslation('fix_all') +
                    '<span class="rex-validation-pro-badge" aria-hidden="true">Pro</span>';
            }
            return '<span class="rex-validation-fix-icon" aria-hidden="true"></span>' + this.getTranslation('quick_fix');
        },

        /** Store configured row state and render its applied rule. */
        setConfiguredQuickFix: function(trigger, rule) {
            var issueKey = this.getQuickFixIssueKey(trigger);
            var attribute = String(trigger.attr('data-attribute') || '');

            this.resetQuickFixAttribute(attribute, trigger);

            var attrData = {
                'data-is-mapping': rule.is_mapping ? 'true' : 'false',
                'data-fix-condition': rule.condition || '',
                'data-fix-target': rule.target_attribute || rule.attribute || '',
                'data-fix-target-label': rule.target_label || rule.attribute_label || '',
                'data-fix-find': rule.find || '',
                'data-fix-replace': rule.candidate || rule.replace || '',
                'data-fix-static': rule.is_static ? 'true' : 'false',
                'data-fix-static-value': rule.static_value || ''
            };

            if (rule.is_mapping && typeof rule.original_type !== 'undefined') {
                attrData['data-original-type'] = rule.original_type;
                attrData['data-original-value'] = typeof rule.original_value !== 'undefined' ? rule.original_value : '';
            }

            trigger
                .addClass('is-configured')
                .removeAttr('data-manual-message')
                .attr(attrData)
                .html(this.getQuickFixButtonContent(true));

            this.quickFixRules[issueKey] = rule;
            this.renderAppliedRule(trigger.closest('tr'), rule);
            this.updateFixFeedButtonState();
        },

        /** Reset configured UI for an attribute because only one rule is allowed. */
        resetQuickFixAttribute: function(attribute, exceptTrigger) {
            var self = this;
            var exceptKey = exceptTrigger ? this.getQuickFixIssueKey(exceptTrigger) : '';

            Object.keys(this.quickFixRules).forEach(function(issueKey) {
                var savedRule = self.quickFixRules[issueKey];

                if (String(savedRule.attribute || '') === attribute && issueKey !== exceptKey) {
                    delete self.quickFixRules[issueKey];
                }
            });

            $('.rex-validation-fix-link').each(function() {
                var rowTrigger = $(this);

                if (
                    String(rowTrigger.attr('data-attribute') || '') !== attribute
                    || (exceptTrigger && rowTrigger.get(0) === exceptTrigger.get(0))
                ) {
                    return;
                }

                rowTrigger
                    .removeClass('is-configured')
                    .removeAttr('data-fix-target data-fix-target-label data-fix-condition data-fix-find data-fix-replace data-fix-static data-fix-static-value data-manual-message data-original-type data-original-value')
                    .html(self.getQuickFixButtonContent(false));
                rowTrigger.closest('tr').removeClass('has-applied-rule').next('.rex-validation-applied-rule-row').remove();
            });

            this.updateFixFeedButtonState();
        },

        /** Render applied rule directly below its issue row. */
        renderAppliedRule: function(issueRow, rule) {
            var description;
            var quote = function(value) {
                if (typeof value === 'undefined' || value === null) {
                    return '""';
                }
                var clean = String(value).replace(/^["“”]+|["“”]+$/g, '').trim();
                return '"' + clean + '"';
            };

            if (rule.is_mapping) {
                var replacement = rule.is_static
                    ? quote(rule.static_value || '')
                    : quote(rule.candidate_label || rule.replace_label || this.formatAttributeLabel(rule.candidate || rule.replace || ''));
                var source = quote(rule.attribute_label || this.formatAttributeLabel(rule.attribute || ''));
                description = 'Update mapping for ' + source + ' to ' + replacement + '.';
            } else {
                var findValue = rule.find ? quote(rule.find) : '(empty)';
                var replacement = rule.is_static
                    ? quote(rule.static_value || '')
                    : quote(rule.candidate_label || rule.replace_label || this.formatAttributeLabel(rule.candidate || rule.replace || ''));
                var source = quote(rule.target_label || rule.attribute_label || this.formatAttributeLabel(rule.target_attribute || rule.attribute || ''));
                var condition = rule.condition_label || this.getConditionLabel(rule.condition || '');
                description = 'If ' + source + ' ' + condition.toLowerCase();

                if ((rule.condition || '') !== 'any') {
                    description += ' ' + findValue;
                }

                description += ', replace with ' + replacement + '.';
            }

            issueRow.addClass('has-applied-rule');
            issueRow.next('.rex-validation-applied-rule-row').remove();
            issueRow.after(
                '<tr class="rex-validation-applied-rule-row"><td colspan="5">' +
                    '<div class="rex-validation-applied-rule">' +
                        '<strong>Applied rule:</strong>' +
                        '<span>' + this.escapeHtml(description) + '</span>' +
                        '<button type="button" class="rex-validation-applied-rule__delete" aria-label="Delete applied rule">' +
                            '<span aria-hidden="true">&times;</span>' +
                        '</button>' +
                    '</div>' +
                '</td></tr>'
            );
        },

        /** Delete staged rule and restore row Quick Fix state. */
        deleteQuickFixRule: function(deleteButton) {
            var self = this;
            var appliedRow = deleteButton.closest('.rex-validation-applied-rule-row');
            var issueRow = appliedRow.prev('.rex-validation-row');
            var trigger = issueRow.find('.rex-validation-fix-link');
            var attribute = String(trigger.attr('data-attribute') || '');
            var isMapping = trigger.attr('data-is-mapping') === 'true';

            if (!attribute || deleteButton.prop('disabled')) {
                return;
            }

            if (isMapping) {
                var issueKey = this.getQuickFixIssueKey(trigger);
                var savedRule = this.quickFixRules[issueKey] || {};
                var originalType = savedRule.original_type || trigger.attr('data-original-type') || 'meta';
                var originalValue = (typeof savedRule.original_value !== 'undefined')
                    ? savedRule.original_value
                    : (trigger.attr('data-original-value') || '');

                deleteButton.prop('disabled', true).addClass('is-loading');

                $.ajax({
                    url: rex_wpfm_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'rex_feed_revert_validation_mapping_fix',
                        feed_id: this.feedId,
                        attribute: attribute,
                        original_type: originalType,
                        original_value: originalValue,
                        security: rex_wpfm_ajax.ajax_nonce
                    },
                    success: function(response) {
                        if (!response.success) {
                            self.showNotice('error', response.data.message || self.getTranslation('error_occurred'));
                            return;
                        }

                        self.syncOnPageFeedConfigMapping(attribute, originalType, originalValue);
                        self.resetQuickFixAttribute(attribute);
                        self.showNotice('success', response.data.message || 'Mapping change unstaged.');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        var errorMessage = jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message
                            ? jqXHR.responseJSON.data.message
                            : (errorThrown || self.getTranslation('error_occurred'));
                        self.showNotice('error', errorMessage);
                    },
                    complete: function() {
                        deleteButton.prop('disabled', false).removeClass('is-loading');
                    }
                });
                return;
            }

            deleteButton.prop('disabled', true).addClass('is-loading');

            $.ajax({
                url: rex_wpfm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'rex_feed_delete_validation_fix_rule',
                    feed_id: this.feedId,
                    attribute: attribute,
                    security: rex_wpfm_ajax.ajax_nonce
                },
                success: function(response) {
                    if (!response.success) {
                        self.showNotice('error', response.data.message || self.getTranslation('error_occurred'));
                        return;
                    }

                    self.resetQuickFixAttribute(attribute);
                    self.showNotice('success', response.data.message || 'Feed Rule deleted.');

                    self.refreshFeedRulesPanel().fail(function() {
                        self.showNotice('error', 'Rule deleted, but Feed Rules panel could not refresh. Reload the page to sync it.');
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var errorMessage = jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message
                        ? jqXHR.responseJSON.data.message
                        : (errorThrown || self.getTranslation('error_occurred'));
                    self.showNotice('error', errorMessage);
                },
                complete: function() {
                    deleteButton.prop('disabled', false).removeClass('is-loading');
                }
            });
        },

        /** Stable browser key; reset naturally after feed regeneration. */
        getQuickFixIssueKey: function(trigger) {
            return [
                String(trigger.attr('data-severity') || ''),
                String(trigger.attr('data-attribute') || ''),
                String(trigger.attr('data-rule') || '')
            ].join('|').toLowerCase();
        },

        /** Feed Rule condition label. */
        getConditionLabel: function(condition) {
            var labels = {
                find_and_replace: 'Find & Replace',
                contain: 'Contains',
                dn_contain: 'Does not contain',
                equal_to: 'Is equal to',
                nequal_to: 'Is not equal to',
                greater_than: 'Greater than',
                greater_than_equal: 'Greater than or equal to',
                less_than: 'Less than',
                less_than_equal: 'Less than or equal to',
                any: 'Is Any'
            };

            return labels[condition] || condition;
        },

        /**
         * Save modal values as a Feed Rule without regenerating feed.
         * @since 7.4.58
         * @param {jQuery} submitButton Modal Add Rule button.
         */
        applyFixRule: function(submitButton) {
            var self = this;
            var trigger = this.fixModalTrigger;
            var attribute = trigger && trigger.length ? String(trigger.attr('data-attribute') || '') : '';
            var targetAttribute = trigger && trigger.length ? String(trigger.attr('data-fix-target') || attribute) : attribute;
            var targetLabel = trigger && trigger.length ? String(trigger.attr('data-fix-target-label') || this.formatAttributeLabel(targetAttribute)) : '';
            var ruleType = trigger && trigger.length ? String(trigger.attr('data-rule') || '') : '';
            var isMappingAnomaly = trigger && trigger.length && (trigger.attr('data-is-mapping') === 'true' || /wrongly_assigned|wrong_assigned|anomaly|invalid_mapping/i.test(ruleType));
            if (!attribute) {
                this.showNotice('error', 'Select an attribute.');
                return;
            }

            if (isMappingAnomaly) {
                var mappingType = $('#rex-validation-mapping-type').val();
                var isStaticMapping = mappingType === 'static';
                var mappingReplace = String($('#rex-validation-mapping-value').val() || '');
                var mappingStaticValue = String($('#rex-validation-mapping-static-value').val() || '');
                var mappingReplaceLabel = String($('#rex-validation-mapping-value option:selected').text() || '');

                if ((isStaticMapping && !mappingStaticValue.trim()) || (!isStaticMapping && !mappingReplace)) {
                    this.showNotice('error', 'Select or enter a replacement value.');
                    return;
                }

                submitButton.prop('disabled', true);

                var existingRule = this.quickFixRules[this.getQuickFixIssueKey(trigger)];
                var existingOriginalType = (existingRule && existingRule.original_type) || trigger.attr('data-original-type');
                var existingOriginalValue = (existingRule && typeof existingRule.original_value !== 'undefined')
                    ? existingRule.original_value
                    : trigger.attr('data-original-value');
                var currentOnPage = this.getOnPageFeedConfigMapping(attribute);

                var mappingConfigured = {
                    is_mapping: true,
                    attribute: attribute,
                    attribute_label: this.formatAttributeLabel(attribute),
                    target_attribute: targetAttribute,
                    target_label: targetLabel,
                    candidate: mappingReplace,
                    candidate_label: mappingReplaceLabel,
                    replace: mappingReplace,
                    replace_label: mappingReplaceLabel,
                    is_static: isStaticMapping,
                    static_value: mappingStaticValue
                };

                $.ajax({
                    url: rex_wpfm_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'rex_feed_apply_validation_mapping_fix',
                        feed_id: this.feedId,
                        attribute: attribute,
                        replace: mappingReplace,
                        static_value: mappingStaticValue,
                        is_static: isStaticMapping ? 'yes' : 'no',
                        security: rex_wpfm_ajax.ajax_nonce
                    },
                    success: function(response) {
                        if (!response.success) {
                            self.showNotice('error', response.data.message || self.getTranslation('error_occurred'));
                            return;
                        }

                        var originalType = existingOriginalType || (response.data && response.data.original_type) || (currentOnPage ? currentOnPage.type : 'meta');
                        var originalValue = (typeof existingOriginalValue !== 'undefined')
                            ? existingOriginalValue
                            : ((response.data && typeof response.data.original_value !== 'undefined')
                                ? response.data.original_value
                                : (currentOnPage ? currentOnPage.value : ''));

                        mappingConfigured.original_type = originalType;
                        mappingConfigured.original_value = originalValue;

                        self.hideFixModalAlert();
                        self.syncOnPageFeedConfigMapping(attribute, isStaticMapping ? 'static' : 'meta', isStaticMapping ? mappingStaticValue : mappingReplace);
                        self.closeFixModal();
                        self.setConfiguredQuickFix(trigger, mappingConfigured);
                        self.showNotice('success', response.data.message || 'Mapping updated. Click Fix Feed to regenerate.');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        if (jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.code === 'mapping_already_exists') {
                            self.showFixModalAlert(jqXHR.responseJSON.data.message || 'This mapping has already been applied. Please select a different value.');
                            return;
                        }

                        var errorMessage = jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message
                            ? jqXHR.responseJSON.data.message
                            : (errorThrown || self.getTranslation('error_occurred'));
                        self.showNotice('error', errorMessage);
                    },
                    complete: function() {
                        submitButton.prop('disabled', false);
                    }
                });
                return;
            }

            var condition = String($('#rex-validation-fix-condition').val() || '');
            var find = String($('#rex-validation-fix-find').val() || '');
            var isStatic = $('#rex-validation-fix-static').prop('checked');
            var replace = String($('#rex-validation-fix-replace').val() || '');
            var staticValue = String($('#rex-validation-fix-static-value').val() || '');
            var replaceLabel = String($('#rex-validation-fix-replace option:selected').text() || '');

            if (!condition) {
                this.showNotice('error', 'Select a valid condition.');
                return;
            }

            if ((isStatic && !staticValue.trim()) || (!isStatic && !replace)) {
                this.showNotice('error', 'Select or enter a replacement value.');
                return;
            }

            // Check if identical rule already exists on page or staged before execution
            if (this.isDuplicateFeedRule(targetAttribute, condition, find, isStatic, staticValue, replace)) {
                this.showFixModalAlert('This rule has already been added. Please configure a different rule.');
                return;
            }

            submitButton.prop('disabled', true);

            var configuredRule = {
                attribute: attribute,
                attribute_label: this.formatAttributeLabel(attribute),
                target_attribute: targetAttribute,
                target_label: targetLabel,
                condition: condition,
                condition_label: this.getConditionLabel(condition),
                find: find,
                candidate: replace,
                candidate_label: replaceLabel,
                is_static: isStatic,
                static_value: staticValue
            };

            $.ajax({
                url: rex_wpfm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'rex_feed_apply_validation_fix_rule',
                    feed_id: this.feedId,
                    attribute: attribute,
                    condition: condition,
                    find: find,
                    replace: replace,
                    static_value: staticValue,
                    is_static: isStatic ? 'yes' : 'no',
                    security: rex_wpfm_ajax.ajax_nonce
                },
                success: function(response) {
                    if (!response.success) {
                        if (response.data && response.data.code === 'rule_already_exists') {
                            self.showFixModalAlert(response.data.message || 'This rule has already been added. Please configure a different rule.');
                            return;
                        }

                        self.showNotice('error', response.data.message || self.getTranslation('error_occurred'));
                        return;
                    }

                    self.hideFixModalAlert();
                    self.reloadFeedRules(response.data.message, function() {
                        self.setConfiguredQuickFix(trigger, configuredRule);
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    if (jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.code === 'rule_already_exists') {
                        self.showFixModalAlert(jqXHR.responseJSON.data.message || 'This rule has already been added. Please configure a different rule.');
                        return;
                    }

                    var errorMessage = jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message
                        ? jqXHR.responseJSON.data.message
                        : (errorThrown || self.getTranslation('error_occurred'));
                    self.showNotice('error', errorMessage);
                },
                complete: function() {
                    submitButton.prop('disabled', false);
                }
            });
        },

        /**
         * Get live feed configuration row mapping in editor if DOM element exists.
         */
        getOnPageFeedConfigMapping: function(attribute) {
            var configRows = $('#config-table tbody tr, .wpfm-field-mappings tbody tr, table#config-table tr');
            var mapping = null;
            configRows.each(function() {
                var row = $(this);
                if (row.css('display') === 'none' && row.attr('data-row-id') && row.hasClass('edit-mode')) {
                    return;
                }

                var attrSelect = row.find('select.attr-dropdown, select[name*="[attr]"]');
                var customAttrInput = row.find('input.rex-custom-attribute, input[name*="[cust_attr]"], input[name*="[attr]"]');
                var currentAttr = attrSelect.length ? attrSelect.val() : (customAttrInput.length ? customAttrInput.val() : '');

                if (!currentAttr) {
                    return;
                }

                var cleanCurrent = String(currentAttr).toLowerCase().replace(/^g:/i, '').trim();
                var cleanTarget = String(attribute).toLowerCase().replace(/^g:/i, '').trim();

                if (cleanCurrent === cleanTarget || currentAttr === attribute) {
                    var typeDropdown = row.find('select.type-dropdown, select.type-dropdowns, select[name*="[type]"]');
                    var type = typeDropdown.length ? String(typeDropdown.val() || 'meta') : 'meta';
                    var value = '';

                    if (type === 'static') {
                        var staticField = row.find('input.attribute-val-static-field, input[name*="[st_value]"]');
                        value = staticField.length ? String(staticField.val() || '') : '';
                    } else {
                        var metaDropdown = row.find('select.attr-val-dropdown, select[name*="[meta_key]"]');
                        value = metaDropdown.length ? String(metaDropdown.val() || '') : '';
                    }

                    mapping = {
                        type: type,
                        value: value
                    };
                    return false;
                }
            });
            return mapping;
        },

        /**
         * Update live feed configuration row in editor if DOM element exists.
         */
        syncOnPageFeedConfigMapping: function(attribute, type, value) {
            var configRows = $('#config-table tbody tr, .wpfm-field-mappings tbody tr, table#config-table tr');
            configRows.each(function() {
                var row = $(this);
                if (row.css('display') === 'none' && row.attr('data-row-id') && row.hasClass('edit-mode')) {
                    // Skip hidden template row used for cloning
                    return;
                }

                var attrSelect = row.find('select.attr-dropdown, select[name*="[attr]"]');
                var customAttrInput = row.find('input.rex-custom-attribute, input[name*="[cust_attr]"], input[name*="[attr]"]');
                var currentAttr = attrSelect.length ? attrSelect.val() : (customAttrInput.length ? customAttrInput.val() : '');

                if (!currentAttr) {
                    return;
                }

                var cleanCurrent = String(currentAttr).toLowerCase().replace(/^g:/i, '').trim();
                var cleanTarget = String(attribute).toLowerCase().replace(/^g:/i, '').trim();

                if (cleanCurrent === cleanTarget || currentAttr === attribute) {
                    var typeDropdown = row.find('select.type-dropdown, select.type-dropdowns, select[name*="[type]"]');
                    if (typeDropdown.length) {
                        typeDropdown.val(type).trigger('change');
                    }

                    if (type === 'meta') {
                        row.find('div.static-input').hide();
                        row.find('div.combined-dropdown').hide();
                        row.find('div.meta-dropdown').show();
                        var metaDropdown = row.find('select.attr-val-dropdown, select[name*="[meta_key]"]');
                        if (metaDropdown.length) {
                            metaDropdown.val(value).trigger('change');
                        }
                    } else if (type === 'static') {
                        row.find('div.meta-dropdown').hide();
                        row.find('div.combined-dropdown').hide();
                        row.find('div.static-input').show();
                        var staticField = row.find('input.attribute-val-static-field, input[name*="[st_value]"]');
                        if (staticField.length) {
                            staticField.val(value).trigger('change');
                        }
                    }
                }
            });
        },

        /**
         * Refresh Feed Rule form fields after adding a validation rule.
         * @since 7.4.58
         * @param {string}   successMessage Rule-save confirmation.
         * @param {Function} onComplete     Optional UI callback.
         */
        reloadFeedRules: function(successMessage, onComplete) {
            var self = this;

            this.refreshFeedRulesPanel()
                .done(function() {
                    self.closeFixModal();
                    self.showNotice('success', successMessage || 'Rule added. Add more rules or click Fix Feed.');

                    if (typeof onComplete === 'function') {
                        onComplete();
                    }
                })
                .fail(function() {
                    self.showNotice('error', 'Feed Rule saved, but Feed Rules panel could not refresh. Reload the page before fixing the feed.');
                });
        },

        /** Refresh Pro Feed Rules form when present; free installs need no panel. */
        refreshFeedRulesPanel: function() {
            var deferred = $.Deferred();
            var rulesBody = $('.rex-feed-rules-area .flex-table-body');

            if (!rulesBody.length || typeof wpAjaxHelperRequest !== 'function') {
                deferred.resolve();
                return deferred.promise();
            }

            wpAjaxHelperRequest('rex-feed-handle-feed-rules-content', {
                feed_id: this.feedId,
                event: 'ready'
            })
                .done(function(response) {
                    if (!response || !response.status || !response.markups) {
                        rulesBody.empty();
                        $('.rex-feed-rules-area').hide();
                        $('#rex_feed_rules_button').show();
                        deferred.resolve();
                        return;
                    }

                    rulesBody.empty().append(response.markups);

                    if ($.fn.select2) {
                        rulesBody.find('select.rules-select2').select2();
                    }

                    var feedForm = $('#post');
                    var rulesStatusInput = feedForm.find('input[name="rex_feed_feed_rules_button"]');

                    if (!rulesStatusInput.length) {
                        $('<input>', {
                            type: 'hidden',
                            name: 'rex_feed_feed_rules_button',
                            value: 'added'
                        }).appendTo(feedForm);
                    } else {
                        rulesStatusInput.val('added');
                    }

                    deferred.resolve();
                })
                .fail(function() {
                    deferred.reject();
                });

            return deferred.promise();
        },

        /**
         * Update the enabled/disabled state of the Validate Feed button based on added rules and validation state.
         * @since 7.4.58
         */
        updateFixFeedButtonState: function() {
            if (!this.isPremium) {
                var isFreeDisabled = this.validationDisabled || this.isLoading;
                $('.rex-feed-validate-btn').prop('disabled', isFreeDisabled);
                return;
            }

            var hasAddedRules = Object.keys(this.quickFixRules || {}).length > 0;
            var isDisabled = !hasAddedRules || this.validationDisabled || this.isLoading;
            $('.rex-feed-validate-btn, .rex-feed-fix-btn').prop('disabled', isDisabled);
        },

        /**
         * Regenerate feed once with every Feed Rule added from validation.
         * @since 7.4.58
         * @param {jQuery} fixButton Header Fix Feed button.
         */
        fixFeed: function(fixButton) {
            if (typeof wpAjaxHelperRequest !== 'function') {
                this.showNotice('error', 'Feed regeneration could not start. Reload the page and try again.');
                return;
            }

            fixButton.prop('disabled', true);
            this.showNotice('success', 'Applying added rules and regenerating feed.');
            $(document).trigger('rex-feed-regenerate-after-validation-fix');
        },

        /**
         * Match Feed Rule Static behavior: attribute select or manual value.
         * @since 7.4.58
         * @param {boolean} useStatic Whether manual value is active.
         */
        toggleFixRuleStatic: function(useStatic) {
            var replaceSelect = $('#rex-validation-fix-replace');
            var select2Container = replaceSelect.next('.select2-container');
            var staticInput = $('#rex-validation-fix-static-value');

            if (useStatic) {
                if ($.fn.select2 && replaceSelect.hasClass('select2-hidden-accessible')) {
                    replaceSelect.select2('close');
                }
                replaceSelect.hide();
                select2Container.hide();
                staticInput.prop('hidden', false).trigger('focus');
            } else {
                staticInput.prop('hidden', true);
                replaceSelect.show();
                select2Container.show();
            }
        },

        /**
         * Toggle Mapping fix value between attribute select and static text input.
         * @since 7.4.58
         * @param {boolean} isStatic
         */
        toggleMappingStatic: function(isStatic) {
            var valueSelect = $('#rex-validation-mapping-value');
            var select2Container = valueSelect.next('.select2-container');
            var staticInput = $('#rex-validation-mapping-static-value');

            if (isStatic) {
                if ($.fn.select2 && valueSelect.hasClass('select2-hidden-accessible')) {
                    valueSelect.select2('close');
                }
                valueSelect.hide();
                select2Container.hide();
                staticInput.show().trigger('focus');
            } else {
                staticInput.hide();
                valueSelect.show();
                select2Container.show();
                if ($.fn.select2 && valueSelect.hasClass('select2-hidden-accessible')) {
                    valueSelect.trigger('change.select2');
                }
            }
        },

        /**
         * Show an alert message inside the Quick Fix modal.
         * @since 7.4.60
         * @param {string} message Alert message to display.
         */
        showFixModalAlert: function(message) {
            var modal = $('#rex-validation-fix-modal');
            var alertEl = modal.find('.rex-validation-fix-modal__alert');
            var alertMsg = modal.find('.rex-validation-fix-modal__alert-message');

            alertMsg.text(message || 'This rule has already been added. Please configure a different rule.');
            alertEl.show();

            var dialog = modal.find('.rex-validation-fix-modal__dialog');
            if (dialog.length) {
                dialog.animate({ scrollTop: 0 }, 150);
            }
        },

        /**
         * Hide the alert message inside the Quick Fix modal.
         * @since 7.4.60
         */
        hideFixModalAlert: function() {
            $('#rex-validation-fix-modal .rex-validation-fix-modal__alert').hide();
        },

        /**
         * Check whether an identical Feed Rule already exists on page or staged.
         *
         * @param {string} targetAttribute Target attribute.
         * @param {string} condition Condition key.
         * @param {string} find Find value.
         * @param {boolean} isStatic Whether replacement is static.
         * @param {string} staticValue Static replacement value.
         * @param {string} replace Attribute replacement value.
         * @return {boolean} True if duplicate rule found.
         */
        isDuplicateFeedRule: function(targetAttribute, condition, find, isStatic, staticValue, replace) {
            var normTarget = String(targetAttribute || '').trim().replace(/^g:/i, '').toLowerCase();
            var normCondition = String(condition || '').trim();
            var normFind = String(find || '').trim().toLowerCase();
            var normStatic = !!isStatic;
            var normStaticVal = String(staticValue || '').trim().toLowerCase();
            var normReplace = String(replace || '').trim().replace(/^g:/i, '').toLowerCase();

            // 1. Check existing on-page feed rules table
            var onPageRows = $('.rex-feed-rules-area .flex-table-body .flex-table-row:not(.rexfeed-hidded-section)');
            var foundDuplicate = false;

            onPageRows.each(function() {
                var row = $(this);
                var rowIf = String(row.find('select[name*="[rules_if]"], input[name*="[cust_rules_if]"]').val() || '').trim().replace(/^g:/i, '').toLowerCase();
                var rowCondition = String(row.find('select[name*="[rules_condition]"]').val() || '').trim();
                var rowFind = String(row.find('input[name*="[rules_find]"]').val() || '').trim().toLowerCase();
                var rowThen = String(row.find('select[name*="[rules_then]"]').val() || '').trim().replace(/^g:/i, '').toLowerCase();
                var rowStatic = row.find('input[name*="[rules_static]"]').prop('checked');
                var rowStaticVal = String(row.find('input[name*="[rules_static_replace]"]').val() || '').trim().toLowerCase();
                var rowReplace = String(row.find('select[name*="[rules_replace]"]').val() || '').trim().replace(/^g:/i, '').toLowerCase();

                if (rowIf === normTarget && rowThen === normTarget && rowCondition === normCondition && rowFind === normFind && rowStatic === normStatic) {
                    if (normStatic && rowStaticVal === normStaticVal) {
                        foundDuplicate = true;
                        return false;
                    }
                    if (!normStatic && rowReplace === normReplace) {
                        foundDuplicate = true;
                        return false;
                    }
                }
            });

            if (foundDuplicate) {
                return true;
            }

            // 2. Check staged rules in this.quickFixRules
            for (var key in this.quickFixRules) {
                if (!this.quickFixRules.hasOwnProperty(key)) {
                    continue;
                }
                var staged = this.quickFixRules[key];
                if (staged && !staged.is_mapping) {
                    var stagedTarget = String(staged.target_attribute || staged.attribute || '').trim().replace(/^g:/i, '').toLowerCase();
                    var stagedCondition = String(staged.condition || '').trim();
                    var stagedFind = String(staged.find || '').trim().toLowerCase();
                    var stagedStatic = !!staged.is_static;
                    var stagedStaticVal = String(staged.static_value || '').trim().toLowerCase();
                    var stagedReplace = String(staged.candidate || staged.replace || '').trim().replace(/^g:/i, '').toLowerCase();

                    if (stagedTarget === normTarget && stagedCondition === normCondition && stagedFind === normFind && stagedStatic === normStatic) {
                        if (normStatic && stagedStaticVal === normStaticVal) {
                            return true;
                        }
                        if (!normStatic && stagedReplace === normReplace) {
                            return true;
                        }
                    }
                }
            }

            return false;
        },

        /**
         * Close fix-rule modal and restore focus.
         * @since 7.4.58
         */
        closeFixModal: function() {
            this.hideFixModalAlert();
            var modal = $('#rex-validation-fix-modal');
            var self = this;
            var animationDuration = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 200;

            if (!modal.length || modal.prop('hidden') || modal.hasClass('is-closing')) {
                return;
            }

            if ($.fn.select2) {
                modal.find('.rex-validation-fix-rule__select2.select2-hidden-accessible').select2('close');
            }

            modal.removeClass('is-open').addClass('is-closing').attr('aria-hidden', 'true');

            window.clearTimeout(this.fixModalCloseTimer);
            this.fixModalCloseTimer = window.setTimeout(function() {
                var trigger = self.fixModalTrigger && self.fixModalTrigger.get(0);

                modal.removeClass('is-closing').prop('hidden', true);
                self.unlockFixModalScroll();

                if (trigger) {
                    try {
                        trigger.focus({ preventScroll: true });
                    } catch (e) {
                        trigger.focus();
                    }
                }
                self.fixModalTrigger = null;
            }, animationDuration);
        },

        /**
         * Get short reason for a validation issue.
         * @since 7.4.58
         * @param {string} rule Issue rule identifier.
         * @param {string} message Full issue message.
         * @param {string} attribute Attribute name.
         * @return {string} Short reason string.
         */
        getIssueShortReason: function(rule, message, attribute) {
            var lowerRule = String(rule || '').toLowerCase().trim();
            var lowerMsg = String(message || '').toLowerCase().trim();
            var lowerAttr = String(attribute || '').toLowerCase().trim();

            // 1. Missing / Empty attributes
            if (lowerRule === 'required_attribute_missing' ||
                lowerRule === 'required_attribute_empty' ||
                lowerRule === 'missing_attribute' ||
                lowerMsg.indexOf('empty or missing') !== -1 ||
                lowerMsg.indexOf('is not mapped') !== -1 ||
                lowerMsg.indexOf('required attribute') !== -1 ||
                lowerMsg.indexOf('missing required') !== -1) {
                return 'Missing attribute';
            }

            // 2. Wrongly assigned value / Mapping anomaly
            if (lowerRule === 'wrongly_assigned_value' ||
                lowerRule === 'mapping_anomaly' ||
                lowerMsg.indexOf('wrongly assigned') !== -1) {
                return 'Wrongly assigned value';
            }

            // 3. Length checks
            if (lowerRule === 'attribute_too_long' ||
                lowerRule === 'max_length' ||
                lowerMsg.indexOf('exceeds maximum length') !== -1 ||
                lowerMsg.indexOf('too long') !== -1) {
                return 'Attribute too long';
            }
            if (lowerRule === 'attribute_too_short' ||
                lowerRule === 'min_length' ||
                lowerMsg.indexOf('shorter than minimum') !== -1 ||
                lowerMsg.indexOf('too short') !== -1) {
                return 'Attribute too short';
            }

            // 4. URL checks
            if (lowerRule === 'invalid_url_protocol' || lowerMsg.indexOf('must use https') !== -1) {
                return 'Invalid URL protocol';
            }
            if (lowerRule === 'invalid_url' || lowerMsg.indexOf('invalid url') !== -1) {
                return 'Invalid URL';
            }

            // 5. Price / Currency
            if (lowerRule === 'invalid_price' || lowerMsg.indexOf('invalid price') !== -1) {
                return 'Invalid price';
            }
            if (lowerRule === 'invalid_currency' || lowerMsg.indexOf('invalid currency') !== -1) {
                return 'Invalid currency';
            }

            // 6. Enum / Value
            if (lowerRule === 'invalid_enum_value' || lowerMsg.indexOf('not valid for attribute') !== -1) {
                return 'Invalid value';
            }

            // 7. Image checks
            if (lowerRule.indexOf('image') !== -1 || lowerMsg.indexOf('image') !== -1) {
                if (lowerMsg.indexOf('missing') !== -1 || lowerRule.indexOf('missing') !== -1) {
                    return 'Missing image';
                }
                return 'Invalid image';
            }

            // 8. Category checks
            if (lowerRule.indexOf('category') !== -1 || lowerMsg.indexOf('category') !== -1) {
                return 'Invalid category';
            }

            // 9. Identifier (GTIN, MPN)
            if (lowerRule.indexOf('identifier') !== -1 || lowerMsg.indexOf('identifier') !== -1 ||
                lowerRule.indexOf('gtin') !== -1 || lowerRule.indexOf('mpn') !== -1) {
                return 'Invalid identifier';
            }

            // 10. Clean message fallback: strip attribute reference and make it a short reason
            if (message) {
                var cleanMsg = message.replace(new RegExp('for attribute [\'"]?' + lowerAttr + '[\'"]?', 'gi'), '')
                                      .replace(new RegExp('attribute [\'"]?' + lowerAttr + '[\'"]?', 'gi'), '')
                                      .replace(/[.:]+$/, '')
                                      .trim();
                if (cleanMsg.length > 0 && cleanMsg.length <= 35) {
                    return cleanMsg.charAt(0).toUpperCase() + cleanMsg.slice(1);
                }
            }

            // 11. Format rule as words: e.g. "title_all_caps" -> "Title all caps"
            if (lowerRule && lowerRule !== 'general' && lowerRule !== 'unknown') {
                var formatted = lowerRule.replace(/_/g, ' ');
                return formatted.charAt(0).toUpperCase() + formatted.slice(1);
            }

            return 'Issue';
        },

        /**
         * Open products modal with affected products for the clicked issue.
         * @since 7.4.58
         * @param {jQuery} trigger Clicked "View Products" link.
         */
        openProductsModal: function(trigger) {
            var modal = $('#rex-validation-products-modal');
            if (!modal.length) {
                return;
            }

            var self = this;
            var attribute = String(trigger.attr('data-attribute') || 'General');
            var rule = String(trigger.attr('data-rule') || '');
            var severity = String(trigger.attr('data-severity') || 'error');
            var message = String(trigger.attr('data-message') || '').trim();

            this.productsModalTrigger = trigger;
            this.lockFixModalScroll();

            // Format dynamic subtitle: Short Reason: <Attribute Name>
            var shortReason = this.getIssueShortReason(rule, message, attribute);
            var displayAttribute = attribute || 'General';

            modal.find('.rex-validation-products-modal__subtitle-label').text(shortReason + ':');
            modal.find('.rex-validation-products-modal__subtitle-value').text(' ' + displayAttribute);

            // Open modal
            modal.prop('hidden', false).removeAttr('aria-hidden');
            window.requestAnimationFrame(function() {
                modal.addClass('is-open');
            });

            // Reset pagination container
            $('#rex-validation-products-modal-pagination').empty();

            // Focus close button
            var closeButton = modal.find('.rex-validation-products-modal__close').get(0);
            if (closeButton) {
                try {
                    closeButton.focus({ preventScroll: true });
                } catch (e) {
                    closeButton.focus();
                }
            }

            // Save query state and load first page
            this.currentProductsQuery = {
                attribute: attribute,
                rule: rule,
                severity: severity,
                page: 1,
                per_page: 10,
                totalPages: 1
            };

            this.loadProductsModalPage(1);
        },

        /**
         * Load specific page of products in the products modal.
         * @since 7.4.58
         * @param {number} page Page number to load.
         */
        loadProductsModalPage: function(page) {
            var self = this;
            if (!this.currentProductsQuery) {
                return;
            }

            var tbody = $('#rex-validation-products-modal-tbody');
            var pagination = $('#rex-validation-products-modal-pagination');

            tbody.html('<tr><td colspan="2" class="rex-validation-products-modal__loading">' +
                '<span class="spinner is-active" style="float:none;margin:0 8px 0 0;vertical-align:middle;"></span>' +
                'Loading products...' +
                '</td></tr>');

            var query = this.currentProductsQuery;
            query.page = parseInt(page, 10) || 1;

            $.ajax({
                url: rex_wpfm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'rex_feed_get_validation_issue_products',
                    feed_id: self.feedId,
                    attribute: query.attribute,
                    rule: query.rule,
                    severity: query.severity,
                    page: query.page,
                    per_page: query.per_page || 10,
                    security: rex_wpfm_ajax.ajax_nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        var data = response.data;
                        var products = data.products || [];
                        var totalPages = parseInt(data.total_pages, 10) || 1;
                        var currentPage = parseInt(data.page, 10) || query.page;

                        query.totalPages = totalPages;
                        query.page = currentPage;

                        if (products.length > 0) {
                            self.renderProductsList(products);
                            self.renderProductsPagination(currentPage, totalPages);
                        } else {
                            tbody.html('<tr><td colspan="2" class="rex-validation-products-modal__empty">No products found for this issue.</td></tr>');
                            pagination.empty();
                        }
                    } else {
                        tbody.html('<tr><td colspan="2" class="rex-validation-products-modal__empty">No products found for this issue.</td></tr>');
                        pagination.empty();
                    }
                },
                error: function() {
                    tbody.html('<tr><td colspan="2" class="rex-validation-products-modal__error">Failed to load products.</td></tr>');
                    pagination.empty();
                }
            });
        },

        /**
         * Render products pagination in the products modal footer.
         * Pagination style: |<  <  [1] [2] [3] .. [Last]  >  >|
         * @since 7.4.58
         * @param {number} currentPage Current active page.
         * @param {number} totalPages Total number of pages.
         */
        renderProductsPagination: function(currentPage, totalPages) {
            var pagination = $('#rex-validation-products-modal-pagination');
            if (!pagination.length) {
                return;
            }

            currentPage = parseInt(currentPage, 10) || 1;
            totalPages = parseInt(totalPages, 10) || 1;

            if (totalPages <= 1) {
                pagination.empty();
                return;
            }

            var self = this;
            var ellipsis = '<span class="rex-validation-products-page-ellipsis" aria-hidden="true">..</span>';
            var visiblePages = [];
            var numbersHtml = '';

            if (totalPages <= 4) {
                for (var p = 1; p <= totalPages; p++) {
                    visiblePages.push(p);
                    numbersHtml += self.getProductPageNumberButton(p, currentPage);
                }
            } else if (currentPage >= (totalPages - 2)) {
                // Near the end: .. [totalPages-3] [totalPages-2] [totalPages-1] [totalPages]
                numbersHtml += ellipsis;
                for (var ep = totalPages - 3; ep <= totalPages; ep++) {
                    visiblePages.push(ep);
                    numbersHtml += self.getProductPageNumberButton(ep, currentPage);
                }
            } else {
                // Near start or middle: [start] [start+1] [start+2] .. [totalPages]
                var start = currentPage <= 2 ? 1 : (currentPage - 1);
                for (var sp = start; sp <= start + 2; sp++) {
                    visiblePages.push(sp);
                    numbersHtml += self.getProductPageNumberButton(sp, currentPage);
                }
                numbersHtml += ellipsis;
                visiblePages.push(totalPages);
                numbersHtml += self.getProductPageNumberButton(totalPages, currentPage);
            }

            // Determine button states:
            // "when the numbering button for first page [1] gets invisible then the First Page navigation responsible button will be enable"
            var isFirstDisabled = visiblePages.indexOf(1) !== -1;
            var isPrevDisabled = currentPage <= 1;
            var prevPage = Math.max(1, currentPage - 1);
            var isNextDisabled = currentPage >= totalPages;
            var nextPage = Math.min(totalPages, currentPage + 1);
            var isLastDisabled = currentPage >= totalPages;

            var html = '';

            // First page button: |<
            html += '<button type="button" class="rex-validation-page-button rex-validation-products-page-btn rex-validation-first-page" data-page="1" aria-label="First page"' + (isFirstDisabled ? ' disabled' : '') + '>';
            html += '<span class="rex-validation-page-icon" aria-hidden="true"></span>';
            html += '</button>';

            // Previous page button: <
            html += '<button type="button" class="rex-validation-page-button rex-validation-products-page-btn rex-validation-prev-page" data-page="' + prevPage + '" aria-label="Previous page"' + (isPrevDisabled ? ' disabled' : '') + '>';
            html += '<span class="rex-validation-page-icon" aria-hidden="true"></span>';
            html += '</button>';

            // Page numbers
            html += numbersHtml;

            // Next page button: >
            html += '<button type="button" class="rex-validation-page-button rex-validation-products-page-btn rex-validation-next-page" data-page="' + nextPage + '" aria-label="Next page"' + (isNextDisabled ? ' disabled' : '') + '>';
            html += '<span class="rex-validation-page-icon" aria-hidden="true"></span>';
            html += '</button>';

            // Last page button: >|
            html += '<button type="button" class="rex-validation-page-button rex-validation-products-page-btn rex-validation-last-page" data-page="' + totalPages + '" aria-label="Last page"' + (isLastDisabled ? ' disabled' : '') + '>';
            html += '<span class="rex-validation-page-icon" aria-hidden="true"></span>';
            html += '</button>';

            pagination.html(html);
        },

        /**
         * Generate single page number button HTML.
         * @since 7.4.58
         * @param {number} page Page number.
         * @param {number} currentPage Current active page.
         * @return {string} HTML string.
         */
        getProductPageNumberButton: function(page, currentPage) {
            var isActive = page === currentPage;
            var activeClass = isActive ? ' is-active' : '';
            var ariaCurrent = isActive ? ' aria-current="page"' : '';
            return '<button type="button" class="rex-validation-products-page-number' + activeClass + '" data-page="' + page + '" aria-label="Page ' + page + '"' + ariaCurrent + '>' + page + '</button>';
        },

        /**
         * Render products list inside products modal table.
         * @since 7.4.58
         * @param {Array} products Array of product objects.
         */
        renderProductsList: function(products) {
            var tbody = $('#rex-validation-products-modal-tbody');
            var html = '';

            for (var i = 0; i < products.length; i++) {
                var product = products[i];
                var rawTitle = product.title || ('Product #' + product.id);
                var fullTitle = product.full_title || rawTitle;

                // Ensure at most 70 characters
                var displayTitle = rawTitle;
                if (displayTitle.length > 70) {
                    displayTitle = displayTitle.substring(0, 67) + '...';
                }

                var titleEscaped = this.escapeHtml(displayTitle);
                var fullTitleEscaped = this.escapeAttribute(fullTitle);
                var editUrl = this.escapeAttribute(product.edit_url || '#');

                html += '<tr class="rex-validation-products-table__row">';
                html += '<td class="rex-validation-products-table__td rex-validation-products-table__td--product">';
                html += '<span class="rex-validation-products-table__product-name" title="' + fullTitleEscaped + '">' + titleEscaped + '</span>';
                html += '</td>';
                html += '<td class="rex-validation-products-table__td rex-validation-products-table__td--link">';
                html += '<a href="' + editUrl + '" class="rex-validation-products-table__link" target="_blank" rel="noopener noreferrer">View</a>';
                html += '</td>';
                html += '</tr>';
            }

            tbody.html(html);
        },

        /**
         * Close products modal.
         * @since 7.4.58
         */
        closeProductsModal: function() {
            var modal = $('#rex-validation-products-modal');
            var self = this;
            var animationDuration = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 200;

            if (!modal.length || modal.prop('hidden') || modal.hasClass('is-closing')) {
                return;
            }

            modal.removeClass('is-open').addClass('is-closing').attr('aria-hidden', 'true');

            window.clearTimeout(this.productsModalCloseTimer);
            this.productsModalCloseTimer = window.setTimeout(function() {
                var trigger = self.productsModalTrigger && self.productsModalTrigger.get(0);

                modal.removeClass('is-closing').prop('hidden', true);
                self.unlockFixModalScroll();

                if (trigger) {
                    try {
                        trigger.focus({ preventScroll: true });
                    } catch (e) {
                        trigger.focus();
                    }
                }
                self.productsModalTrigger = null;
                self.currentProductsQuery = null;
                $('#rex-validation-products-modal-pagination').empty();
            }, animationDuration);
        },

        /**
         * Convert feed attribute key to readable modal label.
         * @since 7.4.58
         * @param {string} attribute Feed attribute key.
         * @return {string} Readable label.
         */
        formatAttributeLabel: function(attribute) {
            return attribute
                .replace(/^g:/i, '')
                .replace(/[_-]+/g, ' ')
                .replace(/\b\w/g, function(character) {
                    return character.toUpperCase();
                });
        },

        /**
         * Persist both validation menu options for the current feed.
         * @since 7.4.58
         * @param {jQuery} changedInput Checkbox that triggered the save.
         */
        saveValidationSettings: function(changedInput) {
            var self = this;
            var settings = $('.rex-feed-validation-setting');
            var previousValue = !changedInput.prop('checked');
            var isToggle = changedInput.is('#rex-feed-validation-toggle-input') || changedInput.is('[data-setting="validation-toggle"]');
            var isValidationEnabled = isToggle
                ? changedInput.prop('checked')
                : !this.validationDisabled;
            var validationDisabled = !isValidationEnabled;
            var excludeErrorProducts = $('[data-setting="exclude-error-products"]').prop('checked');

            this.validationDisabled = validationDisabled;
            this.syncValidationState();
            settings.prop('disabled', true);

            $.ajax({
                url: rex_wpfm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'rex_feed_save_validation_settings',
                    feed_id: this.feedId,
                    validation_disabled: validationDisabled ? 'yes' : 'no',
                    exclude_error_products: excludeErrorProducts ? 'yes' : 'no',
                    security: rex_wpfm_ajax.ajax_nonce
                },
                success: function(response) {
                    if (!response.success) {
                        changedInput.prop('checked', previousValue);
                        self.validationDisabled = !$('#rex-feed-validation-toggle-input').prop('checked');
                        self.syncValidationState();
                        self.showNotice('error', response.data.message || self.getTranslation('error_occurred'));
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    changedInput.prop('checked', previousValue);
                    self.validationDisabled = !$('#rex-feed-validation-toggle-input').prop('checked');
                    self.syncValidationState();

                    var errorMessage = jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message
                        ? jqXHR.responseJSON.data.message
                        : (errorThrown || self.getTranslation('error_occurred'));
                    self.showNotice('error', errorMessage);
                },
                complete: function() {
                    settings.prop('disabled', false);
                }
            });
        },

        /**
         * Keep validation controls aligned with the saved disabled state.
         * @since 7.4.58
         */
        syncValidationState: function() {
            this.wrapper.attr('data-validation-disabled', this.validationDisabled ? '1' : '0');
            this.updateFixFeedButtonState();
            $('#rex-feed-validation-toggle-input').prop('checked', !this.validationDisabled);
            $('[data-setting="exclude-error-products"]').prop('disabled', this.validationDisabled);
            if (this.validationDisabled) {
                $('.rex-feed-validation-exclude-btn').addClass('is-disabled');
                $('.rex-feed-validation-body').hide();
            } else {
                $('.rex-feed-validation-exclude-btn').removeClass('is-disabled');
                $('.rex-feed-validation-body').show();
            }
        },

        /**
         * Synchronize severity tabs and summary card states.
         * @since 7.4.58
         * @param {string} severity Active severity.
         */
        updateSeverityControls: function(severity) {
            var $severityTabs = $('.rex-validation-severity-tab');

            $severityTabs
                .removeClass('is-active')
                .filter('[data-severity="' + severity + '"]')
                .addClass('is-active');

            var activeSeverityLabel = $severityTabs.filter('.is-active').text().trim();
            var allLabel = $('.rex-feed-validation-table .column-severity').data('all-label') || 'All';
            $('.rex-validation-severity-heading').text(allLabel + ' ' + activeSeverityLabel);

            $('.rex-feed-validation-card--clickable').removeClass('rex-feed-validation-card--active');

            if (severity) {
                $('.rex-feed-validation-card--' + severity).addClass('rex-feed-validation-card--active');
            }

            this.toggleFixIssuesColumn(severity);
        },

        /**
         * Show or hide Fix Issues column depending on severity (only error tab needs Fix Issues).
         * @since 7.4.58
         * @param {string} severity Active severity.
         */
        toggleFixIssuesColumn: function(severity) {
            severity = severity || (this.filters && this.filters.severity) || 'error';
            var showAction = (severity === 'error');
            var table = $('.rex-feed-validation-table');

            table.find('th.column-action').toggle(showAction);
            table.toggleClass('hide-fix-issues-column', !showAction);
        },

        /**
         * Load initial results if available.
         * @since 7.4.58
         */
        loadInitialResults: function() {
            if (this.wrapper.find('.rex-feed-validation-results-area').length) {
                this.loadResults();
            }
        },

        /**
         * Validate the feed.
         * @since 7.4.58
         * @param {boolean} isAutoValidation - Whether this is an automatic validation after feed generation
         */
        validateFeed: function(isAutoValidation) {
            var self = this;
            isAutoValidation = isAutoValidation || false;

            if (this.isLoading || this.validationDisabled) {
                return;
            }

            this.isLoading = true;
            this.showProgress();

            $.ajax({
                url: rex_wpfm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'rex_feed_validate_feed',
                    feed_id: this.feedId,
                    security: rex_wpfm_ajax.ajax_nonce
                },
                success: function(response) {
                    self.hideProgress();
                    self.isLoading = false;

                    if (response.success) {
                        self.showNotice('success', response.data.message);
                        // Always reload: the PHP template only renders the results
                        // table, summary cards, etc. when results exist.  If we
                        // cleared results before validation ran (which we do on
                        // every update), those DOM elements won't be on the page
                        // yet, so calling loadResults() would silently fail.  A
                        // full reload lets PHP re-render the complete UI with the
                        // fresh data.
                        location.reload();
                    } else {
                        self.showNotice('error', response.data.message || self.getTranslation('validation_failed'));
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    self.hideProgress();
                    self.isLoading = false;
                    var errorMessage = jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message 
                        ? jqXHR.responseJSON.data.message 
                        : (errorThrown || self.getTranslation('error_during_validation'));
                    self.showNotice('error', errorMessage);
                }
            });
        },

        /**
         * Load validation results.
         * @since 7.4.58
         */
        loadResults: function() {
            var self = this;

            if (this.isLoading) {
                return;
            }

            this.isLoading = true;
            this.showTableLoading();

            $.ajax({
                url: rex_wpfm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'rex_feed_get_validation_results',
                    feed_id: this.feedId,
                    page: this.currentPage,
                    per_page: this.perPage,
                    filters: this.filters,
                    security: rex_wpfm_ajax.ajax_nonce
                },
                success: function(response) {
                    self.isLoading = false;

                    if (response.success) {
                        self.renderResults(response.data.results);
                        self.updatePagination(response.data.results);
                        
                        // Always use full summary for cards, regardless of filters
                        var summaryToUse = response.data.summary;

                        self.updateSummaryCards(summaryToUse);
                        
                        // Store full attribute summary on first load
                        if (response.data.attribute_summary && self.fullAttributeSummary.length === 0) {
                            self.fullAttributeSummary = response.data.attribute_summary;
                        }
                        
                        // Update attribute dropdown with current filtered counts
                        self.updateAttributeFilter(response.data.has_filters, self.filters.severity);
                    } else {
                        self.showTableError(response.data.message || self.getTranslation('failed_to_load'));
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    self.isLoading = false;
                    var errorMessage = jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message 
                        ? jqXHR.responseJSON.data.message 
                        : (errorThrown || self.getTranslation('error_loading_results'));
                    self.showTableError(errorMessage);
                }
            });
        },

        /**
         * Update summary cards with validation results.
         * @since 7.4.58
         */
        updateSummaryCards: function(summary) {
            var errors = summary ? (summary.total_errors || 0) : 0;
            var warnings = summary ? (summary.total_warnings || 0) : 0;
            var info = summary ? (summary.total_info || 0) : 0;
            // Update error count
            $('.rex-feed-validation-card--error .card-number').text(errors);
            
            // Update warning count
            $('.rex-feed-validation-card--warning .card-number').text(warnings);
            
            // Update info/suggestions count
            $('.rex-feed-validation-card--info .card-number').text(info);
            
            // Update filter controls based on current filter.
            var currentSeverityFilter = $('#rex-validation-severity-filter').val();
            this.updateSeverityControls(currentSeverityFilter);

            // Remove existing indicators
            $('.rex-feed-validation-filter-indicator').remove();
            $('.rex-feed-validation-truncation-warning').remove();
            $('.rex-feed-validation-display-limit-banner').remove();
        },

        /**
         * Update attribute filter dropdown with counts based on active severity filter.
         * @since 7.4.58
         */
        updateAttributeFilter: function(hasFilters, severityFilter) {
            var self = this;
            var $attributeFilter = $('#rex-validation-attribute-filter');
            var currentValue = $attributeFilter.val();
            
            if (!self.fullAttributeSummary || self.fullAttributeSummary.length === 0) {
                return;
            }
            
            // Clear existing options except 'All'
            $attributeFilter.find('option:not([value=""])').remove();
            
            // Build new options based on filtered data
            self.fullAttributeSummary.forEach(function(attr) {
                var count = attr.total;
                
                // If severity filter is active, show count for that severity only
                if (severityFilter) {
                    count = attr[severityFilter] || 0;
                }
                
                // Only add option if there are items with this count
                if (count > 0 || !hasFilters) {
                    var optionText = attr.attribute + ' (' + count + ')';
                    $attributeFilter.append(
                        $('<option></option>')
                            .attr('value', attr.attribute)
                            .text(optionText)
                    );
                }
            });
            
            // Restore previous selection if it still exists
            if (currentValue && $attributeFilter.find('option[value="' + currentValue + '"]').length > 0) {
                $attributeFilter.val(currentValue);
            } else if (currentValue) {
                // If previous selection no longer exists, clear it and reset filter
                $attributeFilter.val('');
            }
        },

        /**
         * Render results in the table.
         * @since 7.4.58
         */
        renderResults: function(data) {
            var self = this;
            var tbody = $('#rex-validation-results-body');
            tbody.empty();

            var showAction = (this.filters.severity === 'error' || !this.filters.severity);
            var colSpan = showAction ? 5 : 4;
            this.toggleFixIssuesColumn(this.filters.severity);

            if (!data.items || data.items.length === 0) {
                tbody.html(
                    '<tr class="rex-validation-no-results">' +
                    '<td colspan="' + colSpan + '">' + this.getTranslation('no_results') + '</td>' +
                    '</tr>'
                );
                return;
            }

            var rows = '';
            data.items.forEach(function(item) {
                // One row represents one exact severity/attribute/rule issue.
                var productCount = parseInt(item.product_count, 10) || 0;
                var affectedProducts = 'Affected ' + productCount.toLocaleString() + (productCount === 1 ? ' product. ' : ' products. ');
                var attribute = self.escapeHtml(item.attribute || 'General');
                var issueMessage = self.escapeHtml(item.message || '');
                var issueRule = item.rule || 'general';
                var isNonPro = !self.isPremium;
                var buttonClasses = 'rex-validation-fix-link' + (isNonPro ? ' rex-validation-fix-link--non-pro' : '');
                var editAction = '<button type="button" class="' + buttonClasses + '"' +
                    ' data-attribute="' + self.escapeAttribute(item.attribute || 'General') + '"' +
                    ' data-rule="' + self.escapeAttribute(issueRule) + '"' +
                    ' data-product-count="' + productCount + '"' +
                    ' data-severity="' + self.escapeAttribute(item.severity || 'error') + '"' +
                    ' data-message="' + self.escapeAttribute(item.message || '') + '">' +
                    self.getQuickFixButtonContent(false) +
                    '</button>';
                var viewProductsButton = '<button type="button" class="rex-validation-view-products-link"' +
                    ' data-attribute="' + self.escapeAttribute(item.attribute || 'General') + '"' +
                    ' data-rule="' + self.escapeAttribute(issueRule) + '"' +
                    ' data-product-count="' + productCount + '"' +
                    ' data-severity="' + self.escapeAttribute(item.severity || 'error') + '"' +
                    ' data-message="' + self.escapeAttribute(item.message || '') + '">' +
                    self.getTranslation('view_products') +
                    '</button>';

                rows += '<tr class="rex-validation-row rex-validation-row--' + item.severity + '">' +
                    '<td class="column-severity">' +
                    '<span class="rex-severity-badge rex-severity-badge--' + item.severity + '">' +
                    item.severity +
                    '</span>' +
                    '</td>' +
                    '<td class="column-attribute"><span>' + attribute + '</span></td>' +
                    '<td class="column-message"><em>' + issueMessage + '</em></td>' +
                    '<td class="column-product">' +
                    '<span class="rex-validation-affected-text">' + affectedProducts + '</span>' +
                    viewProductsButton +
                    '</td>' +
                    (showAction ? '<td class="column-action">' + editAction + '</td>' : '') +
                    '</tr>';
            });

            tbody.html(rows);

            if (showAction) {
                tbody.find('.rex-validation-fix-link').each(function() {
                    var trigger = $(this);
                    var savedRule = self.quickFixRules[self.getQuickFixIssueKey(trigger)];

                    if (savedRule) {
                        self.setConfiguredQuickFix(trigger, savedRule);
                    }
                });
            }

            this.updateFixFeedButtonState();
        },

        /**
         * Update pagination controls.
         * @since 7.4.58
         */
        updatePagination: function(data) {
            var totalPages = data.total_pages || 1;
            var total = data.total || 0;
            var start = total > 0 ? ((this.currentPage - 1) * this.perPage) + 1 : 0;
            var end = Math.min(this.currentPage * this.perPage, total);

            // Update showing info
            $('.rex-feed-validation-pagination__info .showing-info').text(
                'Showing ' + start + '-' + end + ' of ' + total + ' attributes'
            );

            // Update page info
            $('.page-info').text('Page ' + this.currentPage + ' of ' + totalPages);

            // Update button states
            $('.rex-validation-first-page').prop('disabled', this.currentPage <= 1);
            $('.rex-validation-prev-page').prop('disabled', this.currentPage <= 1);
            $('.rex-validation-next-page').prop('disabled', this.currentPage >= totalPages);
            $('.rex-validation-last-page')
                .prop('disabled', this.currentPage >= totalPages)
                .data('total-pages', totalPages);
        },

        /**
         * Apply filters and reload results.
         * @since 7.4.58
         */
        applyFilters: function() {
            this.filters = {
                severity: $('#rex-validation-severity-filter').val(),
                attribute: $('#rex-validation-attribute-filter').val(),
                search: $('#rex-validation-search').val()
            };
            this.currentPage = 1;
            this.loadResults();
        },

        /**
         * Clear validation results.
         * @since 7.4.58
         */
        clearResults: function() {
            var self = this;

            if (!confirm('Are you sure you want to clear all validation results?')) {
                return;
            }

            $.ajax({
                url: rex_wpfm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'rex_feed_clear_validation_results',
                    feed_id: this.feedId,
                    security: rex_wpfm_ajax.ajax_nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showNotice('success', response.data.message);
                        location.reload();
                    } else {
                        self.showNotice('error', response.data.message || self.getTranslation('failed_to_clear'));
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var errorMessage = jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message 
                        ? jqXHR.responseJSON.data.message 
                        : (errorThrown || self.getTranslation('error_occurred'));
                    self.showNotice('error', errorMessage);
                }
            });
        },

        /**
         * Show export modal.
         * @since 7.4.58
         */
        showExportModal: function() {
            $('#rex-validation-export-modal').css('display', 'flex');
        },

        /**
         * Hide export modal.
         * @since 7.4.58
         */
        hideExportModal: function() {
            $('#rex-validation-export-modal').hide();
        },

        /**
         * Export validation results.
         * @since 7.4.58
         */
        exportResults: function() {
            var self = this;
            var format = $('input[name="export_format"]:checked').val();

            $.ajax({
                url: rex_wpfm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'rex_feed_export_validation_results',
                    feed_id: this.feedId,
                    format: format,
                    filters: this.filters,
                    security: rex_wpfm_ajax.ajax_nonce
                },
                success: function(response) {
                    self.hideExportModal();

                    if (response.success) {
                        self.downloadFile(response.data.content, response.data.filename, response.data.mime);
                    } else {
                        self.showNotice('error', response.data.message || self.getTranslation('export_failed'));
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    self.hideExportModal();
                    var errorMessage = jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message 
                        ? jqXHR.responseJSON.data.message 
                        : (errorThrown || self.getTranslation('error_during_export'));
                    self.showNotice('error', errorMessage);
                }
            });
        },

        /**
         * Download file.
         * @since 7.4.58
         */
        downloadFile: function(content, filename, mime) {
            var blob = new Blob([content], { type: mime });
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = filename;
            link.click();
        },

        /**
         * Show progress indicator.
         * @since 7.4.58
         */
        showProgress: function() {
            $('.rex-feed-validation-progress').show();
            $('.rex-feed-validate-btn, .rex-feed-fix-btn').prop('disabled', true);
        },

        /**
         * Hide progress indicator.
         * @since 7.4.58
         */
        hideProgress: function() {
            $('.rex-feed-validation-progress').hide();
            this.updateFixFeedButtonState();
        },

        /**
         * Show table loading state.
         * @since 7.4.58
         */
        showTableLoading: function() {
            var colSpan = (this.filters && this.filters.severity !== 'error') ? 4 : 5;
            this.toggleFixIssuesColumn(this.filters && this.filters.severity);
            $('#rex-validation-results-body').html(
                '<tr class="rex-validation-loading">' +
                '<td colspan="' + colSpan + '"><span class="spinner is-active"></span> Loading...</td>' +
                '</tr>'
            );
        },

        /**
         * Show table error state.
         * @since 7.4.58
         */
        showTableError: function(message) {
            var colSpan = (this.filters && this.filters.severity !== 'error') ? 4 : 5;
            this.toggleFixIssuesColumn(this.filters && this.filters.severity);
            $('#rex-validation-results-body').html(
                '<tr class="rex-validation-error">' +
                '<td colspan="' + colSpan + '">' + this.escapeHtml(message) + '</td>' +
                '</tr>'
            );
        },

        /**
         * Show notice.
         */
        showNotice: function(type, message) {
            var notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + this.escapeHtml(message) + '</p></div>');
            var validationBox = $('#rex_feed_validation');

            if (validationBox.length) {
                validationBox.before(notice);
            } else {
                this.wrapper.before(notice);
            }
            
            setTimeout(function() {
                notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        /**
         * Get translation string.
         * @since 7.4.58
         */
        getTranslation: function(key) {
            var translations = {
                'no_results': 'No validation issues found matching your criteria.',
                'loading': 'Loading...',
                'error': 'An error occurred.',
                'validation_failed': 'Validation failed.',
                'error_during_validation': 'An error occurred during validation.',
                'failed_to_clear': 'Failed to clear results.',
                'error_occurred': 'An error occurred.',
                'export_failed': 'Export failed.',
                'error_during_export': 'An error occurred during export.',
                'failed_to_load': 'Failed to load results.',
                'error_loading_results': 'An error occurred while loading results.',
                'quick_fix': 'Quick Fix',
                'fix_all': 'Fix All',
                'configure': 'Configure',
                'view_products': 'View products'
            };
            return translations[key] || key;
        },

        /**
         * Escape HTML special characters.
         * @since 7.4.58
         */
        escapeHtml: function(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        /**
         * Escape text used inside generated HTML attributes.
         * @since 7.4.58
         * @param {string} text Attribute value.
         * @return {string} Escaped attribute value.
         */
        escapeAttribute: function(text) {
            return this.escapeHtml(text)
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        /**
         * Truncate text.
         * @since 7.4.58
         */
        truncate: function(text, length) {
            if (!text || text.length <= length) return text;
            return text.substring(0, length) + '...';
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        RexFeedValidation.init();
    });

    // Expose globally for external access (auto-trigger from feed generation)
    window.RexFeedValidation = RexFeedValidation;

})(jQuery);
