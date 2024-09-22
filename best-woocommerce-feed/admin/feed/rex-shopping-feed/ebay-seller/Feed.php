<?php

namespace RexTheme\RexShoppingFeedCustom\EbaySellerFeed;
use RexTheme\RexShoppingFeed\Item;
use SimpleXMLElement;

class EbaySellerFeed extends \RexTheme\RexShoppingFeed\Feed
{

    /**
     * Add Items to csv
     * @return array|\RexTheme\RexShoppingFeed\Item[]
     */
    private function addItemsToFeedCSV(){
        
        if(count($this->items)){
            $this->items_row[] = array_map('ucfirst', array_keys(end($this->items)->nodes()));
            
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
            
        }
        
        return $this->items_row;
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

}
