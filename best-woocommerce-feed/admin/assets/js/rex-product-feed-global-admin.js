(function ($) {
    'use strict';

    /**
     * All the code for your admin-facing javascript source
     * should reside in this file.
     *
     * note: it has been assumed you will write jquery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * this enables you to define handlers, for when the dom is ready:
     *
     * $(function() {
     *
     * });
     *
     * when the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * ideally, it is not considered best practise to attach more than a
     * single dom-ready or window-load handler for a particular page.
     * although scripts in the wordpress core, plugins and themes may be
     * practising this, we should strive to set a better example in our own work.
     */


    /*
     ** database update
     */

    function wpfm_update_database(event) {
        event.preventDefault();
        var payload = {};
        var $this = $(this);

        $('.wpfm-db-update-loader').fadeIn();


        $.ajax({
            type: "post",
            dataType: "json",
            url: rex_wpfm_ajax.ajax_url,
            data: {
                action: 'rex_wpfm_database_update',
                security: rex_wpfm_ajax.ajax_nonce,
            },
            success: function (response) {
                console.log('woohoo!');
                setTimeout(function () {
                    location.reload();
                }, 1000);
            },
            error: function () {
                console.log('uh, oh!');
            }
        });
    }

    $(document).on('click', '#rex-wpfm-update-db', wpfm_update_database);

    $(document).on('click', '.best-woocommerce-feed-deactivate-link', function (e) {
        $('.wd-dr-modal-footer a.dont-bother-me').hide();

        var $payload = {
            security: rex_wpfm_ajax.ajax_nonce
        };

        wpAjaxHelperRequest('rex-feed-get-appsero-options', $payload)
            .success(function (response) {
                if (response.success) {
                    $('ul.wd-de-reasons').empty();
                    $('ul.wd-de-reasons').append(response.data.html);
                }
            })
            .error(function (response) {
            });

        if (!$('#appsero_new_assistance').length && !$('#appsero_required').length) {
            $('.wd-dr-modal-body').append('<p id="appsero_new_assistance">Need Support/Assistance? <a href="https://rextheme.com/support/?utm_source=plugin&utm_medium=support_link&utm_campaign=pfm_plugin" target="_blank">Click Here!</a></p>');
            $('.wd-dr-modal-body').append('<p id="appsero_required"><span style="color: red">*</span>Please, select one reason and submit.</p>');
        }
    });


    $(document).on('click', '.best-woocommerce-feed-insights-data-we-collect', function () {
        let desc = $(this).parents('.updated').find('p.description').html();
        desc = desc.split('. ');
        if (-1 === desc[0].indexOf(', Feed merchant lists, Feed title lists')) {
            desc[0] = desc[0] + ', Feed merchant lists, Feed title lists';
            $(this).parents('.updated').find('p.description').html(desc.join('. '));
        }
    });

    // Ajax function to update single feed.
    $(document).on('click', '.rex-feed-update-single-feed', function (e) {
        e.preventDefault();
        let $this = $(this);
        let feed_id = $this.data('feed-id');

        wpAjaxHelperRequest('rex-feed-update-single-feed', feed_id)
            .success(function (response) {
                $('tr#post-' + feed_id + ' td.feed_status').text('In queue');
                $this.attr('disabled', 'true');
                $this.css('pointer-events', 'none');
                $this.siblings().attr('disabled', true);
                $this.parent().siblings('td.view_feed').children().attr('disabled', true);
                $this.parent().siblings('td.view_feed').children().css('pointer-events', 'none');
                console.log('Success');
            })
            .error(function (response) {
                console.log('Failed');
            });
    })

    $(document).ready(function (e) {
        if (window.location.href.includes('edit.php')) {
            $('#rex_feed_new_changes_msg_content').hide();
        }

        $('#rex-feed-support-submenu, #rex-feed-gopro-submenu').parent().attr('target', '_blank');

        if ( 0 < $( '.rex-feed-gmc-diagnostics-report-area' )?.length ) {
            document.title = 'Google Merchant Product Diagnostics';
            const feed_id = url?.searchParams?.get( 'feed_id' );
            const payload = { feed_id: feed_id };
            renderGmcReportData( payload, true );
        }
    });

    // ------window on scroll add class to comparison table header------
    $(window).on('scroll', function () {
        var $header = $('.wpfm-compare__header');

        if ($header.length > 0) {
            var headerOffset = $header.offset().top - $(window).scrollTop();

            if (headerOffset < 28) {
                $header.addClass('sticked');
            } else {
                $header.removeClass('sticked');
            }
        }
    });

    const url = new URL(window?.location?.href);
    $( document ).on( 'change', '#rexfeed_gmc_report_max_result', (event) => {
        const maxResult = $( event?.target ).find( 'option:selected' ).val();

        const feed_id = url?.searchParams?.get( 'feed_id' );
        const payload = { maxResult: maxResult, feed_id: feed_id };
        renderGmcReportData(payload);
    } )

    $( document ).on( 'click', '#rexfeed_gmc_report_prev_page', () => {
        let prevPageToken = $( 'input[name=rexfeed_gmc_report_prev_page_token]' ).val();
        const pageTokens = JSON.parse(window.localStorage.getItem('rexfeed_gmc_report_page_tokens'));
        prevPageToken = pageTokens[prevPageToken];
        const maxResult = $( '#rexfeed_gmc_report_max_result' ).find( 'option:selected' ).val();
        const feed_id = url?.searchParams?.get( 'feed_id' );
        const payload = { pageToken: prevPageToken, maxResult: maxResult, feed_id: feed_id };
        renderGmcReportData( payload, false, true );
    } )

    $( document ).on( 'click', '#rexfeed_gmc_report_next_page', () => {
        const nextPageToken = $( 'input[name=rexfeed_gmc_report_next_page_token]' ).val();
        const maxResult = $( '#rexfeed_gmc_report_max_result' ).find( 'option:selected' ).val();
        const feed_id = url?.searchParams?.get( 'feed_id' );
        const payload = { pageToken: nextPageToken, maxResult: maxResult, feed_id: feed_id };
        renderGmcReportData( payload );
    } )

    const renderGmcReportData = ( payload, firstLoad = false, loadPrev = false ) => {
        const $loadingSpinner = $( '.rex-loading-spinner' );
        $loadingSpinner.css( 'left', '-20px' );
        $loadingSpinner.css( 'display', 'flex' );
        wpAjaxHelperRequest( 'rexfeed-fetch-gmc-report', payload )
            .success( ( response ) => {
                if ( response?.success ) {
                    const prevPageToken = response?.data?.report?.prev_page_token;
                    const nextPageToken = response?.data?.report?.next_page_token;

                    $( 'input[name=rexfeed_gmc_report_prev_page_token]' ).val( prevPageToken );
                    $( 'input[name=rexfeed_gmc_report_next_page_token]' ).val( nextPageToken );

                    const diagnosticsReportArea = $( '.rex-feed-gmc-diagnostics-report-list-area' );
                    const rowData = $( diagnosticsReportArea ).children( '.rex-flex-table-body' );
                    if ( rowData?.length ) {
                        rowData.remove();
                    }
                    $( diagnosticsReportArea ).append(response?.data?.markups);

                    storePageTokens( prevPageToken, nextPageToken, firstLoad, loadPrev );

                    const prevPagePaginationBtn = $( '#rexfeed_gmc_report_prev_page' );
                    const nextPagePaginationBtn = $( '#rexfeed_gmc_report_next_page' );
                    if ( !prevPageToken ) {
                        $( prevPagePaginationBtn ).addClass( 'disabled' );
                        $( prevPagePaginationBtn ).parent().addClass( 'disabled' );
                    }
                    else {
                        $( prevPagePaginationBtn ).removeClass( 'disabled' );
                        $( prevPagePaginationBtn ).parent().removeClass( 'disabled' );
                    }

                    if ( !nextPageToken ) {
                        $( nextPagePaginationBtn ).addClass( 'disabled' );
                        $( nextPagePaginationBtn ).parent().addClass( 'disabled' );
                    }
                    else {
                        $( nextPagePaginationBtn ).removeClass( 'disabled' );
                        $( nextPagePaginationBtn ).parent().removeClass( 'disabled' );
                    }
                }
                else {
                    console.log( response );
                }
            } )
            .error( ( response ) => {
                console.log( response?.status + ' ' + response?.statusText );
            } )
            .then( ()=> {
                $loadingSpinner.fadeOut();
            });
    }

    const storePageTokens = (prevToken, nextToken, firstLoad = false, loadPrev = false) => {
        if ( !loadPrev ) {
            let pageTokens = {};
            if (firstLoad) {
                pageTokens[nextToken] = '';
                window.localStorage.setItem('rexfeed_gmc_report_page_tokens', JSON.stringify(pageTokens));
            } else {
                pageTokens = JSON.parse(window.localStorage.getItem('rexfeed_gmc_report_page_tokens'));
                pageTokens[nextToken] = prevToken;
                window.localStorage.setItem('rexfeed_gmc_report_page_tokens', JSON.stringify(pageTokens));
            }
        }
    }

    $(document).on( 'click', '.rex-feed-gmc-diagnostics-report-list-area__view-detail', (event) => {
        $( event?.target ).parent().parent().parent().siblings( '.rex-feed-gmc-diagnostics-report-popup' ).fadeIn();
    } )

    $(document).on( 'click', '.rex-feed-gmc-diagnostics-report-popup__close-btn', () => {
        $(".rex-feed-gmc-diagnostics-report-popup").fadeOut();
    } )
})(jQuery);



