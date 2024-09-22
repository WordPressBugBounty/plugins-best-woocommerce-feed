<?php

/**
 * Class for retriving product data based on user selected feed configuration.
 *
 * Get the product data based on feed config selected by user.
 *
 * @package    Rex_Product_Marktplaats_Data_Retriever
 * @subpackage Rex_Product_Feed/admin
 * @author     RexTheme <info@rextheme.com>
 */
if( class_exists( 'Rex_Product_Data_Retriever_Pro' ) ) {
    class Rex_Product_Glami_Data_Retriever extends Rex_Product_Data_Retriever_Pro {


        /**
         * Retrive and setup all data for every feed rules.
         *
         * @since    2.5
         */
        public function set_all_value() {
            $this->data = array();
            foreach( $this->feed_config as $key => $rule ) {
                if( array_key_exists( 'attr', $rule ) ) {
                    if( $rule[ 'attr' ] === 'IMGURL_ALTERNATIVE' ) {
                        $value = $this->set_val( $rule );
                        if( $value ) {
                            $this->data[ $rule[ 'attr' ] ][] = $value;
                        }
                    }
                    else {
                        $this->data[ $rule[ 'attr' ] ] = $this->set_val( $rule );
                    }
                }
                elseif( array_key_exists( 'cust_attr', $rule ) ) {
                    if( $rule[ 'cust_attr' ] ) {
                        $this->data[ $rule[ 'cust_attr' ] ] = $this->set_val( $rule );
                    }
                }
            }
        }


        public function get_random_key() {
            return md5( uniqid( rand(), true ) );
        }
    }
}
else {
    class Rex_Product_Glami_Data_Retriever extends Rex_Product_Data_Retriever {


        /**
         * Retrive and setup all data for every feed rules.
         *
         * @since    2.5
         */
        public function set_all_value() {
            $this->data = array();
            foreach( $this->feed_config as $key => $rule ) {
                if( array_key_exists( 'attr', $rule ) ) {
                    if( $rule[ 'attr' ] === 'IMGURL_ALTERNATIVE' ) {
                        $value = $this->set_val( $rule );
                        if( $value ) {
                            $this->data[ $rule[ 'attr' ] ][] = $value;
                        }
                    }
                    else {
                        $this->data[ $rule[ 'attr' ] ] = $this->set_val( $rule );
                    }
                }
                elseif( array_key_exists( 'cust_attr', $rule ) ) {
                    if( $rule[ 'cust_attr' ] ) {
                        $this->data[ $rule[ 'cust_attr' ] ] = $this->set_val( $rule );
                    }
                }
            }
        }


        public function get_random_key() {
            return md5( uniqid( rand(), true ) );
        }
    }
}