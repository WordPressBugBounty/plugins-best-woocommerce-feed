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
            this.currentPage = 1;
            this.perPage = 50;
            this.filters = {};
            this.isLoading = false;
            this.fullAttributeSummary = []; // Store full attribute summary

            this.bindEvents();
            this.loadInitialResults();
            this.checkAutoTriggerValidation();
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

            // Validate button
            $(document).on('click', '.rex-feed-validate-btn', function(e) {
                e.preventDefault();
                self.validateFeed();
            });

            // Export button
            $(document).on('click', '.rex-feed-export-validation-btn', function(e) {
                e.preventDefault();
                self.showExportModal();
            });

            // Clear button
            $(document).on('click', '.rex-feed-clear-validation-btn', function(e) {
                e.preventDefault();
                self.clearResults();
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

            // Per page change
            $(document).on('change', '#rex-validation-per-page', function() {
                self.perPage = parseInt($(this).val());
                self.currentPage = 1;
                self.loadResults();
            });

            // Summary card click to filter
            $(document).on('click', '.rex-feed-validation-card--clickable', function() {
                var severity = $(this).data('filter-severity');
                $('#rex-validation-severity-filter').val(severity);
                
                // Update active state
                $('.rex-feed-validation-card--clickable').removeClass('rex-feed-validation-card--active');
                $(this).addClass('rex-feed-validation-card--active');
                
                self.applyFilters();
            });

            // Export modal
            $(document).on('click', '.rex-modal__close, .rex-modal__cancel', function() {
                self.hideExportModal();
            });

            $(document).on('click', '.rex-modal__export', function() {
                self.exportResults();
            });
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

            if (this.isLoading) {
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
                        
                        // Only reload if this is a manual validation (not auto-triggered)
                        if (!isAutoValidation) {
                            location.reload();
                        } else {
                            // For auto-validation, just load the results without reloading
                            self.currentPage = 1;
                            self.loadResults();
                        }
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
                        var totalProducts = response.data.total_products || 0;
                        var isTruncated = response.data.is_truncated || false;
                        var totalIssues = response.data.total_issues || 0;
                        
                        // Get display-level truncation info
                        var isDisplayTruncated = response.data.is_display_truncated || false;
                        var totalBeforeLimit = response.data.total_before_limit || 0;
                        var displayLimit = response.data.display_limit || 500;
                        
                        self.updateSummaryCards(
                            summaryToUse, 
                            response.data.results, 
                            totalProducts, 
                            response.data.has_filters, 
                            isTruncated, 
                            totalIssues,
                            isDisplayTruncated,
                            totalBeforeLimit,
                            displayLimit
                        );
                        
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
        updateSummaryCards: function(summary, results, totalProducts, hasFilters, isTruncated, totalIssues, isDisplayTruncated, totalBeforeLimit, displayLimit) {
            var errors = summary ? (summary.total_errors || 0) : 0;
            var warnings = summary ? (summary.total_warnings || 0) : 0;
            var info = summary ? (summary.total_info || 0) : 0;
            var total = errors + warnings + info;

            // Update error count
            $('.rex-feed-validation-card--error .card-number').text(errors);
            
            // Update warning count
            $('.rex-feed-validation-card--warning .card-number').text(warnings);
            
            // Update info/suggestions count
            $('.rex-feed-validation-card--info .card-number').text(info);
            
            // Update total count
            $('.rex-feed-validation-card--total .card-number').text(total);

            // Update active state based on current filter
            var currentSeverityFilter = $('#rex-validation-severity-filter').val();
            $('.rex-feed-validation-card--clickable').removeClass('rex-feed-validation-card--active');
            
            if (currentSeverityFilter === 'error') {
                $('.rex-feed-validation-card--error').addClass('rex-feed-validation-card--active');
            } else if (currentSeverityFilter === 'warning') {
                $('.rex-feed-validation-card--warning').addClass('rex-feed-validation-card--active');
            } else if (currentSeverityFilter === 'info') {
                $('.rex-feed-validation-card--info').addClass('rex-feed-validation-card--active');
            } else {
                $('.rex-feed-validation-card--total').addClass('rex-feed-validation-card--active');
            }

            // Remove existing indicators
            $('.rex-feed-validation-filter-indicator').remove();
            $('.rex-feed-validation-truncation-warning').remove();
            $('.rex-feed-validation-display-limit-banner').remove();

            // Priority 1: Show display-level truncation banner (when filtered or unfiltered results exceed 500)
            if (isDisplayTruncated && totalBeforeLimit) {
                var bannerMessage = '';
                
                if (hasFilters) {
                    // Filtered results are truncated
                    bannerMessage = '<span class="dashicons dashicons-info"></span> ' +
                        '<strong>Display Limit:</strong> Showing ' + displayLimit + ' of ' + totalBeforeLimit.toLocaleString() + 
                        ' matching issues. ' + (totalBeforeLimit - displayLimit).toLocaleString() + ' matching issues are not displayed due to the display limit.';
                } else {
                    // Unfiltered results are truncated
                    bannerMessage = '<span class="dashicons dashicons-info"></span> ' +
                        '<strong>Display Limit:</strong> Showing ' + displayLimit + ' of ' + totalBeforeLimit.toLocaleString() + 
                        ' total issues. ' + (totalBeforeLimit - displayLimit).toLocaleString() + ' issues are not displayed due to the display limit.';
                }
                
                $('.rex-feed-validation-summary-cards').after(
                    '<div class="rex-feed-validation-display-limit-banner" style="padding: 12px 15px; background: #e7f3ff; border-left: 4px solid #0073aa; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">' +
                    '<span class="dashicons dashicons-info" style="color: #0073aa; flex-shrink: 0;"></span> ' +
                    '<span style="color: #0073aa;">' + bannerMessage + '</span>' +
                    '</div>'
                );
            }

            // Priority 2: Show filter indicator if filters are active
            if (hasFilters) {
                var filterMessage = '<span class="dashicons dashicons-filter"></span> ' +
                    '<small><em>Showing filtered results - Click a card to change filter or "Total Issues" to show all</em></small>';
                
                // Add storage truncation warning if results are truncated AND filters are active
                if (isTruncated && totalIssues && !isDisplayTruncated) {
                    filterMessage += '<br><small style="color: #d63638;"><strong>Note:</strong> Filtering is based on ' + totalIssues.toLocaleString() + ' total issues stored. Actual counts may be higher.</small>';
                }
                
                $('.rex-feed-validation-summary-cards').after(
                    '<div class="rex-feed-validation-filter-indicator">' + filterMessage + '</div>'
                );
            } else if (isTruncated && totalIssues && !isDisplayTruncated) {
                // Priority 3: Show storage truncation warning (only if display is not truncated)
                $('.rex-feed-validation-summary-cards').after(
                    '<div class="rex-feed-validation-truncation-warning" style="padding: 10px 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; margin-bottom: 20px;">' +
                    '<span class="dashicons dashicons-info" style="color: #856404;"></span> ' +
                    '<small style="color: #856404;"><strong>Info:</strong> For performance, only the first 500 issues are stored and displayed. Total found: ' + totalIssues.toLocaleString() + '</small>' +
                    '</div>'
                );
            }
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

            if (!data.items || data.items.length === 0) {
                tbody.html(
                    '<tr class="rex-validation-no-results">' +
                    '<td colspan="5">' + this.getTranslation('no_results') + '</td>' +
                    '</tr>'
                );
                return;
            }

            var rows = '';
            data.items.forEach(function(item) {
                // For variations, use parent_id for the edit link, otherwise use product_id
                var editPostId = item.is_variation && item.parent_id ? item.parent_id : item.product_id;
                
                // Check if title already contains the product ID (for variations)
                var titleHasId = item.product_title && item.product_title.indexOf('#' + item.product_id) !== -1;
                var productIdDisplay = titleHasId ? '' : '<br><small class="product-id">#' + item.product_id + '</small>';
                
                rows += '<tr class="rex-validation-row rex-validation-row--' + item.severity + '">' +
                    '<td class="column-severity">' +
                    '<span class="rex-severity-badge rex-severity-badge--' + item.severity + '">' +
                    item.severity +
                    '</span>' +
                    '</td>' +
                    '<td class="column-product">' +
                    '<a href="' + rex_wpfm_ajax.admin_url + 'post.php?post=' + editPostId + '&action=edit" target="_blank">' +
                    self.escapeHtml(item.product_title || 'Product #' + item.product_id) +
                    '</a>' +
                    productIdDisplay +
                    '</td>' +
                    '<td class="column-attribute"><code>' + self.escapeHtml(item.attribute) + '</code></td>' +
                    // '<td class="column-rule"><code>' + self.escapeHtml(item.rule) + '</code></td>' +
                    '<td class="column-message">' + self.escapeHtml(item.message) + '</td>' +
                    '<td class="column-value">' +
                    (item.raw_value ? '<code title="' + self.escapeHtml(item.raw_value) + '">' + self.truncate(self.escapeHtml(item.raw_value), 30) + '</code>' : '<em>empty</em>') +
                    '</td>' +
                    '</tr>';
            });

            tbody.html(rows);
        },

        /**
         * Update pagination controls.
         * @since 7.4.58
         */
        updatePagination: function(data) {
            var totalPages = data.total_pages || 1;
            var total = data.total || 0;
            var start = ((this.currentPage - 1) * this.perPage) + 1;
            var end = Math.min(this.currentPage * this.perPage, total);

            // Update showing info
            $('.rex-feed-validation-pagination__info .showing-info').text(
                'Showing ' + start + '-' + end + ' of ' + total + ' issues'
            );

            // Update page info
            $('.page-info').text('Page ' + this.currentPage + ' of ' + totalPages);

            // Update button states
            $('.rex-validation-prev-page').prop('disabled', this.currentPage <= 1);
            $('.rex-validation-next-page').prop('disabled', this.currentPage >= totalPages);
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
            $('#rex-validation-export-modal').show();
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
            $('.rex-feed-validate-btn').prop('disabled', true);
        },

        /**
         * Hide progress indicator.
         * @since 7.4.58
         */
        hideProgress: function() {
            $('.rex-feed-validation-progress').hide();
            $('.rex-feed-validate-btn').prop('disabled', false);
        },

        /**
         * Show table loading state.
         * @since 7.4.58
         */
        showTableLoading: function() {
            $('#rex-validation-results-body').html(
                '<tr class="rex-validation-loading">' +
                '<td colspan="6"><span class="spinner is-active" style="float: none;"></span> Loading...</td>' +
                '</tr>'
            );
        },

        /**
         * Show table error state.
         * @since 7.4.58
         */
        showTableError: function(message) {
            $('#rex-validation-results-body').html(
                '<tr class="rex-validation-error">' +
                '<td colspan="6">' + this.escapeHtml(message) + '</td>' +
                '</tr>'
            );
        },

        /**
         * Show notice.
         */
        showNotice: function(type, message) {
            var notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + this.escapeHtml(message) + '</p></div>');
            this.wrapper.prepend(notice);
            
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
                'error_loading_results': 'An error occurred while loading results.'
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