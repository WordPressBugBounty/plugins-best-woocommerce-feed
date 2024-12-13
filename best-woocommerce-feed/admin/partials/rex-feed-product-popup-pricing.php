<?php
/**
 * This file is responsible for displaying warning message after making any changes in filters drawer
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partialsgit 
 */
?>
<!-- `rex-premium-feature` block -->
<section class="rex-premium-feature" id="rex_premium_feature_popup" style="display: none">
    <div class="rex-premium-feature__wrapper">
        <!-- `rex-premium-feature__body` element in the `rex-premium-feature` block  -->
            <span class="rex-premium-feature__close-btn" id="rex_premium_feature_close">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                    <g clip-path="url(#clip0_1_11)">
                        <path d="M16.5 5.5L5.5 16.5" stroke="#A8B3C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M5.5 5.5L16.5 16.5" stroke="#A8B3C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </g>
                    <defs>
                        <clipPath id="clip0_1_11">
                        <rect width="22" height="22" fill="white"></rect>
                        </clipPath>
                    </defs>
                </svg>
			</span>

        <div class="rex-premium-feature__body">

            <!-- `rex-premium-feature__message` element in the `rex-premium-feature` block  -->
            <div class="rex-premium-feature__message">

                <span class="rex-premium-feature__svg-icon" id="">
                    <svg width="128" height="110" viewBox="0 0 128 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="77" cy="48" r="48" fill="#00B4FF" fill-opacity="0.1"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M79.9115 29.8177C78.3273 27.3941 74.7315 27.3941 73.1473 29.8177L65.1486 42.0535L59.0348 38.5896C55.9718 36.8542 52.2891 39.5738 53.1188 42.9584L57.2724 59.9021C57.4594 60.6652 58.1521 61.2026 58.9484 61.2026H94.1104C94.9068 61.2026 95.5995 60.6652 95.7866 59.9021L99.94 42.9584C100.77 39.5738 97.0871 36.8542 94.024 38.5896L87.9101 42.0535L79.9115 29.8177ZM77.0125 31.6584C76.7864 31.3122 76.2725 31.3122 76.0464 31.6584L67.1599 45.2522C66.6624 46.0132 65.6472 46.2562 64.851 45.8052L57.316 41.5361C56.8785 41.2882 56.3524 41.6768 56.4709 42.1603L60.3057 57.8038H92.7532L96.588 42.1603C96.7064 41.6768 96.1805 41.2882 95.7428 41.5361L88.2078 45.8052C87.4115 46.2562 86.3965 46.0132 85.8991 45.2522L77.0125 31.6584Z" fill="#00B4FF"/>
                        <path d="M60.4365 64.6012C59.4842 64.6012 58.7122 65.3622 58.7122 66.3008C58.7122 67.2391 59.4842 68.0001 60.4365 68.0001H92.6233C93.5756 68.0001 94.3476 67.2391 94.3476 66.3008C94.3476 65.3622 93.5756 64.6012 92.6233 64.6012H60.4365Z" fill="#00B4FF"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M46 71.9351C38.4247 70.7671 37.3425 67.4087 35.9366 57C34.954 66.876 33.7021 70.621 26 71.7469C32.7172 74.5633 35.0786 75.5852 36.2785 87C37.9334 75.4393 39.4073 74.4172 46 71.9351Z" fill="#216DF0"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M25.8091 47.9512C21.3361 47.2512 20.6972 45.2385 19.8671 39C19.2876 44.9191 18.5478 47.1637 14 47.8385C17.967 49.5264 19.3611 50.139 20.0685 56.9805C21.0457 50.0515 21.9171 49.4389 25.8091 47.9512Z" fill="#00B4FF"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M128 26.961C123.455 26.2602 122.806 24.2452 121.962 18C121.372 23.9256 120.621 26.1726 116 26.8481C120.03 28.538 121.447 29.1511 122.167 36C123.16 29.0636 124.044 28.4503 128 26.961Z" fill="#216DF0"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M122.809 74.9512C118.336 74.2512 117.697 72.2385 116.867 66C116.288 71.9191 115.548 74.1637 111 74.8385C114.967 76.5264 116.361 77.139 117.069 83.9805C118.046 77.0515 118.917 76.4389 122.809 74.9512Z" fill="#00B4FF"/>
                    </svg>
                </span>

                <h4 class="rex-premium-feature__heading">
                    <?php esc_html_e( 'This is a Premium Feature', 'rex-product-feed' ); ?>
                </h4>

                <p class="rex-premium-feature__subheading">
                    <?php esc_html_e( 'Upgrade to Pro to unlock this and start using the feature.', 'rex-product-feed' ); ?>
                </p>

                <div class="rex-premium-feature__btn-area">

                    <?php
                        $price = '$79.99'; // This could be dynamic
                        $price_discount = '$63.99'; // This could be dynamic
                    ?>

                    <div class="rex-premium-feature__discount-price">
                        <p class="rex-premium-feature__discount-price-label"><?php printf( esc_html__('Starting at %s/year', 'rex-product-feed'), '<span style= "font-weight:600; color:#0F2F72;">' . esc_html( $price_discount ) . '</span>' ); ?></p>
                        <p><?php printf( esc_html__('Normally %s/year', 'rex-product-feed'), esc_html( $price ) ); ?></p>
                    </div>


                    <a href="https://rextheme.com/best-woocommerce-product-feed/pricing/" class="rex-premium-feature__btn" target="_blank" role="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="21" viewBox="0 0 17 21" fill="none"><path d="M11.4548 7.28939H5.20673V5.20668C5.20764 4.37842 5.53706 3.58433 6.12274 2.99866C6.70841 2.41299 7.50249 2.08356 8.33076 2.08265C9.787 2.08265 11.1441 3.08942 11.4878 4.42465C11.5212 4.55781 11.5805 4.68306 11.6625 4.79319C11.7444 4.90332 11.8474 4.99616 11.9653 5.06636C12.0833 5.13657 12.214 5.18275 12.3498 5.20225C12.4857 5.22176 12.6241 5.2142 12.7571 5.18001C12.89 5.14582 13.0149 5.08568 13.1245 5.00304C13.2341 4.92041 13.3263 4.81691 13.3958 4.69851C13.4652 4.58011 13.5106 4.44914 13.5292 4.31314C13.5479 4.17714 13.5395 4.03879 13.5044 3.90606C12.9222 1.64285 10.7465 0 8.33076 0C6.95036 0.0016277 5.62696 0.550709 4.65086 1.5268C3.67476 2.50289 3.12567 3.82628 3.12403 5.20668V7.73075C2.19679 8.13589 1.4076 8.80227 0.852824 9.64851C0.298052 10.4948 0.0017136 11.4842 0 12.4961V15.6201C0.00164064 17.0006 0.550734 18.3239 1.52683 19.3C2.50293 20.2761 3.82633 20.8252 5.20673 20.8268H11.4548C12.8352 20.8252 14.1586 20.2761 15.1347 19.3C16.1108 18.3239 16.6599 17.0006 16.6615 15.6201V12.4961C16.6599 11.1157 16.1108 9.7923 15.1347 8.8162C14.1586 7.8401 12.8352 7.29101 11.4548 7.28939ZM14.5788 15.6201C14.5779 16.4484 14.2485 17.2425 13.6628 17.8282C13.0771 18.4138 12.2831 18.7433 11.4548 18.7442H5.20673C4.37847 18.7433 3.58438 18.4138 2.99871 17.8282C2.41303 17.2425 2.08361 16.4484 2.0827 15.6201V12.4961C2.08361 11.6679 2.41303 10.8738 2.99871 10.2881C3.58438 9.70242 4.37847 9.37299 5.20673 9.37209H11.4548C12.2831 9.37299 13.0771 9.70242 13.6628 10.2881C14.2485 10.8738 14.5779 11.6679 14.5788 12.4961V15.6201ZM9.37209 13.5374V14.5788C9.37209 14.8549 9.26238 15.1198 9.06709 15.3151C8.8718 15.5104 8.60694 15.6201 8.33076 15.6201C8.05458 15.6201 7.78972 15.5104 7.59443 15.3151C7.39915 15.1198 7.28944 14.8549 7.28944 14.5788V13.5374C7.28944 13.2613 7.39915 12.9964 7.59443 12.8011C7.78972 12.6058 8.05458 12.4961 8.33076 12.4961C8.60694 12.4961 8.8718 12.6058 9.06709 12.8011C9.26238 12.9964 9.37209 13.2613 9.37209 13.5374Z" fill="white"/></svg>
                        <?php esc_html_e( 'Upgrade to PRO Now', 'rex-product-feed' ); ?>
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>
<!-- `rex-premium-feature` block  end -->