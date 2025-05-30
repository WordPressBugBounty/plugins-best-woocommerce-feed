<?php

namespace LukeSnowden\GoogleShoppingFeed;

use SimpleXMLElement;
use LukeSnowden\GoogleShoppingFeed\Item;
use Gregwar\Cache\Cache;

class Feed
{

    /**
     * Define Google Namespace url
     * @var string
     */
    protected $namespace = 'http://base.google.com/ns/1.0';

    /**
     * @var string
     */
    protected $version = '2.0';

    /**
     * @var string
     */
    protected $iso4217CountryCode = 'GBP';

    /**
     * Stores the list of items for the feed
     * @var Item[]
     */
    private $items = array();

    /**
     * @var bool
     */
    private $channelCreated = false;

    /**
     * The base for the feed
     * @var SimpleXMLElement
     */
    private $feed = null;

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var string
     */
    private $cacheDir = 'cache';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var string
     */
    private $link = '';

    /**
     * @var string
     */
    private $certification = '';

    /**
     * Feed constructor
     */
    public function __construct()
    {
        $this->feed = new SimpleXMLElement('<rss xmlns:g="' . $this->namespace . '" version="' . $this->version . '"></rss>');
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
     * @param $code
     */
    public function setIso4217CountryCode( $code )
    {
        $this->iso4217CountryCode = $code;
    }

    /**
     * @return string
     */
    public function getIso4217CountryCode()
    {
        return $this->iso4217CountryCode;
    }

    /**
     * [channel description]
     */
    private function channel()
    {
        if (! $this->channelCreated) {
            $channel = $this->feed->addChild('channel');
            $channel->addChild('title', htmlspecialchars($this->title));
            $channel->addChild('link', htmlspecialchars($this->link));
            $channel->addChild('description', htmlspecialchars($this->description));
            $this->channelCreated = true;
        }
    }

    /**
     * @return Item
     */
    public function createItem()
    {
        $this->channel();
        $item = new Item($this);
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
            $feedItemNode = $this->feed->channel->addChild('item');
            foreach ($item->nodes() as $itemNode) {
                if (is_array($itemNode)) {
                    foreach ($itemNode as $node) {
                        $feedItemNode->addChild($node->get('name'), $node->get('value'), $node->get('_namespace'));
                    }
                }
                elseif( stristr( $itemNode->get( 'name' ), 'product_highlight_' ) ) {
                    $feedItemNode->addChild( 'product_highlight', $itemNode->get( 'value' ), $itemNode->get('_namespace') );
                }elseif ( stristr( $itemNode->get( 'name' ), 'certification_authority_' ) ) {
                    $this->certification = $feedItemNode->addChild( 'certification', '', $itemNode->get('_namespace')  );
                    $this->certification->addChild( 'certification_authority', $itemNode->get( 'value' ), $itemNode->get('_namespace')  );
                } elseif ( stristr( $itemNode->get( 'name' ), 'certification_name_' ) ) {
                    $this->certification->addChild( 'certification_name', $itemNode->get( 'value' ), $itemNode->get('_namespace')  );
                } elseif ( stristr( $itemNode->get( 'name' ), 'certification_code_' ) ) {
                    $this->certification->addChild( 'certification_code', $itemNode->get( 'value' ), $itemNode->get('_namespace')  );
                }
                else {
                    $itemNode->attachNodeTo($feedItemNode);
                }
            }
        }
    }

    private function addItemsToFeedText() {
        $str = '';
        if(count($this->items)){
            $items_row[] = array_keys(end($this->items)->nodes());
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
                $items_row[] = $row;
            }
            foreach ($items_row as $fields) {
                $str .= implode("\t", $fields) . "\n";
            }
        }
        return $str;
    }

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
     * @param string $languageISO639
     * @return array
     */
    public function categories($languageISO639 = 'gb')
    {
        //map two letter language to culture
        $languageMap = array(
            'au' => 'en-AU',
            'br' => 'pt-BR',
            'cn' => 'zh-CN',
            'cz' => 'cs-CZ',
            'de' => 'de-DE',
            'dk' => 'da-DK',
            'es' => 'es-ES',
            'fr' => 'fr-FR',
            'gb' => 'en-GB',
            'it' => 'it-IT',
            'jp' => 'ja-JP',
            'nl' => 'nl-NL',
            'no' => 'no-NO',
            'pl' => 'pl-PL',
            'ru' => 'ru-RU',
            'sw' => 'sv-SE',
            'tr' => 'tr-TR',
            'us' => 'en-US'
        );
        //set default language to gb for backward compatibility
        $languageCulture = $languageMap['gb'];
        if (array_key_exists($languageISO639, $languageMap)) {
            $languageCulture = $languageMap[$languageISO639];
        }

        $cache = new Cache;
        $cache->setCacheDirectory($this->cacheDir);
        $data = $cache->getOrCreate('google-feed-taxonomy.'.$languageISO639.'.txt', array('max-age' => '86400'),
            function () use ($languageCulture) {
                $request = wp_remote_get( "http://www.google.com/basepages/producttype/taxonomy." . $languageCulture . ".txt" );
                if( is_wp_error( $request ) ) {
                    return false;
                }
                $body = wp_remote_retrieve_body( $request );
                return json_decode( $body );
            }
        );

        return explode("\n", trim($data));
    }

    /**
     * Build an HTML select containing Google taxonomy categories
     * @param string $selected
     * @param string $languageISO639
     * @return string
     */
    public function categoriesAsSelect($selected = '', $languageISO639 = 'gb')
    {
        $categories = $this->categories($languageISO639);
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
     * @param string $languageISO639
     * @return array
     */
    public function categoriesAsNameAssociativeArray( $languageISO639 = 'gb' )
    {
        $categories = $this->categories($languageISO639);
        unset($categories[0]);
        $return = [];
        foreach( $categories as $key => $value ) {
            $return[$value] = $value;
        }
        return $return;
    }

    /**
     * Generate RSS feed
     * @param bool $output
     * @return string
     */
    public function asRss($output = false)
    {
        if (ob_get_contents()) ob_end_clean();
        $this->addItemsToFeed();
        $data = html_entity_decode($this->feed->asXml());
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
        if (ob_get_contents()) ob_end_clean();
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
