<?php

namespace RexTheme\RexShoppingFeed;

use SimpleXMLElement;
use RexTheme\RexShoppingFeed\Item;
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

    protected $paramNode = null;

    /**
     * Feed constructor
     */
    public function __construct($wrapper = false, $itemlName = 'item', $namespace = null, $version = '', $rss = 'rss', $stand_alone = false, $wrapperel = '', $namespace_prefix = '')
    {
        $this->namespace   = $namespace;
        $this->version     = $version;
        $this->wrapper     = $wrapper;
        $this->channelName = $wrapperel;
        $this->itemlName   = $itemlName;
        $this->rss         = $rss;

        $namespace = $this->namespace && !empty($this->namespace) ? " xmlns{$namespace_prefix}='$this->namespace'" : '';
        $version   = $this->version && !empty($this->version) ? " version='$this->version'" : '';
        $stand_alone_text = $stand_alone ? 'standalone="yes"' : '';
         $this->feed = new SimpleXMLElement("<$rss $namespace $stand_alone_text $version ></$rss>");
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
        foreach ($this->items as $item) {
            /** @var SimpleXMLElement $feedItemNode */
            if ($this->channelName && !empty($this->channelName)) {
                $feedItemNode = $this->feed->{$this->channelName}->addChild($this->itemlName);
            } else {
                $feedItemNode = $this->feed->addChild($this->itemlName);
            }

            $paramNodes = []; // Temporary array to store param name-value pairs

            foreach ($item->nodes() as $itemNode) {
                if (is_array($itemNode)) {
                    foreach ($itemNode as $node) {
                        $feedItemNode->addChild(str_replace(' ', '_', $node->get('name')), $node->get('value'), $node->get('_namespace'));
                    }
                } elseif (str_starts_with($itemNode->get('name'), 'param_value_')) {
                    $paramIndex = str_replace('param_value_', '', $itemNode->get('name'));
                    if (!empty($itemNode->get('value'))) {
                        $paramNodes[$paramIndex]['value'] = $itemNode->get('value');
                    }
                } elseif (str_starts_with($itemNode->get('name'), 'param_name_')) {
                    $paramIndex = str_replace('param_name_', '', $itemNode->get('name'));
                    if (!empty($itemNode->get('value'))) {
                        $paramNodes[$paramIndex]['name'] = $itemNode->get('value');
                    }
                } else {
                    // For Skroutz feed template Variation attributes.
                    if ('variations' === $itemNode->get('name')) {
                        $variations = $itemNode->get('value');
                        if (is_array($variations) && !empty($variations)) {
                            $variationsItemNode = $feedItemNode->addChild('variations');
                            foreach ($variations as $attributes) {
                                $variationItemNode = $variationsItemNode->addChild('variation');
                                foreach ($attributes as $key => $value) {
                                    if ('id' === $key) {
                                        $key = 'variationid';
                                    }
                                    $variationItemNode->addChild($key, htmlspecialchars($value));
                                }
                            }
                        }
                    } else {
                        $feedItemNode->addChild($itemNode->get('name'), htmlspecialchars($itemNode->get('value')), $itemNode->get('_namespace'));
                    }
                }
            }

            // Only process paramNodes if it contains data
            if (!empty($paramNodes)) {
                foreach ($paramNodes as $param) {
                    if (isset($param['name']) && isset($param['value'])) {
                        $paramNode = $feedItemNode->addChild('param', htmlspecialchars($param['value']));
                        $paramNode->addAttribute('name', htmlspecialchars($param['name']));
                    }
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
     * add items to text feed
     *
     * @return string
     */
    private function addItemsToFeedTextPipe() {
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
                $str .= implode("|", $fields) . "\n";

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
     * Generate Txt feed
     * @param bool $output
     * @return string
     */
    public function asTxtPipe($output = false)
    {
        if (ob_get_contents()) ob_end_clean();
        $str = $this->addItemsToFeedTextPipe();
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
