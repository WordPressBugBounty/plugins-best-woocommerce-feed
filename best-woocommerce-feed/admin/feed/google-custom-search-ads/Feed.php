<?php

namespace RexTheme\RexShoppingGoogleCustomSearchAds;

use Gregwar\Cache\Cache;
use SimpleXMLElement;


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

    /**
     * Feed constructor
     */
    public function __construct($wrapper = false, $itemlName = 'item', $namespace = null, $version = '', $rss = 'rss', $stand_alone = false, $wrapperel = '')
    {
        $this->namespace = $namespace;
        $this->version = $version;
        $this->wrapper = $wrapper;
        $this->channelName = $wrapperel;
        $this->itemlName = $itemlName;
        $this->rss = $rss;

        $namespace = " xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'";
        $version = $this->version && !empty($this->version) ? " version='$this->version'" : '';
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
        if (!$this->wrapper) {
            $this->channelCreated = true;
            return;
        }
        if (!$this->channelCreated) {
            $channel = $this->channelName ? $this->feed->addChild($this->channelName) : $this->feed;
            !$this->title ?: $channel->addChild('title', $this->title);
            !$this->link ?: $channel->addChild('link', $this->link);
            !$this->description ?: $channel->addChild('description', $this->description);
            !$this->datetime ?: $channel->addChild('datetime', $this->datetime);
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

        $count = 0;
        $attrNode = array();
        $childNode = array();
        $imageNode = array();
        $otherAttrNode = array();

        foreach ($this->items as $item) {
            foreach ($item->nodes() as $itemNode) {
                if ($itemNode->get('name') === 'id' || $itemNode->get('name') === 'url' || $itemNode->get('name') === 'price' ||
                    $itemNode->get('name') === 'avail' || $itemNode->get('name') === 'stock' || $itemNode->get('name') === 'weight' ||
                    $itemNode->get('name') === 'basket') {
                    array_push($attrNode, $itemNode);

                } else if ($itemNode->get('name') == 'image_link' || $itemNode->get('name') == 'additional_image_link') {
                    array_push($imageNode, $itemNode);
                } else if ($itemNode->get('name') == 'cat' || $itemNode->get('name') == 'name' || $itemNode->get('name') == 'desc') {
                    array_push($childNode, $itemNode);
                } else {
                    array_push($otherAttrNode, $itemNode);
                }
            }
        }

        foreach ($this->items as $item) {
            $count = 0;
            $count2 = 0;

            /** @var SimpleXMLElement $feedItemNode */
            if ($this->channelName && !empty($this->channelName)) {

                $feedItemNode = $this->feed->{$this->channelName}->addChild($this->itemlName);
            } else {

                $feedItemNode = $this->feed->addChild($this->itemlName);
            }
            foreach ($item->nodes() as $itemNode) {
                if (is_array($itemNode)) {
                    foreach ($itemNode as $node) {
                        $feedItemNode->addChild(str_replace(' ', '_', $node->get('name')), $node->get('value'), $node->get('_namespace'));
                    }
                } else {
                    if ($itemNode->get('name') == 'id') {
                        $feedItemNode->addAttribute('id', $itemNode->get('value'));
                    } elseif ($itemNode->get('name') == 'url') {
                        $feedItemNode->addAttribute('url', $itemNode->get('value'));
                    } elseif ($itemNode->get('name') == 'price') {
                        $feedItemNode->addAttribute('price', $itemNode->get('value'));
                    } elseif ($itemNode->get('name') == 'avail') {
                        $feedItemNode->addAttribute('avail', $itemNode->get('value'));
                    } elseif ($itemNode->get('name') == 'stock') {
                        $feedItemNode->addAttribute('stock', $itemNode->get('value'));
                    } elseif ($itemNode->get('name') == 'basket') {
                        $feedItemNode->addAttribute('basket', $itemNode->get('value'));
                    } elseif ($itemNode->get('name') == 'weight') {
                        $feedItemNode->addAttribute('weight', $itemNode->get('value'));
                    } elseif ($itemNode->get('name') == 'image_link' || $itemNode->get('name') == 'additional_image_link') {
                        $count++;
                        if ($count === 1) {
                            $image = $feedItemNode->addChild('imgs');
                            $main = $image->addChild('main');
                            $i = $image->addChild('i');
                            //$main_ad_im = $image->addChild('additional_img');
                        }
                        if ($itemNode->get('name') == 'image_link') {
                            $main->addAttribute('url', $itemNode->get('value'));
                            //$i->addAttribute('url', $itemNode->get('value'));
                        } elseif ($itemNode->get('name') == 'additional_image_link') {
                            //$main_ad_im->addAttribute('url', $itemNode->get('value'));
                            $i->addAttribute('url', $itemNode->get('value'));
                        }
                        //$feedItemNode->addAttribute('stock', $itemNode->get('value'));

                    } elseif (in_array($itemNode, $otherAttrNode)) {
                        $count2++;
                        if ($count2 === 1) {
                            $attr = $feedItemNode->addChild('attrs');
                        }

                        $a_tag = $attr->addChild('a', '<![CDATA [' . $itemNode->get('value') . ']]>');
                        $a_tag->addAttribute('name', $itemNode->get('name'));
//                        $a_tag->addChild('value','<![CDATA ['.$itemNode->get('value').']]>');
                        //$a_tag->addChild($itemNode->get('name'), '<![CDATA ['.$itemNode->get('value').']]>');


//                        $a_tag = $attr->addChild('a', '<![CDATA ['.$itemNode->get('value').']]>');
//                        $a_tag->addAttribute('name', $itemNode->get('name'));
                    } else {
                        $itemNode->attachNodeTo($feedItemNode);
                    }
                }
            }
        }
    }

    private function addItemsToFeedText()
    {
        $str = '';
        if (count($this->items)) {
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

    /*private function addItemsToFeedCSV()
    {
        if (count($this->items)) {
            $checkcustom=0;
            $checkheader=0;
            $this->items_row[] = array_keys(end($this->items)->nodes());
            $header = [];

            foreach ($this->items as $item) {
                $row = array();
                $_temp_label = [];
                foreach ($item->nodes() as $itemNode) {
                    if (is_array($itemNode)) {
                        foreach ($itemNode as $node) {
                            $row[] = str_replace(array("\r\n", "\n", "\r"), ' ', $node->get('value'));
                        }
                    } else {
                        $name = $itemNode->get('name');
                        if ($name === 'Product_URL' || $name === 'id' || $name === 'description' || $name === 'sku') {
                            $row[] = str_replace(array("\r\n", "\n", "\r"), ' ', $itemNode->get('value'));
                            if($checkheader===0){
                                $header[] = $name;
                            }

                        } elseif($name !== 'item_group_id' ) {
                            $_temp_label[] = $itemNode->get('value');
                            $checkcustom++;
                        }
                    }
                }
                $checkheader++;
                $row[] = implode(';', $_temp_label);
                $this->items_row[] = $row;
            }
        }

        if($checkcustom!==0){
            $header [] = 'Custom Label';
        }
        $this->items_row[0] = $header;
        return $this->items_row;
    }*/
    private function addItemsToFeedCSV(){
        $items_row = array();
        if(count($this->items)){
            $items_row[] = array_keys(end($this->items)->nodes());
            if(!in_array('item_group_id', $items_row[0])) $items_row[0][] = 'item_group_id';
            $length = count($items_row[0]);
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
                if((count($row)+1) == $length) {
                    $row[$length-1] = '';
                }
                $items_row[] = $row;
            }

            $str = '';
            foreach ($items_row as $fields) {
                if(!$fields[$length-1]) {
                    $str .= implode("\t", $fields) . ",\n";
                }else {
                    $str .= implode("\t", $fields) . "\n";
                }
            }
        }
        return $items_row;
    }

    /**
     * Retrieve Google product categories from internet and cache the result
     * @return array
     */
    public function categories()
    {
        $cache = new Cache;
        $cache->setCacheDirectory($this->cacheDir);
        $data = $cache->getOrCreate('google-feed-taxonomy.txt', array('max-age' => '86400'), function () {
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
        $this->addItemsToFeedText();
        $data = html_entity_decode($this->feed->asXml());

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
