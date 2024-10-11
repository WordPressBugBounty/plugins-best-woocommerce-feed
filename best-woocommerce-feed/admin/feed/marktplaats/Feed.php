<?php

namespace RexTheme\MarktPlaatsShoppingFeed;

use SimpleXMLElement;
use RexTheme\MarktPlaatsShoppingFeed\Item;
use Gregwar\Cache\Cache;

class Feed
{

    /**
     * Define Google Namespace url
     * @var string
     */
    protected $namespace ;

    /**
     * [$version description]
     * @var string
     */
    protected $version;

    /**
     * Stores the list of items for the feed
     * @var Item[]
     */
    protected $items = array();

    /**
     * Stores the list of items for the feed
     * @var Item[]
     */
    protected $items_row = array();

    /**
     * [$channelCreated description]
     * @var boolean
     */
    protected $channelName;


    /**
     * [$channelCreated description]
     * @var boolean
     */
    protected $itemlName;

    /**
     * [$channelCreated description]
     * @var boolean
     */
    protected $channelCreated = false;

    /**
     * The base for the feed
     * @var SimpleXMLElement
     */
    protected $feed = null;

    /**
     * [$title description]
     * @var string
     */
    protected $title = '';

    /**
     * [$cacheDir description]
     * @var string
     */
    protected $cacheDir = 'cache';

    /**
     * [$description description]
     * @var string
     */
    protected $description = '';

    /**
     * [$link description]
     * @var string
     */
    protected $link = '';


    protected $rss = 'rss';

    /**
     * Feed constructor
     */
    public function __construct($wrapper = false, $itemlName = 'item', $namespace = null, $version = '', $rss = 'rss')
    {

        $this->namespace   = $namespace;
        $this->version     = $version;
        $this->channelName = $wrapper;
        $this->itemlName   = $itemlName;
        $this->rss          = $rss;

        $namespace = $this->namespace && !empty($this->namespace) ? " xmlns:admarkt='$this->namespace'" : '';

        $this->feed = new SimpleXMLElement("<$rss $namespace ></$rss>");
    }

    /**
     * @param string $title
     */
    public function title($title)
    {
        $this->title = (string)$title;
    }

    /**
     * @param string $description
     */
    public function description($description)
    {
        $this->description = (string)$description;
    }

    /**
     * @param string $link
     */
    public function link($link)
    {
        $this->link = (string)$link;
    }

    /**
     * [channel description]
     */
    private function channel()
    {
        if (! $this->channelName) {
            $this->channelCreated = true;
            return;
        }
        if (! $this->channelCreated ) {
            $channel = $this->feed->addChild($this->channelName);
            ! $this->title       ?: $channel->addChild('title', $this->title);
            ! $this->link        ?: $channel->addChild('link', $this->link);
            ! $this->description ?: $channel->addChild('description', $this->description);
            $this->channelCreated = true;
        }
    }

    /**
     * @return Item
     */
    public function createItem()
    {

        $this->channel();
        $item = new Item($this->namespace);
        $index = 'index_' . md5(microtime());
        $this->items[$index] = $item;
        $item->setIndex($index);
        return $item;
    }

    /**
     * @param int $index
     */
    public function removeItemByIndex($index)
    {
        unset($this->items[$index]);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function standardiseSizeVarient($value)
    {
        return $value;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function standardiseColourVarient($value)
    {
        return $value;
    }

    /**
     * @param string $group
     * @return bool|string
     */
    public function isVariant($group)
    {
        if (preg_match("#^\s*colou?rs?\s*$#is", trim($group))) {
            return 'color';
        }
        if (preg_match("#^\s*sizes?\s*$#is", trim($group))) {
            return 'size';
        }
        if (preg_match("#^\s*materials?\s*$#is", trim($group))) {
            return 'material';
        }
        return false;
    }

    /**
     * Adds items to feed
     */
	private function addItemsToFeed() {
		$budgetNodes = [ 'totalBudget', 'dailyBudget', 'cpc', 'autobid' ];
		foreach ( $this->items as $item ) {
			/** @var SimpleXMLElement $feedItemNode */
			$feedItemNode = ! empty( $this->channelName ) ? $this->feed->{$this->channelName}->addChild( $this->itemlName ) : $this->feed->addChild( $this->itemlName );

			$shippingOptions = [];
            $attributes = [];

			foreach ( $item->nodes() as $itemNode ) {
				if ( is_array( $itemNode ) ) {
					foreach ( $itemNode as $node ) {
						$feedItemNode->addChild( str_replace( ' ', '_', $node->get( 'name' ) ),
							$node->get( 'value' ),
							$node->get( '_namespace' ) );
					}
				} else {
					$nodeName  = $itemNode->get( 'name' );
					$nodeValue = $itemNode->get( 'value' );
					if ( preg_match( '/^(shippingType|cost|time|location)_(\d+)$/', $nodeName, $matches ) ) {
						$shippingOptions[ $matches[ 2 ] ][ $matches[ 1 ] ] = $nodeValue;
					} else if ( preg_match( '/^(attributeValue|attributeName|attributeLocale|attributeLabel)_(\d+)$/', $nodeName, $matches ) ) {
                        $attributes[ $matches[ 2 ] ][ $matches[ 1 ] ] = $nodeValue;
                    } elseif ( 'media' === $nodeName ) {
						$media  = $feedItemNode->addChild( 'media' );
						$values = is_array( $nodeValue ) ? $nodeValue : [ $nodeValue ];
						foreach ( $values as $value ) {
							$image = $media->addChild( 'image' );
							$image->addAttribute( 'url', $value );
						}
					} elseif ( in_array( $nodeName, $budgetNodes ) ) {
						$budgetNode = $feedItemNode->children( 'admarkt',
							true )->budget ?? $feedItemNode->addChild( 'budget' );
						$budgetNode->addChild( $nodeName, $nodeValue );
					} else {
						$itemNode->attachNodeTo( $feedItemNode );
					}
				}
			}

			if ( ! empty( $shippingOptions ) ) {
				$shippingOptionsNode = $feedItemNode->addChild( 'shippingOptions' );
				foreach ( $shippingOptions as $option ) {
					$shippingOptionNode = $shippingOptionsNode->addChild( 'shippingOption' );
					foreach ( $option as $key => $value ) {
						$shippingOptionNode->addChild( $key, $value );
					}
				}
			}

            if ( ! empty( $attributes ) ) {
                $attributesNode = $feedItemNode->addChild( 'attributes' );
                foreach ( $attributes as $option ) {
                    $attributeNode = $attributesNode->addChild( 'attribute' );
                    foreach ( $option as $key => $value ) {
                        $attributeNode->addChild( $key, $value );
                    }
                }
            }
		}
	}

	private function addItemsToFeedText() {
        $str = '';
        if(count($this->items)){
            $this->items_row[] = array_keys(end($this->items)->nodes());
            foreach ($this->items as $item) {
                $row = array();
                foreach ($item->nodes() as $itemNode) {
                    if (is_array($itemNode)) {
                        foreach ($itemNode as $node) {
                            $row[] = str_replace(array("\r\n", "\n", "\r"), ' ', $node->get('value'));
                        }
                    } else {
                        $row[] = str_replace(array("\r\n", "\n", "\r"), ' ', $itemNode->get('value'));
                    }
                }
                $this->items_row[] = $row;
            }
            foreach ($this->items_row as $fields) {
                $str .= implode("\t", $fields) . "\n";
            }
        }
        return $str;
    }

    private function addItemsToFeedCSV(){

        if(count($this->items)){

            $this->items_row[] = array_keys(end($this->items)->nodes());
            foreach ($this->items as $item) {
                $row = array();
                foreach ($item->nodes() as $itemNode) {
                    if (is_array($itemNode)) {
                        foreach ($itemNode as $node) {
                            $row[] = str_replace(array("\r\n", "\n", "\r"), ' ', $node->get('value'));
                        }
                    } else {
                        $row[] = str_replace(array("\r\n", "\n", "\r"), ' ', $itemNode->get('value'));
                    }
                }
                $this->items_row[] = $row;
            }

            $str = '';
            foreach ($this->items_row as $fields) {
                $str .= implode("\t", $fields) . "\n";
            }
        }

        return $this->items_row;
    }

    /**
     * Retrieve Google product categories from internet and cache the result
     * @return array
     */
    public function categories()
    {
        $cache = new Cache;
        $cache->setCacheDirectory($this->cacheDir);
        $data = $cache->getOrCreate('google-feed-taxonomy.txt', array( 'max-age' => '86400' ), function () {
            $request = wp_remote_get( "http://www.google.com/basepages/producttype/taxonomy.en-GB.txt" );
            if( is_wp_error( $request ) ) {
                return false;
            }
            $body = wp_remote_retrieve_body( $request );
            return json_decode( $body );
        });
        return explode("\n", trim($data));
    }

    /**
     * Build an HTML select containing Google taxonomy categories
     * @param string $selected
     * @return string
     */
    public function categoriesAsSelect($selected = '')
    {
        $categories = $this->categories();
        unset($categories[0]);
        $select = '<select name="google_category">';
        $select .= '<option value="">'.__( 'Please select a Google Category', 'rex-product-feed' ).'</option>';
        foreach ($categories as $category) {
            $select .= '<option ' . ($category == $selected ? 'selected' : '') . ' name="' . $category . '">' . $category . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    /**
     * Generate RSS feed
     * @param bool $output
     * @param string/bool $merchant
     * @return string
     */
    public function asRss($output = false)
    {
        if (ob_get_contents()) ob_end_clean();
        $this->addItemsToFeed();

        $data = $this->feed->asXml();
        if ($output) {
            header('Content-Type: application/xml; charset=utf-8');
            die($data);
        }

        return $data;
    }

    /**
     * Generate Txt feed
     * @param bool $output
     * @return string
     */
    public function asTxt($output = false)
    {
        ob_end_clean();
        $data = $this->addItemsToFeedText();
        if ($output) {
            die($data);
        }
        return $data;
    }

    /**
     * Generate CSV feed
     * @param bool $output
     * @return string
     */
    public function asCsv($output = false)
    {

        ob_end_clean();
        $data = $this->addItemsToFeedCSV();
        if ($output) {
            die($data);
        }
        return $data;
    }


    /**
     * Remove last inserted item
     */
    public function removeLastItem()
    {
        array_pop($this->items);
    }
}
