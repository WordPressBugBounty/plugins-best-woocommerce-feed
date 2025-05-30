<?php

namespace RexTheme\RexShoppingZbozi;

use SimpleXMLElement;
use RexTheme\RexShoppingZbozi\Item;
use Gregwar\Cache\Cache;

class Feed
{

    static $keys = array(
        'PRODUCTNAME',
        'DESCRIPTION',
        'URL',
        'PRICE_VAT',
        'DELIVERY_DATE',
        'CATEGORYTEXT',
        'ITEM_ID',
        'IMGURL',
        'EAN',
        'ISBN',
        'PRODUCTNO',
        'ITEMGROUP_ID',
        'MANUFACTURER',
        'EROTIC',
        'EXTRA_MESSAGE',
        'CUSTOM_LABEL_0',
        'CUSTOM_LABEL_1',
        'CUSTOM_LABEL_2',
        'BRAND',
        'SHOP_DEPOTS',
        'VISIBILITY',
        'MAX_CPC',
        'MAX_CPC_SEARCH',
        'LIST_PRICE',
        'RELEASE_DATE',
        'IMGURL_ALTERNATIVE',
        'DELIVERY',
        'PRICE_BEFORE_DISCOUNT',
        'CONDITION',
        'CONDITION_DESC',
        'WARRANTY',
        'PRODUCTNO',
        'item_group_id',
    );

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

    public $delivery = '';

    /**
     * Feed constructor
     */
    public function __construct($wrapper = false, $itemlName = 'item', $namespace = null, $version = '', $rss = 'rss', $stand_alone = false, $wrapperel = '', $namespace_prefix = '')
    {
        $this->namespace   = 'http://www.zbozi.cz/ns/offer/1.0';
        $this->version     = $version;
        $this->wrapper     = $wrapper;
        $this->channelName = $wrapperel;
        $this->itemlName   = $itemlName;
        $this->rss         = $rss;

        $namespace = $this->namespace && !empty($this->namespace) ? " xmlns{$namespace_prefix}='$this->namespace'" : '';
        $version   = $this->version && !empty($this->version) ? " version='$this->version'" : '';
        $stand_alone_text = $stand_alone ? 'standalone="yes"' : '';
        // $xmlns = "http://www.zbozi.cz/ns/offer/1.0";
        $this->feed = new SimpleXMLElement("<$rss $namespace $stand_alone_text $version ></$rss>");
        // $this->feed = new SimpleXMLElement('<$rss '.$namespace.' ></$rss>');
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
        $s_nodes = array('PARAM');
        foreach ($this->items as $item) {
            /** @var SimpleXMLElement $feedItemNode */
            if ( $this->channelName && !empty($this->channelName) ) {
                $feedItemNode = $this->feed->{$this->channelName}->addChild($this->itemlName);
            }else{
                $feedItemNode = $this->feed->addChild($this->itemlName);
            }

            foreach ($item->nodes() as $itemNode) {
                if ( ! in_array($itemNode->get('name'), self::$keys ) && !stristr( $itemNode->get( 'name' ), 'Delivery_' )) {
                    $value = $itemNode->get('value');
                    $param = $feedItemNode->addChild('PARAM');
                    $param->addChild('PARAM_NAME', $itemNode->get('name'));
                    $param->addChild('VAL', $value);
                } elseif ( stristr( $itemNode->get( 'name' ), 'Delivery_id_' ) ) {
                    $this->delivery = $feedItemNode->addChild( 'DELIVERY' );
                    $this->delivery->addChild( 'DELIVERY_ID', $itemNode->get( 'value' ) );
                } elseif ( stristr( $itemNode->get( 'name' ), 'Delivery_price_' ) && !stristr( $itemNode->get( 'name' ), 'Delivery_price_cod_' ) ) {
                    $this->delivery->addChild( 'DELIVERY_PRICE', $itemNode->get( 'value' ) );
                } elseif ( stristr( $itemNode->get( 'name' ), 'Delivery_price_cod_' ) ) {
                    $this->delivery->addChild( 'DELIVERY_PRICE_COD', $itemNode->get( 'value' ) );
                } elseif($itemNode->get('name') != 'item_group_id' && $itemNode->get('name') != 'PARAM') {
                    if (is_array($itemNode)) {
                        foreach ($itemNode as $node) {
                            $feedItemNode->addChild(str_replace(' ', '_', $node->get('name')), $node->get('value'), $node->get('_namespace'));
                        }
                    } else {
                        $itemNode->attachNodeTo($feedItemNode);
                    }
                } else{
                    $feedItemNode->addChild('ITEMGROUP_ID',$itemNode->get('value'));
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

//        $data = html_entity_decode($this->feed->asXml());
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
