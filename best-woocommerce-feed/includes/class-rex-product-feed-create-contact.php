<?php
/**
 * Create Contact to MailMint
 *
 * @since 4.7.14
 */
class Rex_Product_Feed_Create_Contact {

    /**
     * @var string The webhook URL.
     * @since 4.7.14
     */
    protected $webHookUrl = [WPFM_WEBHOOK_URL];

    /**
     * Email
     *
     * @var string
     * @since 4.7.14
     */
    protected $email = '';


    /**
     * Constructor
     *
     * @param string $email
     * @param string $name
     * @since 7.4.14
     */
    public function __construct( $email, $name ){
        $this->email = $email;
        $this->name = $name;
    }


    /**
     * Create contact to MailMint via webhook
     *
     * @return array
     * @since 7.4.14
     */
    public function create_contact_via_webhook(){
        if( !$this->email ){
            return [
                'suceess' => false,
            ];
        }

        $response = [
            'suceess' => true,
        ];

        $json_body_data = json_encode([
            'email'         => $this->email,
            'first_name'    => $this->name
        ]);

        try{
            if( !empty($this->webHookUrl ) ){
                foreach( $this->webHookUrl as $url ){
                    $response = wp_remote_request($url, [
                        'method'    => 'POST',
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ],
                        'body' => $json_body_data
                    ]);
                }
            }
        }catch(\Exception $e){
            error_log('Error sending contact data to MailMint');
            $response = [
                'suceess' => false,
            ];
        }

        return $response;
    }
}
?>