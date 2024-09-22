<?php

namespace RexTheme\RexShoppingMirakl;

use SimpleXMLElement;
use RexTheme\RexShoppingMirakl\Item;
use Gregwar\Cache\Cache;

class Feed
{

    /**
     * Define Google Namespace url
     * @var string
     */
    protected $namespace;

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
    protected $wrapper;

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


    /**
     * [$datetime]
     * @var string
     */
    protected $datetime = '';


    protected $rss = 'rss';


    protected $stand_alone = false;
    protected $attr;
    protected $additional_offer_info;
    protected $product;

    /**
     * Feed constructor the initializes the value
     *
     * @param bool|string $wrapper XML Feed Wrapper.
     * @param string $itemlName Attribute item name.
     * @param string|null|bool $namespace Namespace.
     * @param string|int|float $version XML version.
     * @param string $rss RSS tag.
     * @param bool|string $stand_alone Stand alone.
     * @param string $wrapperel XML Wrapper element.
     * @param string $namespace_prefix Prefix for namespace.
     *
     * @return void
     * @since 7.2.35
     */
    public function __construct( $wrapper = false, $itemlName = 'item', $namespace = null, $version = '', $rss = 'rss', $stand_alone = false, $wrapperel = '', $namespace_prefix = '' ) {
        $this->namespace   = $namespace;
        $this->version     = $version;
        $this->wrapper     = $wrapper;
        $this->channelName = $wrapperel;
        $this->itemlName   = $itemlName;
        $this->rss         = $rss;

        $this->feed = new SimpleXMLElement('<import></import>');
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
     * @param string $link
     */
    public function datetime($datetime)
    {
        $this->datetime = (string)$datetime;
    }

    /**
     * [channel description]
     */
    private function channel()
    {
        if (! $this->wrapper) {
            $this->channelCreated = true;
            return;
        }
        if (! $this->channelCreated ) {
            $channel = $this->channelName ? $this->feed->addChild($this->channelName) : $this->feed;
            ! $this->title       ?: $channel->addChild('title', $this->title);
            ! $this->link        ?: $channel->addChild('link', $this->link);
            ! $this->description ?: $channel->addChild('description', $this->description);
            ! $this->datetime ?: $channel->addChild('datetime', $this->datetime);
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
    private function addItemsToFeed()
    {
        $productNodes = $this->feed->addChild('products');
        $feedItemNodes = $this->feed->addChild('offers');

        foreach ($this->items as $item) {
            $nodes = $item->nodes();
            $this->product = $productNodes->addChild( 'product' );
            $feedItemNode = $feedItemNodes->addChild('offer');

            if( !empty( $nodes ) ) {
                foreach( $nodes as $itemNode ) {
                    if( is_array( $itemNode->get( 'value' ) ) ) {
                        if( !empty( $itemNode->get( 'value' ) ) ) {
                            if( preg_match( '/Channel [0-9] Prices$/', $itemNode->get( 'name' ) ) ) {
                                $all_prices      = 'all-prices';
                                $all_prices_node = $feedItemNode->$all_prices ?: $feedItemNode->addChild( $all_prices );
                                $prices_node     = $all_prices_node->addChild( 'pricing' );
                                foreach( $itemNode->get( 'value' ) as $key => $value ) {
                                    $prices_node->addChild( $key, $value );
                                }
                            }
                        }
                    }
                    else {
                        if( stristr( $itemNode->get( 'name' ), 'attribute_' ) ) {

                            if( stristr( $itemNode->get( 'name' ), 'attribute_code_' ) ) {
                                $this->attr = $this->product->addChild( 'attribute' );
                                $this->attr->addChild( 'code', $itemNode->get( 'value' ) );
                            }
                            else {
                                $this->attr->addChild( 'value', $itemNode->get( 'value' ) );
                            }
                        }
                        elseif( !stristr( $itemNode->get( 'name' ), 'offer_additional_field_code_' ) && !stristr( $itemNode->get( 'name' ), 'offer_additional_field_value' ) ) {
                            $itemNode->attachNodeTo( $feedItemNode );
                        }
                    }
                }

                $offer_additional = 'offer-additional-fields';
                $feed_item_node_offer = $feedItemNode->addChild( $offer_additional );

                foreach( $nodes as $itemNode ) {
                    if( !is_array( $itemNode ) && stristr( $itemNode->get( 'name' ), 'offer_additional_field_' ) ) {
                        if( stristr( $itemNode->get( 'name' ), 'offer_additional_field_code_' ) ) {
                            $this->additional_offer_info = $feed_item_node_offer->addChild( 'offer-additional-field' );
                            $this->additional_offer_info->addChild( 'code', $itemNode->get( 'value' ) );
                        }
                        else {
                            $this->additional_offer_info->addChild( 'value', $itemNode->get( 'value' ) );
                        }
                    }
                }

                if( empty( $feed_item_node_offer ) ) {
                    unset( $feedItemNode->$offer_additional );
                }
            }
        }
    }


    /**
     * add items to text feed
     *
     * @return string
     */
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

    /**
     * add items to csv feed
     *
     * @return Item[]
     */
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
     * add items to json feed
     *
     * @return Item[]
     */
    private function addItemsToFeedJSON(){

        if(count($this->items)){
            $this->items_row[] = array_keys(end($this->items)->nodes());
            foreach ($this->items as $item) {
                $row = array();
                foreach ($item->nodes() as $itemNode) {
//                    if($itemNode->get)
                }
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

        if (ob_get_contents()) ob_end_clean();
        $str = $this->addItemsToFeedText();

        return $str;
    }

    /**
     * Generate CSV feed
     * @param bool $output
     * @return string
     */
    public function asCsv($output = false)
    {

        if (ob_get_contents()) ob_end_clean();
        $data = $this->addItemsToFeedCSV();
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
    public function asJSON($output = false)
    {

        if (ob_get_contents()) ob_end_clean();
        $data = $this->addItemsToFeedJSON();
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
