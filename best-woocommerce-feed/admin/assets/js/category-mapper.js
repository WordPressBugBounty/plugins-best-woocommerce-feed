
jQuery(document).ready(function($){

    /**
     * Searchable category select ("combo") for every mapping row.
     *
     * Each row has a hidden `.category-suggest` input (the saved value, same
     * name and format as before) and a `.wpfm-catmap-combo` button that shows
     * it. One shared popover holds the search box and the result list, so 200+
     * rows never each carry 5,580 options. Free text stays possible through the
     * "Use …" option.
     *
     * @since 7.14.4
     */
    var comboStrings   = window.rex_wpfm_cat_map_translate_strings || {};
    var comboItems     = null;  // [ { value, lower, leaf, leafLower, path, depth } ]
    var comboLimit     = 60;
    var comboState     = { $input: null, $button: null, options: [], active: -1 };
    var comboUid       = 0;
    var $combo         = $(
        '<div class="wpfm-catmap-popover" hidden>' +
            '<div class="wpfm-catmap-popover__search">' +
                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>' +
                '<input type="text" class="browser-default" role="combobox" aria-autocomplete="list" aria-expanded="true" autocomplete="off" spellcheck="false">' +
            '</div>' +
            '<ul class="wpfm-catmap-popover__list" role="listbox"></ul>' +
            '<p class="wpfm-catmap-popover__note" aria-live="polite"></p>' +
        '</div>'
    );
    var $comboSearch   = $combo.find( 'input' );
    var $comboList     = $combo.find( '.wpfm-catmap-popover__list' );
    var $comboNote     = $combo.find( '.wpfm-catmap-popover__note' );

    $comboList.attr( 'id', 'wpfm-catmap-popover-list' );
    $comboSearch
        .attr( 'aria-controls', 'wpfm-catmap-popover-list' )
        .attr( 'placeholder', comboStrings.combo_search || 'Search categories' )
        .attr( 'aria-label', comboStrings.combo_search || 'Search categories' );
    ( $( '#wpfm-catmap' ).length ? $( '#wpfm-catmap' ) : $( 'body' ) ).append( $combo );

    /**
     * Split a stored value into leaf and parent path for display.
     *
     * @since 7.14.4
     *
     * @param {string} value `<id> - A > B > C` or custom text.
     * @return {{leaf: string, path: string, parts: string[]}}
     */
    function comboParts( value ) {
        var path  = $.trim( String( value || '' ) ).replace( /^\s*\d+\s*-\s*/, '' );
        var parts = $.map( path.split( '>' ), function( part ) { return $.trim( part ); } );
        var leaf  = parts.pop() || '';
        return { leaf: leaf, path: parts.join( ' › ' ), parts: parts };
    }

    /**
     * Show a row's current value on its combo button.
     *
     * @since 7.14.4
     *
     * @param {jQuery} $input Hidden `.category-suggest` input.
     */
    function comboRender( $input ) {
        var value   = $.trim( $input.val() );
        var parts   = comboParts( value );
        var $button = $input.siblings( '.wpfm-catmap-combo' );

        $button.attr( 'title', value );
        $button.find( '.wpfm-catmap-combo__leaf' ).text( parts.leaf );
        $button.find( '.wpfm-catmap-combo__path' ).text( parts.path );
        $button.find( '.wpfm-catmap-combo__value' ).prop( 'hidden', '' === value );
        $button.find( '.wpfm-catmap-combo__placeholder' ).prop( 'hidden', '' !== value );
    }

    /**
     * Build the search index from the Google category lines.
     *
     * @since 7.14.4
     *
     * @param {string[]} lines Raw lines (`<id> - A > B`, may end in a newline).
     */
    function comboIndex( lines ) {
        comboItems = [];
        $.each( lines || [], function( i, line ) {
            var value = $.trim( line );
            if ( ! value ) {
                return;
            }
            var parts = comboParts( value );
            comboItems.push( {
                value: value,
                lower: ( parts.parts.concat( [ parts.leaf ] ) ).join( ' > ' ).toLowerCase(),
                leaf: parts.leaf,
                leafLower: parts.leaf.toLowerCase(),
                path: parts.path,
                depth: parts.parts.length + 1
            } );
        });
    }

    /**
     * Ranked matches for a query: every word must appear in the path.
     *
     * Leaf starting with the query first, then leaf containing every word,
     * then path matches; shallower and shorter nodes first within a group.
     * An empty query lists the top-level categories.
     *
     * @since 7.14.4
     *
     * @param {string} query Search text.
     * @return {{items: Array, total: number}}
     */
    function comboSearch( query ) {
        var q      = $.trim( query ).toLowerCase();
        var words  = q ? q.split( /\s+/ ) : [];
        var found  = [];

        $.each( comboItems || [], function( i, item ) {
            if ( ! words.length ) {
                if ( 1 === item.depth ) {
                    found.push( { item: item, rank: 0 } );
                }
                return;
            }
            for ( var w = 0; w < words.length; w++ ) {
                if ( item.lower.indexOf( words[ w ] ) === -1 ) {
                    return;
                }
            }
            var inLeaf = true;
            for ( var l = 0; l < words.length; l++ ) {
                if ( item.leafLower.indexOf( words[ l ] ) === -1 ) {
                    inLeaf = false;
                    break;
                }
            }
            var rank = 0 === item.leafLower.indexOf( q ) ? 0 : ( inLeaf ? 1 : 2 );
            found.push( { item: item, rank: rank } );
        });

        found.sort( function( a, b ) {
            if ( ! words.length ) {
                return a.item.leaf.localeCompare( b.item.leaf );
            }
            return ( a.rank - b.rank ) || ( a.item.depth - b.item.depth ) || ( a.item.lower.length - b.item.lower.length );
        });

        return {
            items: $.map( found.slice( 0, comboLimit ), function( f ) { return f.item; } ),
            total: found.length
        };
    }

    /**
     * Append text to an element with every query word wrapped in <mark>.
     *
     * Builds text nodes only, never HTML from the data.
     *
     * @since 7.14.4
     *
     * @param {jQuery}   $el   Target element.
     * @param {string}   text  Text.
     * @param {string[]} words Lowercase query words.
     */
    function comboHighlight( $el, text, words ) {
        var lower  = text.toLowerCase();
        var ranges = [];

        $.each( words, function( i, word ) {
            var from = 0;
            var at;
            while ( word && ( at = lower.indexOf( word, from ) ) !== -1 ) {
                ranges.push( [ at, at + word.length ] );
                from = at + word.length;
            }
        });
        ranges.sort( function( a, b ) { return a[0] - b[0]; } );

        var pos = 0;
        $.each( ranges, function( i, range ) {
            if ( range[0] < pos ) {
                range[0] = pos;
            }
            if ( range[1] <= range[0] ) {
                return;
            }
            $el.append( document.createTextNode( text.slice( pos, range[0] ) ) );
            $( '<mark></mark>' ).text( text.slice( range[0], range[1] ) ).appendTo( $el );
            pos = range[1];
        });
        $el.append( document.createTextNode( text.slice( pos ) ) );
    }

    /**
     * Render the result list for the current search text.
     *
     * @since 7.14.4
     */
    function comboFill() {
        var query   = $comboSearch.val();
        var q       = $.trim( query );
        var words   = q ? q.toLowerCase().split( /\s+/ ) : [];
        var current = comboState.$input ? $.trim( comboState.$input.val() ) : '';

        $comboList.empty();
        comboState.options = [];
        comboState.active  = -1;

        if ( null === comboItems ) {
            $comboNote.text( comboStrings.combo_loading || 'Loading categories...' ).prop( 'hidden', false );
            return;
        }

        var result = comboSearch( query );
        var exact  = false;

        function addOption( value, leaf, path, kind ) {
            var id  = 'wpfm-catmap-opt-' + ( ++comboUid );
            var $li = $( '<li role="option" class="wpfm-catmap-popover__option"></li>' )
                .attr( { id: id, 'aria-selected': value === current && 'clear' !== kind ? 'true' : 'false' } )
                .data( 'value', value );

            if ( kind ) {
                $li.addClass( 'wpfm-catmap-popover__option--' + kind );
            }
            comboHighlight( $( '<span class="wpfm-catmap-popover__leaf"></span>' ).appendTo( $li ), leaf, kind ? [] : words );
            if ( path ) {
                comboHighlight( $( '<span class="wpfm-catmap-popover__path"></span>' ).appendTo( $li ), path, words );
            }
            $comboList.append( $li );
            comboState.options.push( $li );
        }

        if ( current && ! q ) {
            // Current value first (also when it is a custom value), then Clear.
            var currentParts = comboParts( current );
            addOption( current, currentParts.leaf, currentParts.path, '' );
            addOption( '', comboStrings.combo_clear || 'Clear selection', '', 'clear' );
        }

        $.each( result.items, function( i, item ) {
            if ( current && ! q && item.value === current ) {
                return;
            }
            if ( item.value.toLowerCase() === q.toLowerCase() || item.leafLower === q.toLowerCase() ) {
                exact = true;
            }
            addOption( item.value, item.leaf, item.path, '' );
        });

        if ( q && ! exact ) {
            // catmapFormat(), not String.replace(): "$&" or "$'" in the typed text must stay literal.
            addOption( q, catmapFormat( comboStrings.combo_custom || 'Use "%s"', q ), comboStrings.combo_custom_hint || 'Custom value', 'custom' );
        }

        if ( ! result.items.length && q ) {
            $comboNote.text( comboStrings.combo_no_results || 'No matching categories.' ).prop( 'hidden', false );
        } else if ( result.total > result.items.length ) {
            $comboNote.text( catmapFormat( comboStrings.combo_more || 'Showing %1$s of %2$s matches. Type more to narrow down.', result.items.length, result.total ) ).prop( 'hidden', false );
        } else {
            $comboNote.text( '' ).prop( 'hidden', true );
        }

        // Start on the current value, else on the first real match.
        var start = -1;
        $.each( comboState.options, function( i, $li ) {
            if ( 'true' === $li.attr( 'aria-selected' ) ) {
                start = i;
                return false;
            }
        });
        if ( -1 === start && comboState.options.length ) {
            start = comboState.options[0].hasClass( 'wpfm-catmap-popover__option--clear' ) && comboState.options.length > 1 ? 1 : 0;
        }
        comboActivate( start );
        $comboList.scrollTop( 0 );
        if ( start > 0 ) {
            comboState.options[ start ][0].scrollIntoView( { block: 'nearest' } );
        }
    }

    /**
     * Mark one option as active (keyboard focus inside the list).
     *
     * @since 7.14.4
     *
     * @param {number} index Option index, or -1 for none.
     */
    function comboActivate( index ) {
        $comboList.children().removeClass( 'is-active' );
        comboState.active = index;
        if ( index < 0 || ! comboState.options[ index ] ) {
            $comboSearch.removeAttr( 'aria-activedescendant' );
            return;
        }
        var $li = comboState.options[ index ].addClass( 'is-active' );
        $comboSearch.attr( 'aria-activedescendant', $li.attr( 'id' ) );
        $li[0].scrollIntoView( { block: 'nearest' } );
    }

    /**
     * Place the popover under (or above) its button, inside the viewport.
     *
     * @since 7.14.4
     */
    function comboPosition() {
        if ( ! comboState.$button ) {
            return;
        }
        var rect   = comboState.$button[0].getBoundingClientRect();
        var vw     = document.documentElement.clientWidth;
        var vh     = window.innerHeight;
        var width  = Math.min( Math.max( rect.width, 420 ), vw - 16 );
        var left   = Math.min( Math.max( 8, rect.left ), vw - width - 8 );
        var below  = vh - rect.bottom - 12;
        var above  = rect.top - 12;
        var height = Math.min( 380, Math.max( below, above ) );
        var top    = below >= Math.min( 380, height ) || below >= above ? rect.bottom + 4 : rect.top - 4 - height;

        $combo.css( { top: top, left: left, width: width, maxHeight: height } );
        $combo.toggleClass( 'is-above', top < rect.top );
    }

    /**
     * Open the popover for one row.
     *
     * @since 7.14.4
     *
     * @param {jQuery} $button Row combo button.
     */
    function comboOpen( $button ) {
        if ( comboState.$button && comboState.$button[0] === $button[0] ) {
            comboClose( true );
            return;
        }
        comboClose( false );
        comboState.$button = $button;
        comboState.$input  = $button.siblings( '.category-suggest' );
        $button.attr( 'aria-expanded', 'true' ).addClass( 'is-open' );
        $comboSearch.val( '' );
        $combo.prop( 'hidden', false );
        comboPosition();
        comboFill();
        $comboSearch.trigger( 'focus' );
    }

    /**
     * Close the popover.
     *
     * @since 7.14.4
     *
     * @param {boolean} refocus Move focus back to the row button.
     */
    function comboClose( refocus ) {
        if ( ! comboState.$button ) {
            return;
        }
        var $button = comboState.$button;
        $button.attr( 'aria-expanded', 'false' ).removeClass( 'is-open' );
        $combo.prop( 'hidden', true );
        comboState = { $input: null, $button: null, options: [], active: -1 };
        if ( refocus ) {
            $button.trigger( 'focus' );
        }
    }

    /**
     * Whether the category popover is open (used by the modal Escape key).
     *
     * @since 7.14.4
     *
     * @return {boolean}
     */
    function comboIsOpen() {
        return !! comboState.$button;
    }

    /**
     * Write the chosen option into the row and close.
     *
     * Triggers `change` so the suggestion chip and "Use all" count update.
     *
     * @since 7.14.4
     *
     * @param {number} index Option index.
     */
    function comboChoose( index ) {
        var $li = comboState.options[ index ];
        if ( ! $li || ! comboState.$input ) {
            return;
        }
        comboState.$input.val( $li.data( 'value' ) ).trigger( 'change' );
        comboRender( comboState.$input );
        comboClose( true );
    }

    $( document ).on( 'click', '.wpfm-catmap-combo', function( e ) {
        e.preventDefault();
        comboOpen( $( this ) );
    });

    $( document ).on( 'keydown', '.wpfm-catmap-combo', function( e ) {
        if ( 'ArrowDown' === e.key || 'ArrowUp' === e.key ) {
            e.preventDefault();
            comboOpen( $( this ) );
        }
    });

    $comboSearch.on( 'input', comboFill );

    $comboSearch.on( 'keydown', function( e ) {
        var count = comboState.options.length;
        switch ( e.key ) {
            case 'ArrowDown':
                e.preventDefault();
                comboActivate( count ? ( comboState.active + 1 ) % count : -1 );
                break;
            case 'ArrowUp':
                e.preventDefault();
                comboActivate( count ? ( comboState.active - 1 + count ) % count : -1 );
                break;
            case 'Home':
            case 'End':
                if ( count && e.ctrlKey ) {
                    e.preventDefault();
                    comboActivate( 'Home' === e.key ? 0 : count - 1 );
                }
                break;
            case 'Enter':
                e.preventDefault();
                if ( comboState.active > -1 ) {
                    comboChoose( comboState.active );
                }
                break;
            case 'Escape':
                e.preventDefault();
                e.stopPropagation();
                comboClose( true );
                break;
            case 'Tab':
                comboClose( false );
                break;
        }
    });

    $comboList.on( 'mousedown', '.wpfm-catmap-popover__option', function( e ) {
        e.preventDefault(); // keep focus in the search box
    });
    $comboList.on( 'click', '.wpfm-catmap-popover__option', function() {
        comboChoose( $.inArray( this, $.map( comboState.options, function( $li ) { return $li[0]; } ) ) );
    });
    $comboList.on( 'mousemove', '.wpfm-catmap-popover__option', function() {
        var index = $.inArray( this, $.map( comboState.options, function( $li ) { return $li[0]; } ) );
        if ( index !== comboState.active ) {
            $comboList.children().removeClass( 'is-active' );
            comboState.active = index;
            $( this ).addClass( 'is-active' );
            $comboSearch.attr( 'aria-activedescendant', this.id );
        }
    });

    // Close on outside click; follow the button when the dialog scrolls.
    $( document ).on( 'mousedown', function( e ) {
        if ( comboIsOpen() && ! $( e.target ).closest( '.wpfm-catmap-popover, .wpfm-catmap-combo' ).length ) {
            comboClose( false );
        }
    });
    $( window ).on( 'resize', comboPosition );
    document.addEventListener( 'scroll', function( e ) {
        if ( comboIsOpen() && ! $( e.target ).closest( '.wpfm-catmap-popover' ).length ) {
            comboPosition();
        }
    }, true );

    wpAjaxHelperRequest( 'rex-wpfm-fetch-google-category' )
        .success( function( data ) {
            try {
                comboIndex( 'string' === typeof data ? JSON.parse( data ) : data );
            } catch ( err ) {
                comboIndex( [] );
            }
            if ( comboIsOpen() ) {
                comboFill();
            }
        })
        .error( function( res ) {
            comboIndex( [] );
            console.log( res.statusText );
        });


    /**
     * Smart category suggestions.
     *
     * Suggestions for every product category, keyed by term ID:
     * { term_id: [ { value, score, confidence }, ... ] }.
     * Kept in memory so later steps (accept, save tracking) can reuse them.
     *
     * @since 7.14.4
     */
    var smartSuggestions = {};
    var smartStrings     = window.rex_wpfm_cat_map_translate_strings || {};

    /**
     * Whether suggestion chips are switched on in the open editor.
     *
     * Chips are shown only after the user clicks "Suggest categories" for a
     * Google Shopping map, and are reset when the editor opens or the
     * merchant changes.
     *
     * @since 7.14.4
     */
    var smartActive      = false;

    /**
     * Whether a merchant gets suggestions (its category field takes Google
     * product taxonomy values). The list comes from PHP.
     *
     * @since 7.14.4
     *
     * @param {string} merchant Merchant slug.
     * @return {boolean}
     */
    function smartSupportsMerchant( merchant ) {
        var list = $.isArray( smartStrings.smart_merchants ) && smartStrings.smart_merchants.length ? smartStrings.smart_merchants : [ 'google' ];
        return -1 !== $.inArray( merchant, list );
    }
    var smartLoaded      = false;
    // Map key the loaded suggestions were requested for ('' for a new map).
    var smartLoadedFor   = null;

    /**
     * Category ID of a stored value (`<id> - A > B`), or the trimmed text without one.
     *
     * @since 7.14.4
     *
     * @param {string} value Value.
     * @return {string}
     */
    function smartValueId( value ) {
        var text  = $.trim( String( value || '' ) );
        var match = /^(\d+)\s*-/.exec( text );
        return match ? match[1] : text;
    }

    /**
     * Suggestions for one row of the open map.
     *
     * When editing a saved map, a value the row held when the map was opened is left out:
     * the user cleared it on purpose, so it must not come back as a chip or through
     * "Fill suggestions" (it may still be known from this map's own earlier saves).
     *
     * @since 7.14.4
     *
     * @param {string} termId Term ID.
     * @return {Array} Suggestions, best first.
     */
    function smartListFor( termId ) {
        var list = termId ? smartSuggestions[ termId ] : null;
        if ( ! $.isArray( list ) || ! list.length ) {
            return [];
        }
        var map   = catmapKey && catmapMaps[ catmapKey ] ? catmapMaps[ catmapKey ] : null;
        var saved = map && map.values ? $.trim( String( map.values[ termId ] || '' ) ) : '';
        if ( '' === saved ) {
            return list;
        }
        var savedId = smartValueId( saved );
        return $.grep( list, function( item ) {
            return item && smartValueId( item.value ) !== savedId;
        });
    }

    /**
     * Short label for a Google category string: the leaf of `<id> - A > B > C`.
     *
     * @since 7.14.4
     *
     * @param {string} value Full autocomplete value.
     * @return {string} Leaf name.
     */
    function smartCategoryLeaf( value ) {
        var path  = String( value ).replace( /^\s*\d+\s*-\s*/, '' );
        var parts = path.split( ' > ' );
        return $.trim( parts[ parts.length - 1 ] );
    }

    /**
     * Small text button used inside the chip and alternatives list.
     *
     * Always `type="button"` so it never submits the mapping form.
     *
     * @since 7.14.4
     *
     * @param {string} className CSS class.
     * @param {string} label     Visible text.
     * @return {jQuery} Button.
     */
    function smartButton( className, label ) {
        return $( '<button type="button"></button>' ).addClass( className ).text( label );
    }

    /**
     * Put a Google category value into a row input.
     *
     * Triggers `change` so the chip re-renders (and hides) and any other
     * listener sees the new value. The user can still edit the input after.
     *
     * @since 7.14.4
     *
     * @param {jQuery} $row  `.single-category` row.
     * @param {string} value Full autocomplete value (`<id> - A > B`).
     */
    function applySmartValue( $row, value ) {
        $row.find( '.category-suggest' ).val( $.trim( value ) ).trigger( 'change' );
    }

    /**
     * Render (or remove) the suggestion chip of one mapping row.
     *
     * A chip is shown only when the row input is empty and the matcher returned
     * a suggestion for the row's term. Rows with a saved or typed value get none.
     * The chip has an Accept button and, when the matcher returned more than one
     * candidate, a toggle that lists up to 2 alternatives.
     *
     * @since 7.14.4
     *
     * @param {jQuery} $row `.single-category` row.
     */
    function renderSmartChip( $row ) {
        var termId = $row.attr( 'data-term-id' );
        var $field = $row.find( '.input-field' );
        var $input = $field.find( '.category-suggest' );
        var list   = smartListFor( termId );

        $field.find( '.wpfm-smart-chip, .wpfm-smart-alts' ).remove();

        if ( ! smartActive ) {
            return;
        }

        if ( ! $.isArray( list ) || ! list.length || '' !== $.trim( $input.val() ) ) {
            return;
        }

        var top        = list[0];
        var alts       = list.slice( 1, 3 );
        var confidence = String( top.confidence || 'low' ).toLowerCase();
        var confLabel  = smartStrings[ 'smart_' + confidence ] || confidence;
        var source     = 'memory' === top.source ? 'memory' : 'rules';

        // Remember what this row was shown, so Save / Update can report the outcome
        // even after the chip is hidden by Accept or typing.
        $row.data( 'wpfmSmartShown', {
            top: $.trim( top.value ),
            alts: $.map( alts, function( alt ) { return $.trim( alt.value ); } ),
            confidence: confidence,
            source: source
        });

        var $chip = $( '<div class="wpfm-smart-chip"></div>' )
            .addClass( 'wpfm-smart-chip--' + confidence )
            .attr( 'data-confidence', confidence )
            .attr( 'data-source', source );

        // Suggestions from the store's own earlier maps say so.
        // dir="auto": each piece keeps its own direction, so English text reads right on RTL admin screens.
        $( '<span class="wpfm-smart-chip__prefix" dir="auto"></span>' )
            .text( ( 'memory' === source ? ( smartStrings.smart_previous || 'Used before:' ) : ( smartStrings.smart_suggested || 'Suggested:' ) ) + ' ' )
            .appendTo( $chip );
        $( '<span class="wpfm-smart-chip__name" dir="auto"></span>' )
            .text( smartCategoryLeaf( top.value ) )
            .attr( 'title', $.trim( top.value ) )
            .appendTo( $chip );
        $( '<span class="wpfm-smart-chip__confidence"></span>' )
            .text( confLabel )
            .appendTo( $chip );

        smartButton( 'wpfm-smart-chip__accept', smartStrings.smart_accept || 'Accept' )
            .attr( 'title', $.trim( top.value ) )
            .on( 'click', function( e ) {
                e.preventDefault();
                applySmartValue( $row, top.value );
            })
            .appendTo( $chip );

        $field.append( $chip );

        if ( ! alts.length ) {
            return;
        }

        var listId = 'wpfm-smart-alts-' + termId + '-' + $row.closest( 'form' ).index( 'form' );
        var $alts  = $( '<ul class="wpfm-smart-alts" hidden></ul>' ).attr( 'id', listId );

        $.each( alts, function( i, alt ) {
            var altConf = String( alt.confidence || 'low' ).toLowerCase();
            var $item   = smartButton( 'wpfm-smart-alts__item', '' )
                .attr( 'title', $.trim( alt.value ) )
                .on( 'click', function( e ) {
                    e.preventDefault();
                    applySmartValue( $row, alt.value );
                });

            $( '<span class="wpfm-smart-alts__path" dir="auto"></span>' )
                .text( $.trim( String( alt.value ).replace( /^\s*\d+\s*-\s*/, '' ) ) )
                .appendTo( $item );
            $( '<span class="wpfm-smart-alts__confidence"></span>' )
                .addClass( 'wpfm-smart-alts__confidence--' + altConf )
                .text( smartStrings[ 'smart_' + altConf ] || altConf )
                .appendTo( $item );

            $( '<li></li>' ).append( $item ).appendTo( $alts );
        });

        smartButton( 'wpfm-smart-chip__toggle', ( smartStrings.smart_alternatives || 'Alternatives' ) + ' (' + alts.length + ')' )
            .attr( { 'aria-expanded': 'false', 'aria-controls': listId } )
            .on( 'click', function( e ) {
                e.preventDefault();
                var open = 'true' === $( this ).attr( 'aria-expanded' );
                $( this ).attr( 'aria-expanded', open ? 'false' : 'true' );
                $alts.prop( 'hidden', open );
            })
            .appendTo( $chip );

        $field.append( $alts );
    }

    /**
     * Confidence levels the Fill menu offers, in menu order.
     *
     * @since 7.15.1
     * @type {string[]}
     */
    var SMART_FILL_LEVELS = [ 'high', 'medium', 'low' ];

    /**
     * Build the "Fill All Suggestions" split button of one mapping form.
     *
     * The main button fills every empty row that shows a chip. The caret opens a
     * menu that fills only one confidence level.
     *
     * @since 7.15.1
     *
     * @return {jQuery} `.wpfm-smart-fill` wrapper.
     */
    function smartFillControl() {
        var $wrap = $( '<div class="wpfm-smart-fill"></div>' );
        var $menu = $( '<div class="wpfm-smart-fill__menu" role="menu" hidden></div>' );

        smartButton( 'wpfm-catmap-btn wpfm-catmap-btn--primary wpfm-smart-toolbar__use-all', smartStrings.smart_fill_all || 'Fill All Suggestions' )
            .attr( 'data-levels', SMART_FILL_LEVELS.join( ' ' ) )
            .appendTo( $wrap );

        smartButton( 'wpfm-catmap-btn wpfm-catmap-btn--primary wpfm-smart-fill__toggle', '' )
            .attr({
                'aria-haspopup': 'menu',
                'aria-expanded': 'false',
                'aria-label': smartStrings.smart_fill_more || 'More fill options'
            })
            .append( '<span class="wpfm-smart-fill__caret" aria-hidden="true"></span>' )
            .appendTo( $wrap );

        $.each( SMART_FILL_LEVELS, function( i, level ) {
            var $item = smartButton( 'wpfm-smart-fill__item wpfm-smart-fill__item--' + level, '' )
                .attr({ role: 'menuitem', 'data-levels': level });
            $( '<span class="wpfm-smart-fill__label"></span>' )
                .text( smartStrings[ 'smart_fill_' + level ] || ( 'Fill ' + level + ' confidence suggestions' ) )
                .appendTo( $item );
            $( '<span class="wpfm-smart-fill__count"></span>' ).appendTo( $item );
            $item.appendTo( $menu );
        });

        return $wrap.append( $menu );
    }

    /**
     * Open or close the Fill menu of one split button.
     *
     * @since 7.15.1
     *
     * @param {jQuery}  $wrap `.wpfm-smart-fill` wrapper.
     * @param {boolean} open  Whether the menu should be open.
     */
    function smartFillMenu( $wrap, open ) {
        $wrap.toggleClass( 'is-open', open );
        $wrap.find( '.wpfm-smart-fill__menu' ).prop( 'hidden', ! open );
        $wrap.find( '.wpfm-smart-fill__toggle' ).attr( 'aria-expanded', open ? 'true' : 'false' );
    }

    /**
     * Show, hide and update the "Fill All Suggestions" control of each mapping form.
     *
     * Counts the form's rows that show a chip of each confidence level (the row
     * is empty and has a suggestion). Menu items with nothing to fill are disabled.
     *
     * @since 7.14.4
     */
    function updateSmartUseAll() {
        $( 'form.wpfm-catmap-form' ).each( function() {
            var $form    = $( this );
            var counts   = {};
            var total    = 0;
            var $toolbar = $form.find( '.wpfm-smart-toolbar' );

            $.each( SMART_FILL_LEVELS, function( i, level ) {
                counts[ level ] = $form.find( '.wpfm-smart-chip--' + level ).length;
                total += counts[ level ];
            });

            if ( ! $toolbar.length ) {
                if ( ! total ) {
                    return;
                }
                $toolbar = $( '<div class="wpfm-smart-toolbar"></div>' );
                smartFillControl().appendTo( $toolbar );
                var $panelBody = $form.find( '.wpfm-catmap-suggest-panel__body' );
                if ( $panelBody.length ) {
                    $panelBody.append( $toolbar );
                } else {
                    $form.find( '.wpfm-catmap-grid-card' ).before( $toolbar );
                }
            }

            $.each( SMART_FILL_LEVELS, function( i, level ) {
                $toolbar.find( '.wpfm-smart-fill__item--' + level )
                    .prop( 'disabled', ! counts[ level ] )
                    .find( '.wpfm-smart-fill__count' ).text( counts[ level ] );
            });

            if ( ! total ) {
                smartFillMenu( $toolbar.find( '.wpfm-smart-fill' ), false );
            }
            $toolbar.prop( 'hidden', ! total );
        });
        catmapUpdateFilterCounts();
    }

    /**
     * Fill every empty row of a form whose chip has one of the given levels.
     *
     * @since 7.15.1
     *
     * @param {jQuery}   $form  Mapping form.
     * @param {string[]} levels Confidence levels to fill.
     */
    function smartFillLevels( $form, levels ) {
        var selector = $.map( levels, function( level ) {
            return -1 !== $.inArray( level, SMART_FILL_LEVELS ) ? '.wpfm-smart-chip--' + level : null;
        }).join( ', ' );

        if ( ! selector ) {
            return;
        }

        $form.find( selector ).each( function() {
            var $row   = $( this ).closest( '.single-category' );
            var termId = $row.attr( 'data-term-id' );
            var list   = smartListFor( termId );

            if ( list.length && '' === $.trim( $row.find( '.category-suggest' ).val() ) ) {
                comboRender( $row.find( '.category-suggest' ).val( $.trim( list[0].value ) ) );
                renderSmartChip( $row );
            }
        });

        updateSmartUseAll();
        catmapUpdateProgress();
    }

    // "Fill All Suggestions" and the per-level menu items.
    $( document ).on( 'click', '.wpfm-smart-toolbar__use-all, .wpfm-smart-fill__item', function( e ) {
        e.preventDefault();
        var $btn = $( this );
        if ( $btn.prop( 'disabled' ) ) {
            return;
        }
        var $wrap = $btn.closest( '.wpfm-smart-fill' );
        smartFillMenu( $wrap, false );
        smartFillLevels( $btn.closest( 'form' ), String( $btn.attr( 'data-levels' ) || '' ).split( ' ' ) );
        if ( $btn.hasClass( 'wpfm-smart-fill__item' ) ) {
            $wrap.find( '.wpfm-smart-fill__toggle' ).trigger( 'focus' );
        }
    });

    $( document ).on( 'click', '.wpfm-smart-fill__toggle', function( e ) {
        e.preventDefault();
        var $wrap = $( this ).closest( '.wpfm-smart-fill' );
        var open  = ! $wrap.hasClass( 'is-open' );
        $( '.wpfm-smart-fill.is-open' ).not( $wrap ).each( function() {
            smartFillMenu( $( this ), false );
        });
        smartFillMenu( $wrap, open );
        if ( open ) {
            $wrap.find( '.wpfm-smart-fill__item:not(:disabled)' ).first().trigger( 'focus' );
        }
    });

    // Keyboard: Escape closes the menu (not the editor), arrows move between items.
    $( document ).on( 'keydown', '.wpfm-smart-fill', function( e ) {
        var $wrap = $( this );
        if ( ! $wrap.hasClass( 'is-open' ) ) {
            return;
        }
        if ( 'Escape' === e.key ) {
            e.preventDefault();
            e.stopPropagation();
            smartFillMenu( $wrap, false );
            $wrap.find( '.wpfm-smart-fill__toggle' ).trigger( 'focus' );
            return;
        }
        if ( 'ArrowDown' !== e.key && 'ArrowUp' !== e.key ) {
            return;
        }
        e.preventDefault();
        var $items = $wrap.find( '.wpfm-smart-fill__item:not(:disabled)' );
        if ( ! $items.length ) {
            return;
        }
        var index = $items.index( document.activeElement );
        index = 'ArrowDown' === e.key ? index + 1 : ( index < 0 ? $items.length - 1 : index - 1 );
        $items.eq( ( index + $items.length ) % $items.length ).trigger( 'focus' );
    });

    $( document ).on( 'click focusin', function( e ) {
        if ( ! $( e.target ).closest( '.wpfm-smart-fill' ).length ) {
            $( '.wpfm-smart-fill.is-open' ).each( function() {
                smartFillMenu( $( this ), false );
            });
        }
    });

    // "About these suggestions" tooltip. CSS shows it on hover and keyboard
    // focus; a click or tap pins it open, Escape or a click elsewhere closes it.
    $( document ).on( 'click', '.wpfm-catmap-info__btn', function( e ) {
        e.preventDefault();
        var $info = $( this ).closest( '.wpfm-catmap-info' );
        $info.removeClass( 'is-dismissed' ).toggleClass( 'is-open' );
    });

    $( document ).on( 'keydown', '.wpfm-catmap-info__btn', function( e ) {
        if ( 'Escape' === e.key ) {
            var $info = $( this ).closest( '.wpfm-catmap-info' );
            if ( $info.find( '.wpfm-catmap-info__tip' ).is( ':visible' ) ) {
                // Keep the modal open: this Escape only closes the tooltip.
                e.stopImmediatePropagation();
            }
            $info.removeClass( 'is-open' ).addClass( 'is-dismissed' );
        }
    });

    $( document ).on( 'focusout mouseleave', '.wpfm-catmap-info', function() {
        $( this ).removeClass( 'is-dismissed' );
    });

    $( document ).on( 'click', function( e ) {
        if ( ! $( e.target ).closest( '.wpfm-catmap-info' ).length ) {
            $( '.wpfm-catmap-info.is-open' ).removeClass( 'is-open' );
        }
    });

    /**
     * Load suggestions for every product category (one request, then cached).
     *
     * @since 7.14.4
     *
     * @param {Function} done Called with `true` on success, `false` on failure.
     */
    function smartLoadSuggestions( done ) {
        var forKey = catmapKey || '';
        if ( smartLoaded && smartLoadedFor === forKey ) {
            done( true );
            return;
        }
        // The open map is left out of "Used before" on the server.
        wpAjaxHelperRequest( 'rexfeed-suggest-categories', { map_key: forKey } )
            .success( function( response ) {
                if ( ! response || ! response.success || ! response.data || 'object' !== typeof response.data ) {
                    done( false );
                    return;
                }
                smartSuggestions = response.data;
                smartLoaded      = true;
                smartLoadedFor   = forKey;
                done( true );
            })
            .error( function( res ) {
                console.log( res.statusText );
                done( false );
            });
    }

    /**
     * Remove all suggestion chips and forget what was shown.
     *
     * Used when the editor opens and when the merchant no longer gets
     * suggestions, so the save payload never reports suggestions the user did
     * not see for this map.
     *
     * @since 7.14.4
     */
    function smartClearChips() {
        smartActive = false;
        $( '.single-category[data-term-id]' ).each( function() {
            $( this ).removeData( 'wpfmSmartShown' );
            $( this ).find( '.wpfm-smart-chip, .wpfm-smart-alts' ).remove();
        });
        updateSmartUseAll();
    }

    // Hide the chip once the row has a value; bring it back when cleared.
    $( document ).on( 'change', '.single-category .category-suggest', function() {
        var $row = $( this ).closest( '.single-category' );
        comboRender( $( this ) );
        renderSmartChip( $row );
        updateSmartUseAll();
        catmapUpdateProgress();
    });

    /**
     * Suggestions shown in one mapping form, for the `suggestions` save payload.
     *
     * Only rows that rendered a chip during this page view are included, so rows
     * that already had a saved value are never reported.
     *
     * @since 7.14.4
     *
     * @param {jQuery} $form Mapping form.
     * @return {string} JSON `{ term_id: { top, alts[], confidence, source } }`.
     */
    function smartShownSuggestions( $form ) {
        var shown = {};
        $form.find( '.single-category[data-term-id]' ).each( function() {
            var data = $( this ).data( 'wpfmSmartShown' );
            if ( data && data.top ) {
                shown[ $( this ).attr( 'data-term-id' ) ] = data;
            }
        });
        return JSON.stringify( shown );
    }

    /**
     * Category mapping list, add / edit modal and delete confirmation.
     *
     * The page renders the mapping rows once inside `#wpfm-catmap-editor`.
     * Opening a map fills the row inputs from the `#wpfm-catmap-data` JSON
     * (`{ map_key: { name, values: { term_id: value } } }`), so the same rows,
     * chips and save payload serve both "add" and "edit".
     *
     * @since 7.14.4
     */
    var catmapStrings  = $.extend( {
        add_title: 'Add Category Mapping',
        edit_title: 'Edit Category Mapping',
        showing: 'Showing %1$s-%2$s of %3$s categories',
        delete_confirm: 'Are you sure you want to delete the category mapping \'%s\'?',
        saving: 'Saving...',
        deleting: 'Deleting...',
        saved: 'Category mapping saved.',
        deleted: 'Category mapping deleted.',
        save_failed: 'Could not save the category mapping. Please try again.',
        delete_failed: 'Could not delete the category mapping. Please try again.',
        template: 'Google Shopping',
        edit_label: 'Edit %s',
        delete_label: 'Delete %s',
        merchant_category: '%s Category',
        suggest: 'Suggest categories',
        suggesting: 'Suggesting...',
        suggest_google_only: 'Category suggestions are available for merchants that use Google product categories.',
        suggest_done: '%1$s of %2$s categories have suggestions',
        suggest_none: 'No suggestions for the empty rows.',
        progress_table: '%1$s / %2$s mapped',
        suggest_failed: 'Could not load suggestions. Please try again.'
    }, smartStrings );
    var catmapPerPage  = 12;
    var catmapFilter   = 'all';
    var catmapSearch   = '';

    try {
        var catmapSavedSize = window.localStorage.getItem( 'wpfmCatmapPerPage' );
        if ( 'all' === catmapSavedSize || -1 !== $.inArray( parseInt( catmapSavedSize, 10 ), [ 12, 24, 48, 96 ] ) ) {
            catmapPerPage = 'all' === catmapSavedSize ? 'all' : parseInt( catmapSavedSize, 10 );
        }
    } catch ( err ) {}
    var catmapPage     = 1;
    var catmapKey      = '';
    var catmapDeleting = '';
    var catmapLastFocus = null;
    var catmapMaps     = {};
    var $catmapRoot    = $( '#wpfm-catmap' );
    var $catmapEditor  = $( '#wpfm-catmap-editor' );
    var $catmapDelete  = $( '#wpfm-catmap-delete' );
    var $catmapForm    = $catmapEditor.find( '.wpfm-catmap-form' );
    var $catmapRows    = $catmapForm.find( '.single-category' );

    try {
        catmapMaps = JSON.parse( $( '#wpfm-catmap-data' ).text() || '{}' ) || {};
    } catch ( err ) {
        catmapMaps = {};
    }

    /**
     * Replace `%s` / `%1$s` style placeholders.
     *
     * @since 7.14.4
     *
     * @param {string} text Template.
     * @return {string} Formatted text.
     */
    function catmapFormat( text ) {
        var args = Array.prototype.slice.call( arguments, 1 );
        var next = 0;
        return String( text ).replace( /%%|%(\d+\$)?s/g, function( match, pos ) {
            if ( '%%' === match ) {
                return '%';
            }
            var index = pos ? parseInt( pos, 10 ) - 1 : next++;
            return undefined !== args[ index ] ? args[ index ] : '';
        });
    }

    /**
     * Show a short status message above the list.
     *
     * @since 7.14.4
     *
     * @param {string} message Text.
     * @param {string} type    `success` or `error`.
     */
    function catmapNotice( message, type ) {
        var $notice = $catmapRoot.find( '.wpfm-catmap-notice' );
        clearTimeout( $notice.data( 'timer' ) );
        $notice
            .removeClass( 'wpfm-catmap-notice--success wpfm-catmap-notice--error' )
            .addClass( 'wpfm-catmap-notice--' + ( type || 'success' ) )
            .text( message )
            .prop( 'hidden', false );
        $notice.data( 'timer', setTimeout( function() {
            $notice.prop( 'hidden', true );
        }, 5000 ) );
    }

    /**
     * Show the rows of the current page in two columns.
     *
     * Rows fill the left column first, then the right one, so a parent and its
     * children read top to bottom. Hidden rows keep their inputs, so the form
     * still sends every category on save.
     *
     * @since 7.14.4
     */
    function catmapRenderPage() {
        var $match  = $catmapRows.filter( function() {
            return catmapRowMatches( $( this ) );
        });
        var total   = $match.length;
        var perPage = 'all' === catmapPerPage ? Math.max( 1, total ) : catmapPerPage;
        var pages   = Math.max( 1, Math.ceil( total / perPage ) );

        catmapPage = Math.min( Math.max( 1, catmapPage ), pages );

        var start = ( catmapPage - 1 ) * perPage;
        var end   = Math.min( total, start + perPage );
        var shown = $match.slice( start, end );

        $catmapRows.addClass( 'is-hidden' );
        shown.removeClass( 'is-hidden' );

        $catmapForm.find( '.wpfm-catmap-grid' ).css( '--wpfm-catmap-rows', Math.max( 1, Math.ceil( ( end - start ) / 2 ) ) );
        $catmapForm.find( '.wpfm-catmap-grid__empty' ).prop( 'hidden', total > 0 );
        $catmapForm.find( '.wpfm-catmap-pager__status' ).text(
            total ? catmapFormat( catmapStrings.showing, start + 1, end, total ) : ''
        );
        $catmapForm.find( '.wpfm-catmap-pager__prev' ).prop( 'disabled', catmapPage <= 1 );
        $catmapForm.find( '.wpfm-catmap-pager__next' ).prop( 'disabled', catmapPage >= pages );
        $catmapForm.find( '.wpfm-catmap-pager__buttons' ).prop( 'hidden', pages <= 1 );
        $catmapForm.find( '.wpfm-catmap-pager' ).prop( 'hidden', ! $catmapRows.length );
    }

    /**
     * Whether a row passes the active filter and the store category search.
     *
     * Filters: `all`, `unmapped` (empty value), `mapped`, and `high` /
     * `medium` / `low` (row shows a suggestion chip of that confidence).
     *
     * @since 7.14.4
     *
     * @param {jQuery} $row `.single-category` row.
     * @return {boolean}
     */
    function catmapRowMatches( $row ) {
        var empty = '' === $.trim( $row.find( '.category-suggest' ).val() );

        switch ( catmapFilter ) {
            case 'unmapped':
                if ( ! empty ) {
                    return false;
                }
                break;
            case 'mapped':
                if ( empty ) {
                    return false;
                }
                break;
            case 'high':
            case 'medium':
            case 'low':
                if ( ! $row.find( '.wpfm-smart-chip--' + catmapFilter ).length ) {
                    return false;
                }
                break;
        }

        if ( catmapSearch ) {
            // Each typed word must start a word of the name or its parent path,
            // so "hair" finds "Hair Care" but not "Pushchairs".
            var text  = ' ' + $row.find( '.label' ).text().toLowerCase().replace( /[^a-z0-9À-￿]+/g, ' ' );
            var words = catmapSearch.split( /\s+/ );
            for ( var i = 0; i < words.length; i++ ) {
                if ( words[ i ] && text.indexOf( ' ' + words[ i ] ) === -1 ) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Refresh the counts on the filter buttons (live, without moving rows).
     *
     * Suggestion filters appear only while suggestion chips are shown. If the
     * active one has nothing left to show, the view goes back to All.
     *
     * @since 7.14.4
     */
    function catmapUpdateFilterCounts() {
        if ( ! $catmapRows || ! $catmapRows.length ) {
            return;
        }
        var counts = { all: $catmapRows.length, unmapped: 0, mapped: 0, high: 0, medium: 0, low: 0 };

        $catmapRows.each( function() {
            var $row = $( this );
            counts[ '' === $.trim( $row.find( '.category-suggest' ).val() ) ? 'unmapped' : 'mapped' ]++;
            var $chip = $row.find( '.wpfm-smart-chip' );
            if ( $chip.length ) {
                counts[ $chip.attr( 'data-confidence' ) ] = ( counts[ $chip.attr( 'data-confidence' ) ] || 0 ) + 1;
            }
        });

        var anySmart = !! ( counts.high || counts.medium || counts.low );
        $catmapForm.find( '.wpfm-catmap-filter' ).each( function() {
            var key = $( this ).attr( 'data-filter' );
            $( this ).find( '.wpfm-catmap-filter__count' ).text( counts[ key ] || 0 );
            if ( $( this ).hasClass( 'wpfm-catmap-filter--smart' ) ) {
                $( this ).prop( 'hidden', ! anySmart );
            }
        });
        catmapSyncSuggestPanel();

        if ( -1 !== $.inArray( catmapFilter, [ 'high', 'medium', 'low' ] ) && ! anySmart ) {
            catmapSetFilter( 'all' );
        }
    }

    /**
     * Switch the row filter and go back to page 1.
     *
     * @since 7.14.4
     *
     * @param {string} filter Filter key.
     */
    function catmapSetFilter( filter ) {
        catmapFilter = filter;
        $catmapForm.find( '.wpfm-catmap-filter' ).each( function() {
            $( this ).attr( 'aria-pressed', $( this ).attr( 'data-filter' ) === filter ? 'true' : 'false' );
        });
        catmapPage = 1;
        catmapRenderPage();
    }

    $catmapForm.on( 'click', '.wpfm-catmap-filter', function( e ) {
        e.preventDefault();
        catmapSetFilter( $( this ).attr( 'data-filter' ) );
    });

    var catmapSearchTimer = null;
    $catmapForm.on( 'input', '#wpfm-catmap-row-search', function() {
        var value = $.trim( this.value ).toLowerCase();
        clearTimeout( catmapSearchTimer );
        catmapSearchTimer = setTimeout( function() {
            catmapSearch = value;
            catmapPage   = 1;
            catmapRenderPage();
        }, 120 );
    });
    // Enter in the search box must not submit (save) the map.
    $catmapForm.on( 'keydown', '#wpfm-catmap-row-search', function( e ) {
        if ( 'Enter' === e.key ) {
            e.preventDefault();
        }
    });

    $catmapForm.on( 'change', '#wpfm-catmap-per-page', function() {
        catmapPerPage = 'all' === this.value ? 'all' : ( parseInt( this.value, 10 ) || 12 );
        try {
            window.localStorage.setItem( 'wpfmCatmapPerPage', String( catmapPerPage ) );
        } catch ( err ) {}
        catmapPage = 1;
        catmapRenderPage();
    });

    /**
     * Mapped / total counts and a progress bar.
     *
     * @since 7.14.4
     *
     * @param {jQuery} $bar   `.wpfm-catmap-progress__bar` element.
     * @param {number} mapped Mapped categories.
     * @param {number} total  All categories.
     */
    function catmapSetBar( $bar, mapped, total ) {
        var percent = total ? Math.round( mapped / total * 100 ) : 0;
        $bar.attr( { 'aria-valuenow': mapped, 'aria-valuemax': total } )
            .children( 'span' ).css( 'width', percent + '%' );
        $bar.closest( '.wpfm-catmap-progress' ).toggleClass( 'is-complete', !! total && mapped >= total );
    }

    /**
     * Count mapped rows from the row inputs and refresh the filter tab counts.
     *
     * @since 7.14.4
     *
     * @return {{mapped: number, total: number}}
     */
    function catmapUpdateProgress() {
        var total  = $catmapRows.length;
        var mapped = $catmapRows.find( '.category-suggest' ).filter( function() {
            return '' !== $.trim( this.value );
        }).length;

        catmapUpdateFilterCounts();

        return { mapped: mapped, total: total };
    }

    $catmapForm.on( 'click', '.wpfm-catmap-pager__prev, .wpfm-catmap-pager__next', function( e ) {
        e.preventDefault();
        catmapPage += $( this ).hasClass( 'wpfm-catmap-pager__next' ) ? 1 : -1;
        catmapRenderPage();
        $catmapForm.find( '.wpfm-catmap-grid-card' )[0].scrollIntoView( { block: 'nearest' } );
    });

    /**
     * Open a modal and remember where focus was.
     *
     * @since 7.14.4
     *
     * @param {jQuery} $modal Modal element.
     * @param {jQuery} $focus Element to focus inside the modal.
     */
    function catmapOpenModal( $modal, $focus ) {
        catmapLastFocus = document.activeElement;
        $modal.prop( 'hidden', false );
        $( 'body' ).addClass( 'wpfm-catmap-modal-open' );
        setTimeout( function() {
            ( $focus && $focus.length ? $focus : $modal.find( 'button' ).first() ).trigger( 'focus' );
        }, 0 );
    }

    /**
     * Close a modal and give focus back.
     *
     * @since 7.14.4
     *
     * @param {jQuery} $modal Modal element.
     */
    function catmapCloseModal( $modal ) {
        $modal.prop( 'hidden', true );
        comboClose( false );
        if ( ! $( '.wpfm-catmap-modal:not([hidden])' ).length ) {
            $( 'body' ).removeClass( 'wpfm-catmap-modal-open' );
        }
        if ( catmapLastFocus && document.body.contains( catmapLastFocus ) ) {
            catmapLastFocus.focus();
        }
        catmapLastFocus = null;
    }

    var $catmapMerchant = $catmapForm.find( '#wpfm_catmap_merchant' );
    var $catmapSuggest  = $catmapEditor.find( '#wpfm-catmap-suggest' );

    /**
     * Display name of a merchant slug, from the merchant select.
     *
     * @since 7.14.4
     *
     * @param {string} merchant Merchant slug.
     * @return {string} Name.
     */
    function catmapMerchantName( merchant ) {
        var $option = $catmapMerchant.find( 'option' ).filter( function() {
            return this.value === merchant;
        });
        return $option.length ? $.trim( $option.first().text() ) : merchant;
    }

    /**
     * Status line under the editor fields (suggest results and hints).
     *
     * @since 7.14.4
     *
     * @param {string} message Text, or empty to clear.
     * @param {string} type    `info`, `success` or `error`.
     */
    function catmapSuggestStatus( message, type ) {
        $catmapForm.find( '.wpfm-catmap-suggest-status' )
            .removeClass( 'is-info is-success is-error' )
            .addClass( message ? 'is-' + ( type || 'info' ) : '' )
            .text( message || '' );
        catmapSyncSuggestPanel();
    }

    /**
     * Show the suggestion panel only while it has something to say: a status
     * message, or suggestion chips (which bring the confidence filters and the
     * bulk fill button).
     *
     * @since 7.14.4
     */
    function catmapSyncSuggestPanel() {
        var $panel    = $catmapForm.find( '.wpfm-catmap-suggest-panel' );
        var hasChips  = $catmapForm.find( '.wpfm-smart-chip' ).length > 0;
        var hasStatus = '' !== $.trim( $panel.find( '.wpfm-catmap-suggest-status' ).text() );

        $panel.find( '.wpfm-catmap-suggest-panel__body' ).prop( 'hidden', ! hasChips );
        $panel.toggleClass( 'has-chips', hasChips ).prop( 'hidden', ! hasChips && ! hasStatus );
    }

    /**
     * Apply the selected merchant: column title, Suggest button state.
     *
     * Suggestions use the Google product taxonomy, so the Suggest button only
     * works for merchants in `smart_merchants` (Google, Meta, Pinterest,
     * TikTok, ...). Leaving them removes any shown chips.
     *
     * @since 7.14.4
     */
    function catmapApplyMerchant() {
        var merchant = $catmapMerchant.val();
        var isGoogle = smartSupportsMerchant( merchant );

        $catmapForm.find( '.wpfm-catmap-merchant-col' ).text(
            catmapFormat( catmapStrings.merchant_category, catmapMerchantName( merchant ) )
        );
        $catmapSuggest
            .toggleClass( 'is-unavailable', ! isGoogle )
            .attr( 'aria-disabled', isGoogle ? null : 'true' )
            .attr( 'title', isGoogle ? '' : catmapStrings.suggest_google_only );

        if ( ! isGoogle ) {
            smartClearChips();
        }
        catmapSuggestStatus( '' );
    }

    /**
     * Set the merchant select value (and its select2 view, when used).
     *
     * @since 7.14.4
     *
     * @param {string} merchant Merchant slug.
     */
    function catmapSetMerchant( merchant ) {
        if ( ! $catmapMerchant.find( 'option' ).filter( function() { return this.value === merchant; } ).length ) {
            merchant = $catmapMerchant.attr( 'data-default' );
        }
        $catmapMerchant.val( merchant ).trigger( 'change.select2' );
        catmapApplyMerchant();
    }

    $catmapMerchant.on( 'change', catmapApplyMerchant );

    // Searchable merchant list (200+ merchants) when select2 is loaded.
    if ( $.fn.select2 ) {
        $catmapMerchant.select2( {
            width: '100%',
            dropdownParent: $catmapEditor.find( '.wpfm-catmap-modal__dialog' ),
            dropdownCssClass: 'wpfm-catmap-select2'
        } );
    }

    // Suggest categories: Google Shopping maps only, on demand.
    $catmapSuggest.on( 'click', function( e ) {
        e.preventDefault();
        var $btn = $( this );

        if ( ! smartSupportsMerchant( $catmapMerchant.val() ) ) {
            catmapSuggestStatus( catmapStrings.suggest_google_only, 'info' );
            return;
        }
        if ( $btn.attr( 'aria-busy' ) ) {
            return;
        }

        $btn.attr( 'aria-busy', 'true' ).addClass( 'is-busy' );
        $btn.find( '.wpfm-catmap-btn--suggest__label' ).text( catmapStrings.suggesting );
        catmapSuggestStatus( '' );

        smartLoadSuggestions( function( ok ) {
            $btn.removeAttr( 'aria-busy' ).removeClass( 'is-busy' );
            $btn.find( '.wpfm-catmap-btn--suggest__label' ).text( catmapStrings.suggest );

            if ( ! ok ) {
                catmapSuggestStatus( catmapStrings.suggest_failed, 'error' );
                return;
            }
            // The merchant may have changed while the request ran.
            if ( ! smartSupportsMerchant( $catmapMerchant.val() ) || $catmapEditor.prop( 'hidden' ) ) {
                return;
            }

            smartActive = true;
            $catmapRows.each( function() {
                renderSmartChip( $( this ) );
            });
            updateSmartUseAll();

            // Chips only render on empty rows, so "of" counts the unmapped rows.
            var count    = $catmapRows.find( '.wpfm-smart-chip' ).length;
            var unmapped = $catmapRows.find( '.category-suggest' ).filter( function() {
                return '' === $.trim( this.value );
            }).length;
            catmapSuggestStatus(
                count ? catmapFormat( catmapStrings.suggest_done, count, unmapped ) : catmapStrings.suggest_none,
                count ? 'success' : 'info'
            );
        });
    });

    /**
     * Open the editor for a new map (no key) or a saved one.
     *
     * Fills every row from the saved values, then re-renders the suggestion
     * chips, so rows with a value show none and the save payload only reports
     * suggestions shown in this session.
     *
     * @since 7.14.4
     *
     * @param {string} key Map key, or empty for a new map.
     */
    function catmapOpenEditor( key ) {
        var map    = key && catmapMaps[ key ] ? catmapMaps[ key ] : null;
        var values = map && map.values ? map.values : {};

        catmapKey = map ? key : '';
        smartClearChips();
        catmapSetMerchant( map && map.merchant ? map.merchant : $catmapMerchant.attr( 'data-default' ) );
        $catmapEditor.toggleClass( 'is-edit', !! map );
        $catmapEditor.find( '#wpfm-catmap-editor-title' ).text( map ? catmapStrings.edit_title : catmapStrings.add_title );
        $catmapForm.find( '#map_name' ).val( map ? map.name : '' ).removeAttr( 'aria-invalid' );
        $catmapForm.find( '#wpfm-catmap-name-error' ).prop( 'hidden', true );

        $catmapRows.each( function() {
            var $row   = $( this );
            var termId = $row.attr( 'data-term-id' );
            var value  = undefined !== values[ termId ] ? values[ termId ] : '';
            comboRender( $row.find( '.category-suggest' ).val( value ) );
        });

        catmapFilter = 'all';
        catmapSearch = '';
        $catmapForm.find( '#wpfm-catmap-row-search' ).val( '' );
        $catmapForm.find( '#wpfm-catmap-per-page' ).val( String( catmapPerPage ) );
        catmapUpdateProgress();
        catmapSetFilter( 'all' );
        catmapOpenModal( $catmapEditor, $catmapForm.find( '#map_name' ) );
        $catmapEditor.find( '.wpfm-catmap-modal__dialog' ).scrollTop( 0 );
    }

    $( document ).on( 'click', '.wpfm-catmap-open-add', function( e ) {
        e.preventDefault();
        catmapOpenEditor( '' );
    });

    $( document ).on( 'click', '.wpfm-catmap-edit', function( e ) {
        e.preventDefault();
        catmapOpenEditor( $( this ).closest( 'tr' ).attr( 'data-map-key' ) );
    });

    $( document ).on( 'click', '.wpfm-catmap-modal .wpfm-catmap-close', function( e ) {
        e.preventDefault();
        catmapCloseModal( $( this ).closest( '.wpfm-catmap-modal' ) );
    });

    // Clicking the dimmed backdrop closes only the delete dialog; the editor
    // may hold unsaved work, so it closes only from its own buttons or Escape.
    $catmapDelete.on( 'mousedown', function( e ) {
        if ( e.target === this ) {
            catmapCloseModal( $catmapDelete );
        }
    });

    $( document ).on( 'keydown', function( e ) {
        if ( 'Escape' !== e.key ) {
            return;
        }
        // Escape first closes an open category list.
        if ( comboIsOpen() ) {
            return;
        }
        // An Escape that an inner widget already handled (the select2 merchant list closes
        // itself on Escape) must not also close the editor and lose unsaved rows.
        if ( e.isDefaultPrevented() || $( e.target ).closest( '.select2-dropdown' ).length ) {
            return;
        }
        var $open = $( '.wpfm-catmap-modal:not([hidden])' ).last();
        if ( $open.length ) {
            e.preventDefault();
            catmapCloseModal( $open );
        }
    });

    /**
     * Turn a button into a busy state while a request runs.
     *
     * @since 7.14.4
     *
     * @param {jQuery}  $btn  Button.
     * @param {boolean} busy  Busy or not.
     * @param {string}  label Label while busy.
     */
    function catmapBusy( $btn, busy, label ) {
        $btn.each( function() {
            var $one = $( this );
            if ( busy ) {
                $one.data( 'label', $one.html() ).prop( 'disabled', true ).attr( 'aria-busy', 'true' );
                if ( $one.is( '#save_mapping_cat, .wpfm-catmap-delete-confirm' ) ) {
                    $one.text( label );
                }
            } else if ( undefined !== $one.data( 'label' ) ) {
                $one.html( $one.data( 'label' ) ).prop( 'disabled', false ).removeAttr( 'aria-busy' );
            }
        });
    }

    /**
     * Add or update a row of the list table after a save.
     *
     * @since 7.14.4
     *
     * @param {string} key      Map key.
     * @param {string} name     Map name.
     * @param {string} template Merchant display name.
     * @param {Object} progress `{ mapped, total }` of the saved map.
     */
    function catmapUpsertListRow( key, name, template, progress ) {
        var $tbody = $catmapRoot.find( '.wpfm-catmap-table tbody' );
        var $row   = $tbody.children( 'tr' ).filter( function() {
            return $( this ).attr( 'data-map-key' ) === key;
        });

        if ( ! $row.length ) {
            return;
        }
        $row.find( '.wpfm-catmap-table__map-name' ).text( name );
        $row.find( 'td.wpfm-catmap-table__template' ).text( template );
        if ( progress ) {
            catmapSetBar( $row.find( '.wpfm-catmap-progress__bar' ), progress.mapped, progress.total );
            $row.find( '.wpfm-catmap-progress__text' ).text( catmapFormat( catmapStrings.progress_table, progress.mapped, progress.total ) );
        }
        $row.find( '.wpfm-catmap-edit' ).attr( 'aria-label', catmapFormat( catmapStrings.edit_label, name ) );
        $row.find( '.wpfm-catmap-delete' ).attr( 'aria-label', catmapFormat( catmapStrings.delete_label, name ) );
    }

    /**
     * Save the map open in the editor (new map or update).
     *
     * Sends only the category inputs as `cat_map`, in the same format as
     * before, plus the shown suggestions for feedback tracking.
     *
     * @since 7.14.4
     *
     * @param {Event} event Click / submit event.
     */
    function catmapSave( event ) {
        event.preventDefault();

        var $btn      = $( this ).is( 'button' ) ? $( this ) : $catmapForm.find( '#save_mapping_cat' );
        var closeTab  = 'update_close_mapping_cat' === $btn.attr( 'id' );
        var $name     = $catmapForm.find( '#map_name' );
        var name      = $.trim( $name.val() );
        var values    = {};

        if ( ! name ) {
            $name.attr( 'aria-invalid', 'true' ).trigger( 'focus' );
            $catmapForm.find( '#wpfm-catmap-name-error' ).prop( 'hidden', false );
            return false;
        }
        $name.removeAttr( 'aria-invalid' );
        $catmapForm.find( '#wpfm-catmap-name-error' ).prop( 'hidden', true );

        $catmapRows.each( function() {
            values[ $( this ).attr( 'data-term-id' ) ] = $( this ).find( '.category-suggest' ).val();
        });

        var isUpdate = '' !== catmapKey;
        var payload  = {
            map_name: name,
            cat_map: $catmapForm.find( '.category-suggest' ).serialize(),
            suggestions: smartShownSuggestions( $catmapForm ),
            merchant: $catmapMerchant.val(),
            feed_id: $( '#post_ID' ).val()
        };

        if ( isUpdate ) {
            payload.map_key = catmapKey;
        } else {
            payload.track = 'yes';
        }

        catmapBusy( $catmapForm.find( '.wpfm-catmap-editor__footer button' ), true, catmapStrings.saving );

        wpAjaxHelperRequest( isUpdate ? 'rexfeed-update-category-mapping' : 'rexfeed-save-category-mapping', payload )
            .success( function( response ) {
                if ( ! isUpdate ) {
                    if ( response && response.success && response.data && response.data.location ) {
                        location.replace( response.data.location + '&new-added=1' );
                        return;
                    }
                    catmapBusy( $catmapForm.find( '.wpfm-catmap-editor__footer button' ), false );
                    catmapNotice( catmapStrings.save_failed, 'error' );
                    return;
                }

                catmapBusy( $catmapForm.find( '.wpfm-catmap-editor__footer button' ), false );
                // Saved values feed the next suggestions ("Used before"), so reload them next time.
                smartLoaded = false;
                catmapMaps[ catmapKey ] = { name: name, merchant: $catmapMerchant.val(), values: values };
                catmapUpsertListRow( catmapKey, name, catmapMerchantName( $catmapMerchant.val() ), catmapUpdateProgress() );

                if ( closeTab ) {
                    self.close();
                }
                catmapCloseModal( $catmapEditor );
                catmapNotice( catmapStrings.saved, 'success' );
            })
            .error( function( response ) {
                // Show the server's reason when it sent one (empty name, map deleted elsewhere).
                var data    = response && response.responseJSON && response.responseJSON.data;
                var message = data && 'string' === typeof data.message && data.message ? data.message : catmapStrings.save_failed;
                catmapBusy( $catmapForm.find( '.wpfm-catmap-editor__footer button' ), false );
                catmapNotice( message, 'error' );
                console.log( response.statusText );
            });

        return false;
    }
    $catmapForm.on( 'submit', catmapSave );
    $( document ).on( 'click', '#update_close_mapping_cat', catmapSave );

    // Delete: confirm first, then remove the row.
    $( document ).on( 'click', '.wpfm-catmap-delete', function( e ) {
        e.preventDefault();
        var $tr = $( this ).closest( 'tr' );
        catmapDeleting = $tr.attr( 'data-map-key' );
        $catmapDelete.find( '#wpfm-catmap-delete-text' ).text(
            catmapFormat( catmapStrings.delete_confirm, $.trim( $tr.find( '.wpfm-catmap-table__map-name' ).text() ) )
        );
        catmapOpenModal( $catmapDelete, $catmapDelete.find( '.wpfm-catmap-btn--ghost' ) );
    });

    $( document ).on( 'click', '.wpfm-catmap-delete-confirm', function( e ) {
        e.preventDefault();
        var $btn = $( this );
        var key  = catmapDeleting;

        if ( ! key ) {
            return;
        }
        catmapBusy( $btn, true, catmapStrings.deleting );

        wpAjaxHelperRequest( 'rexfeed-delete-category-mapping', { map_key: key, feed_id: $( '#post_ID' ).val() } )
            .success( function() {
                catmapBusy( $btn, false );
                catmapDeleting = '';
                delete catmapMaps[ key ];
                catmapLastFocus = $catmapRoot.find( '.wpfm-catmap__header .wpfm-catmap-open-add' )[0];
                catmapCloseModal( $catmapDelete );

                $catmapRoot.find( '.wpfm-catmap-table tbody tr' ).filter( function() {
                    return $( this ).attr( 'data-map-key' ) === key;
                }).remove();

                if ( ! $catmapRoot.find( '.wpfm-catmap-table tbody tr' ).length ) {
                    $catmapRoot.find( '.wpfm-catmap-list' ).prop( 'hidden', true );
                    $catmapRoot.find( '.wpfm-catmap-empty' ).prop( 'hidden', false );
                }
                catmapNotice( catmapStrings.deleted, 'success' );
            })
            .error( function( response ) {
                catmapBusy( $btn, false );
                catmapNotice( catmapStrings.delete_failed, 'error' );
                console.log( response.statusText );
            });
    });

    catmapRenderPage();
    catmapApplyMerchant();

    // Opened from the feed editor ("Configure Category Map"): edit that map
    // right away and offer to close the tab after saving.
    var catmapUrl = window.location.href;
    if ( catmapUrl.indexOf( 'wpfm-expand=' ) !== -1 ) {
        var expandKey = new URL( catmapUrl ).searchParams.get( 'wpfm-expand' );
        if ( expandKey && catmapMaps[ expandKey ] ) {
            $catmapForm.find( '#update_close_mapping_cat' ).prop( 'hidden', false );
            catmapOpenEditor( expandKey );
        }
    }

    // After adding a map, highlight it (newest is first) and clean the URL.
    if ( catmapUrl.indexOf( 'new-added=1' ) !== -1 ) {
        $catmapRoot.find( '.wpfm-catmap-table tbody tr' ).first().addClass( 'is-new' );
        catmapNotice( catmapStrings.saved, 'success' );
        window.history.replaceState( {}, '', catmapUrl.replace( '&new-added=1', '' ) );
    }
});
