(function ( $ ) {
    'use strict';

    $( document ).on( 'ready', function ( event ) {
        $( ".post-type-product-feed #rex_feed_config_heading table#config-table" ).css( 'background-color', '#f6f5fa' );

        setTimeout( function () {
            const steps = get_tour_steps();
            create_tour( steps );
        }, 2000 );

        $( 'body.post-type-product-feed' ).addClass( 'shepherd-active' );
    } );

    $( document ).on( 'click', '.post-type-product-feed .product_filter_next_btn, a#rex-pr-filter-btn', function ( event ) {
        if ( $( this ).hasClass( 'product_filter_next_btn' ) ) {
            $( 'a#rex-pr-filter-btn' ).trigger( 'click' );
        }
        else if ( $( this ).attr( 'id' ) === 'rex-pr-filter-btn' ) {
            if ( undefined !== event.originalEvent ) {
                setTimeout(function () {
                    $('.product_filter_next_btn').trigger('click');
                }, 1000);
            }
        }
    } );

    $( document ).on( 'click', '.post-type-product-feed .filter_tab_close_next_btn, .post-type-product-feed .filter_tab_close_prev_btn, #rex_feed_filter_modal_close_btn', function ( event ) {
        if ( $( this ).hasClass( 'filter_tab_close_next_btn' ) || $( this ).hasClass( 'filter_tab_close_prev_btn' ) ) {
            $( '#rex_feed_filter_modal_close_btn' ).trigger( 'click' );
        }
        else if ( $( this ).attr( 'id' ) === 'rex_feed_filter_modal_close_btn' ) {
            if ( undefined !== event.originalEvent ) {
                $('.filter_tab_close_next_btn').trigger('click');
            }
        }
    } );

    $( document ).on( 'click', '.post-type-product-feed .feed_settings_next_btn, .post-type-product-feed .feed_settings_prev_btn, a#rex-feed-settings-btn', function ( event ) {
        if ( $( this ).hasClass( 'feed_settings_next_btn' ) ) {
            $( 'a#rex-feed-settings-btn' ).trigger( 'click' );
        }
        else if ( $( this ).hasClass( 'feed_settings_prev_btn' ) ) {
            $( 'a#rex-pr-filter-btn' ).trigger( 'click' );
        }
        else if ( $( this ).attr( 'id' ) === 'rex-feed-settings-btn' ) {
            if ( undefined !== event.originalEvent ) {
                setTimeout(function () {
                    $('.feed_settings_next_btn').trigger('click');
                }, 1000);
            }
        }
    } );

    $( document ).on( 'click', '.settings_tab_close_next_btn, .settings_tab_close_prev_btn', function ( event ) {
        if ( $( this ).hasClass( 'settings_tab_close_next_btn' ) || $( this ).hasClass( 'settings_tab_close_prev_btn' ) ) {
            $( '#rex_feed_settings_modal_close_btn' ).trigger( 'click' );
        }
        else if ( $( this ).attr( 'id' ) === 'rex_feed_settings_modal_close_btn' ) {
            if ( undefined !== event.originalEvent ) {
                $( '.settings_tab_close_next_btn' ).trigger('click');
            }
        }
    } );

    $( document ).on( 'click', '.tour_end_feed_publish_prev_btn', function ( event ) {
        $( '#rex-feed-settings-btn' ).trigger( 'click' );
    } );

    $( document ).on( 'change', 'select#rex_feed_merchant', function ( event ) {
        let merchant = $( 'select#rex_feed_merchant' ).find( 'option:selected' ).val();

        if ( '-1' !== merchant ) {
            $( '.merchant_name_type_next_btn' ).prop( "disabled", false );
        }
        else {
            $( '.merchant_name_type_next_btn' ).prop( "disabled", true );
        }
    } );

    $( document ).on( 'click', '.tour_end_feed_publish_next_btn, .shepherd-header .shepherd-cancel-icon, #publish, #rex-bottom-publish-btn', function () {
        let url = window.location.href;

        if ( url.includes( 'tour_guide=1' ) ) {
            window.history.pushState({}, '', url.replace( '&tour_guide=1', '' ));
        }

        $( 'body.post-type-product-feed' ).removeClass( 'shepherd-active' );

        if ( $( this ).attr( 'id' ) === 'publish' || $( this ).attr( 'id' ) === 'rex-bottom-publish-btn' ) {
            $( '.shepherd-cancel-icon' ).trigger( 'click' );
        }
    } );

    /**
     * @desc Generate Tour Guides.
     * @param tour_steps
     */
    function create_tour( tour_steps ) {

        const tour = new Shepherd.Tour({
            defaultStepOptions: {
                cancelIcon: {
                    enabled: true
                },
                classes: 'rex-feed-shepherd-container',
                scrollTo: { behavior: 'smooth', block: 'center' }
            }
        });
        let current_step = 1;

        $.each( tour_steps, function( _index, item ) {
            let buttons = [];
            if ( item[ 'prev_button' ] !== '' ) {
                let prev_button = {
                    action() {
                        trigger_prev_button_actions( this, _index );
                    },
                    classes: 'shepherd-button-secondary ' + _index + '_prev_btn',
                    text: item[ 'prev_button' ]
                }
                buttons.push( prev_button );
            }
            if ( item[ 'next_button' ] !== '' ) {
                let next_button = {
                    action() {
                        trigger_next_button_actions( this, _index );
                    },
                    classes: _index + '_next_btn',
                    text: item[ 'next_button' ]
                }
                buttons.push( next_button );
            }

            if ( 'length' !== _index ) {
                tour.addStep( {
                    title: item['title'] + ' [ ' + current_step++ + '/' + Object.keys( tour_steps ).length + ' ]',
                    text: item['desc'],
                    attachTo: {
                        element: item['attach_element'],
                        on: item['attach_element_on']
                    },
                    buttons: buttons,
                    id: _index
                });
            }
        });

        tour.start();
    }

    /**
     * @desc Contains all required tour steps information.
     *
     * and returns as an object
     * @returns object
     */
    function get_tour_steps() {
        let next_button = window?.rexOnboardingJs?.next_button?.title ?? 'Next';
        let prev_button = window?.rexOnboardingJs?.prev_button?.title ?? 'Previous';
        return {
            feed_title: {
                title: window?.rexOnboardingJs?.feed_title?.title ?? 'Give A Name To This Feed',
                desc: window?.rexOnboardingJs?.feed_title?.desc ?? 'You may give any name. It\'s just to help you save settings for this feed generation and help you distinguish between other feeds you generate in the future.',
                attach_element: '.post-type-product-feed #post-body-content',
                attach_element_on: 'bottom',
                next_button: next_button,
                prev_button: '',
            },
            merchant_name_type: {
                title: window?.rexOnboardingJs?.merchant_name_type?.title ?? 'Select Merchant And Feed Type',
                desc: window?.rexOnboardingJs?.merchant_name_type?.desc ?? 'Here, you can change the merchant/marketplace and choose the file type when the product feed is generated.',
                attach_element: '.post-type-product-feed #rex_feed_conf',
                attach_element_on: 'top',
                next_button: next_button,
                prev_button: prev_button,
            },
            config_table: {
                title: window?.rexOnboardingJs?.config_table?.title ?? 'Feed Attributes & Product Data Mapping',
                desc: window?.rexOnboardingJs?.config_table?.desc ?? 'These are the list of attributes that you are supposed to include for your products in the product feed. We will be mapping your store product data as the values of the attributes in this section.<br><br>However, most of these are already mapped, and you do not need to make any changes to them.',
                attach_element: ".post-type-product-feed #rex_feed_config_heading table#config-table",
                attach_element_on: 'top',
                next_button: next_button,
                prev_button: prev_button,
            },
            feed_publish: {
                title: window?.rexOnboardingJs?.feed_publish?.title ?? 'Publish To Generate The Product Feed',
                desc: window?.rexOnboardingJs?.feed_publish?.desc ?? 'Once you have mapped the attribute values, you can click on Publish and the feed will be generated.<br><br><em><b>**This tour will end if you click on the Publish button and the feed will start generating.</b><br><br>- You can view the generated feed once the feed generation is completed.<br>- Click on the Next to skip feed generation for now and  to learn more options to configure the feed.</em>',
                attach_element: '.post-type-product-feed #rex-bottom-publish-btn',
                attach_element_on: 'left',
                next_button: next_button,
                prev_button: prev_button,
            },
            additional_feed_attr: {
                title: window?.rexOnboardingJs?.additional_feed_attr?.title ?? 'Add More Attributes To Your Feed',
                desc: window?.rexOnboardingJs?.additional_feed_attr?.desc ?? 'You can also include more attributes for your products using these buttons.<br><br>The "Add New Attribute" will let you choose from other available attributes for your selected merchant and then you can map the value with a product data.<br><br>The "Add New Custom Attribute" will let you name an attribute title yourself and then map the value with a product data.',
                attach_element: '.post-type-product-feed .rex-feed-attr-btn-area',
                attach_element_on: 'top',
                next_button: next_button,
                prev_button: prev_button,
            },
            product_filter: {
                title: window?.rexOnboardingJs?.product_filter?.title ?? 'Use Advanced Filters',
                desc: window?.rexOnboardingJs?.product_filter?.desc ?? 'Click on this Product Filter button to: <br><br>- Use All Featured Products Filter<br>- Category Filter<br>- Tag Filter<br>- Custom Filter<br>- Product Filter (Pro)<br>- Product Rule (Pro)',
                attach_element: '.post-type-product-feed #rex-pr-filter-btn',
                attach_element_on: 'bottom',
                next_button: next_button,
                prev_button: prev_button,
            },
            filter_tab_close: {
                title: window?.rexOnboardingJs?.filter_tab_close?.title ?? 'Product Filter Close Button',
                desc: window?.rexOnboardingJs?.filter_tab_close?.desc ?? 'Once you make any changes, click on the Close button to get back to the Attributes section.',
                attach_element: '.post-type-product-feed #rex_feed_filter_modal_close_btn',
                attach_element_on: 'bottom',
                next_button: next_button,
                prev_button: prev_button,
            },
            feed_settings: {
                title: window?.rexOnboardingJs?.feed_settings?.title ?? 'Feed Settings Option',
                desc: window?.rexOnboardingJs?.feed_settings?.desc ?? 'Click on the Feed Settings button to: <br><br>- Schedule Feed Update<br>- Include Out of Stock Products<br>- Include Product with No Price<br>- Include/ Exclude Product Type<br>- Skip Products/ Attributes With Empty Values<br>- Track Campaign With UTM Parameters',
                attach_element: '.post-type-product-feed #rex-feed-settings-btn',
                attach_element_on: 'bottom',
                next_button: next_button,
                prev_button: prev_button,
            },
            settings_tab_close: {
                title: window?.rexOnboardingJs?.settings_tab_close?.title ?? 'Close The Settings Drawer',
                desc: window?.rexOnboardingJs?.settings_tab_close?.desc ?? 'Once you make any changes, click on the Close button to get back to the Attributes section.',
                attach_element: '.post-type-product-feed #rex_feed_settings_modal_close_btn',
                attach_element_on: 'bottom',
                next_button: next_button,
                prev_button: prev_button,
            },
            tour_end_feed_publish: {
                title: window?.rexOnboardingJs?.tour_end_feed_publish?.title ?? 'Publish Feed',
                desc: window?.rexOnboardingJs?.tour_end_feed_publish?.desc ?? 'Click on the publish button to start generating the feed.<br><br>- Once you click on the Publish button, this tour will end.<br>- You will see a feed loading bar once the feed generation starts.<br>- Once the feed generation is completed, you can view or download the generated feed.<br>- Once the feed is generated, you can click on the View/ Download button to view the feed or to download the generated feed',
                attach_element: '.post-type-product-feed #publish',
                attach_element_on: 'bottom',
                next_button: window?.rexOnboardingJs?.tour_end_feed_publish?.next_button ?? 'Finish Tour',
                prev_button: prev_button,
            },
        };
    }

    /**
     * @desc Next step button custom actions.
     *
     * @param obj
     * @param index
     * @returns {void|*}
     */
    function trigger_next_button_actions( obj, index ) {
        rex_feed_disable_next();
        let tour_ids = [
            'product_filter',
            'feed_settings',
        ]
        if ( $.inArray( index, tour_ids ) !== -1 ) {
            return trigger_delayed_steps( obj );
        }
        else {
            return obj.next();
        }
    }

    /**
     * @desc Previous step button custom actions.
     *
     * @param obj
     * @param index
     * @returns {void|*}
     */
    function trigger_prev_button_actions( obj, index ) {
        let tour_ids = [
            'feed_settings',
            'filter_option',
            'feed_settings_contents',
            'tour_end_feed_publish',
        ];

        if ( $.inArray( index, tour_ids ) !== -1 ) {
            return trigger_delayed_steps( obj, 'back' );
        }
        else {
            return obj.back();
        }
    }

    /**
     * @desc Delaying every next/prev step actions by 1 seconds
     * since it takes few time to load the modal contents.
     *
     * @param obj
     * @param direction
     */
    function trigger_delayed_steps( obj, direction = 'next' ) {
        if ( 'next' === direction ) {
            setTimeout( function () {
                return obj.next();
            }, 1000 );
        }
        else {
            setTimeout( function () {
                return obj.back();
            }, 1000 );
        }
    }

    /**
     * @desc Retrieves select fields name attributes
     * if no value is selected.
     *
     * @returns {*[]}
     */
    function get_blank_meta_fields() {
        let meta_field_val = '';
        let meta_names = [];
        let row_id = 0;

        while ( meta_field_val !== undefined ) {
            meta_field_val = $( 'select[name="fc['+ row_id +'][meta_key]"]' ).val();
            if ( meta_field_val === '' ) {
                meta_names.push( ".post-type-product-feed select[name='fc[" + row_id + "][meta_key]']" );
            }
            row_id++;
        }
        return meta_names;
    }

    /**
     * @desc Disable next button on next button click.
     * @param event
     */
    function rex_feed_disable_next( event ) {
        let merchant = $( 'select#rex_feed_merchant' ).find( 'option:selected' ).val();

        if ( '-1' === merchant ) {
            setTimeout( function () {
                $( '.merchant_name_type_next_btn' ).prop( "disabled", true );
            }, 500 )
        }
    }
} )( jQuery );