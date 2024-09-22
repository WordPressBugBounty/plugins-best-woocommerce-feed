
(function( $ ) {
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
            type : "post",
            dataType : "json",
            url : rex_wpfm_ajax.ajax_url,
            data: {
                action   : 'rex_wpfm_database_update',
                security : rex_wpfm_ajax.ajax_nonce,
            },
            success: function(response) {
                console.log('woohoo!');
                setTimeout(function(){
                    location.reload();
                }, 1000);
            },
            error: function(){
                console.log( 'uh, oh!' );
            }
        });
    }
    $(document).on('click', '#rex-wpfm-update-db', wpfm_update_database);

    $(document).on('click', '.best-woocommerce-feed-deactivate-link', function ( e ) {
        $( '.wd-dr-modal-footer a.dont-bother-me' ).hide();

        var $payload = {
            security: rex_wpfm_ajax.ajax_nonce
        };

        wpAjaxHelperRequest( 'rex-feed-get-appsero-options', $payload )
            .success( function( response ) {
                if (response.success) {
                    $( 'ul.wd-de-reasons' ).empty();
                    $( 'ul.wd-de-reasons' ).append( response.data.html );
                }
            })
            .error( function( response ) {
            });

        if ( !$( '#appsero_new_assistance' ).length && !$( '#appsero_required' ).length ) {
            $( '.wd-dr-modal-body' ).append( '<p id="appsero_new_assistance">Need Support/Assistance? <a href="https://rextheme.com/support/?utm_source=plugin&utm_medium=support_link&utm_campaign=pfm_plugin" target="_blank">Click Here!</a></p>' );
            $( '.wd-dr-modal-body' ).append( '<p id="appsero_required"><span style="color: red">*</span>Please, select one reason and submit.</p>' );
        }
    });


    $( document ).on( 'click', '.best-woocommerce-feed-insights-data-we-collect', function () {
        let desc = $( this ).parents( '.updated' ).find( 'p.description' ).html();
        desc = desc.split( '. ' );
        if ( -1 === desc[ 0 ].indexOf( ', Feed merchant lists, Feed title lists' ) ) {
            desc[0] = desc[0] + ', Feed merchant lists, Feed title lists';
            $(this).parents('.updated').find('p.description').html(desc.join('. '));
        }
    } );

    // Ajax function to update single feed.
    $( document ).on( 'click', '.rex-feed-update-single-feed', function ( e ) {
        e.preventDefault();
        let $this = $( this );
        let feed_id = $this.data( 'feed-id' );

        wpAjaxHelperRequest('rex-feed-update-single-feed', feed_id)
            .success(function (response) {
                $( 'tr#post-' + feed_id + ' td.feed_status' ).text( 'In queue' );
                $this.attr( 'disabled', 'true' );
                $this.css( 'pointer-events', 'none' );
                $this.siblings().attr( 'disabled', true );
                $this.parent().siblings( 'td.view_feed' ).children().attr( 'disabled', true );
                $this.parent().siblings( 'td.view_feed' ).children().css( 'pointer-events', 'none' );
                console.log('Success');
            })
            .error(function (response) {
                console.log('Failed');
            });
    } )

    $( document ).ready( function ( e ) {
        if ( window.location.href.includes('edit.php') ) {
            $( '#rex_feed_new_changes_msg_content' ).hide();
        }
        $( '#rex-feed-support-submenu, #rex-feed-gopro-submenu' ).parent().attr( 'target', '_blank' );
    } );



          // ------window on scroll add class to comparison table header------
          $(window).on('scroll', function() {
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


  

})( jQuery );



