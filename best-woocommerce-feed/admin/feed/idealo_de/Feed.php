<?php

namespace RexTheme\RexShoppingFeedCustom\Idealo_de;

use RexTheme\RexShoppingFeed\Item;

class Feed extends \RexTheme\RexShoppingFeed\Feed
{
    protected $attributes;

    /**
     * Add Items to csv
     * @return array|Item[]
     */
	private function addItemsToFeedCSV() {
		$items_row = [];

		if ( !empty( $this->items ) ) {
			$items_row[] = array_keys( end( $this->items )->nodes() );
			foreach( $this->items as $item ) {
				$row = array();
				foreach( $item->nodes() as $itemNode ) {
					if ( is_array( $itemNode ) ) {
						foreach( $itemNode as $node ) {
							$row[] = str_replace( array( "\r\n", "\n", "\r" ), ' ', $node->get( 'value' ) );
						}
					}
					else {
						$row[] = str_replace( array( "\r\n", "\n", "\r" ), ' ', $itemNode->get( 'value' ) );
					}
				}
				$items_row[] = $row;
			}
		}

		return $items_row;
	}


	/**
	 * Generate CSV feed
	 *
	 * @param bool $output
	 * @return array|Item[]
	 */
	public function asCSVFeeds( $output = false ) {
		if ( ob_get_contents() )
			ob_end_clean();

		$data = $this->addItemsToFeedCSV();
		if ( $output ) {
			die( $data );
		}
		return $data;
	}
}
