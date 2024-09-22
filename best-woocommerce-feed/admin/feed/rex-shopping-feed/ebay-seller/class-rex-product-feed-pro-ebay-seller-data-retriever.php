

<?php

/**
 * Class for retriving product data based on user selected feed configuration.
 *
 * Get the product data based on feed config selected by user.
 *
 * @package    Rex_Product_Ebay_Seller_Data_Retriever
 * @subpackage Rex_Product_Feed/admin
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Product_Ebay_Seller_Data_Retriever extends Rex_Product_Data_Retriever {

    /**
     * @var $ebay_seller_config
     */
    protected $ebay_seller_config;

    protected $merchant;

    public function __construct( WC_Product $product, Rex_Product_Feed_Abstract_Generator $feed, $product_meta_keys, $ebay_seller_config ) {
        $this->ebay_seller_config = $ebay_seller_config;
        $this->merchant = 'ebay_seller';
        parent::__construct( $product, $feed, $product_meta_keys );
    }

    /**
     * Retrive and setup all data for every feed rules.
     *
     * @since    3.0
     */
    public function set_all_value() {
        $this->data = array();
        foreach ($this->feed_config as $key => $rule) {
            if(isset($rule['attr'])) {
                if($rule['attr'] === '*Action') {
                    if(!array_key_exists('site_id', $this->ebay_seller_config) ) {
                        $this->ebay_seller_config['site_id'] = '';
                    }
                    if(!array_key_exists('country', $this->ebay_seller_config) ) {
                        $this->ebay_seller_config['country'] = '';
                    }
                    if(!array_key_exists('currency', $this->ebay_seller_config) ) {
                        $this->ebay_seller_config['currency'] = '';
                    }
                    if($this->merchant === 'ebay_seller') {
                        $this->data["*Action(SiteID={$this->ebay_seller_config['site_id']}|Country={$this->ebay_seller_config['country']}|Currency={$this->ebay_seller_config['currency']}|Version=941)"] = $this->set_val( $rule );
                    }else {
                        $this->data["*Action(SiteID={$this->ebay_seller_config['site_id']}|Country={$this->ebay_seller_config['country']}|Currency={$this->ebay_seller_config['currency']}|Version=941|UseCatalogTitle=1|TemplateName=TicketCatalog|ProductIdType=Keywords|Duration=7|Format=FixedPrice|PayPalAccepted=1|StockPhoto=1)"] = $this->set_val( $rule );
                    }
                }else {
                    $this->data[ $rule['attr'] ] = $this->set_val( $rule );
                }
            } elseif (isset($rule['cust_attr'])) {
                $this->data[ $rule['cust_attr'] ] = $this->set_val( $rule );
            }
        }
    }
}