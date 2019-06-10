<?php
/*
 * Plugin Name: WooCommerce Fevertree Payment Gateway
 * 
 * Description: Take account  payments on your store.
 * Author: FeverTree Developer
 *
 /*
 */
add_filter('woocommerce_payment_gateways', 'fevertree_add_gateway_class');
function fevertree_add_gateway_class($gateways)
{
    $gateways[] = 'WC_Fevertree_Gateway'; // your class name is here
    return $gateways;
}
/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action('plugins_loaded', 'fevertree_init_gateway_class');
function fevertree_init_gateway_class()
{
    class WC_Fevertree_Gateway extends WC_Payment_Gateway
    {
        
        public function __construct()
        {
            $this->id = 'fevertree'; // payment gateway plugin ID
            $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = true; 
            $this->method_title = 'Fevertree Gateway';
            $this->method_description = 'Description of Fevertree payment gateway'; // will be displayed on the options page
            // Method with all the options fields
            $this->init_form_fields();
            // Load the settings.
            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');
            
            add_action('woocommerce_update_options_payment_gateways_' . $this->id,
                array($this, 'process_admin_options'));
       
        }
    
        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Enable/Disable',
                    'label' => 'Enable Fevertree Gateway',
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'no'
                ),
                'title' => array(
                    'title' => 'Title',
                    'type' => 'text',
                    'description' => 'This controls the title which the user sees during checkout.',
                    'default' => 'Account Payment',
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => 'Description',
                    'type' => 'textarea',
                    'description' => 'This controls the description which the user sees during checkout.',
                    'default' => 'Please enter your ID Number and click send OTP to continue the process, This helps us confirm your identity and secure your transaction.',
                    'css' => array('-webkit-text-fill-color:white')
                ),
                'publishable_key' => array(
                    'title' => 'Business ID',
                    'type' => 'text'
                ),
                'private_key' => array(
                    'title' => 'FTPassword',
                    'type' => 'password'
                )
            );
        }
        /**
         *
         */
        public function payment_fields()
        {
            
            if ($this->description) {
           
                echo wpautop(wp_kses_post($this->description));
            }
           
              
            $float = WC_Payment_Gateway::get_order_total();
            $grandTotal = number_format((float)$float, 2, '.', '');
           // $grandTotal = '15234.17';
            $username = $this->get_option('publishable_key');
            $password = $this->get_option('private_key');
        
             ?>
             <div  id="custom_input" class="container">
                    <div class="row">
                        <div class="col-3">
                            <p class="text-white"  >ID Number : <span class="required"></span></p>
                           
                        </div>
                        <div class="col-5">
                              <input id="idNumber" name="idNumber" type="text" class="form-control " AutoPostBack="True" >
                        </div>
                        <div class="col-4">
                             <button id="SendOTP" type="button" class="btn btn-secondary p-2" name="SendOTP" value="SendOTP">Send OTP</button>   
                            <p class="loader d-none" id="loader"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-3">
                            <p id="pinverifylabel" class="text-white">OTP : <span class="required"></span></p>
                        </div>
                        <div class="col-5">
                              <input id="OTP" type="text"  class="form-control" autocomplete="off" >
                        </div>

                        <div class="col-4">
                             <button id="pinverifybtn" type="button" class="btn btn-secondary  p-2" name="VerifyOTP" value="VerifyOTP">Verify OTP</button>
                            <p class="loader d-none" id="loader2"></p>
                        </div>

                    </div>
                </div>

                    <input type="hidden" id="otpSent" value=""/>
                    <input type="text" class="d-none" id="GUID" name="GUID" />
                    <input type="text" class="d-none" id="pinSuccess" name="pinSuccess" />
                    <input type="username" class="d-none" id="FTusername" name="FTusername" value="<?php echo $username ?>"  />
                    <input type="password" class="d-none" id="FTpassword" name="FTpassword" value="<?php echo $password ?>" />
                    <input type="text" class="d-none" id="orderTotal" name="orderTotal" value="<?php echo $grandTotal ?>"/>


                  
                    
                    <p id="otpResult" class="d-none"></p>
                    <p id="balance" class="d-none"></p>
                    <p id="labelBalance" class="d-none"></p>
                    <a id="link" href="https://www.themattresswarehouse.co.za/apply-for-credit/" class="d-none">Click Here</a>
                  
                      
                        <div class="form-row form-row-first">
                            
                            
                        </div>
                        <div class="form-row form-row-last">
                            
                            
                      
                        <div class="clear"></div>
                    </div>
             
           
                 <script type="text/javascript">
                    jQuery(document).ready(function($){
                    
         
                     jQuery('#pinverifylabel').addClass('d-none');
                     jQuery('#OTP').addClass('d-none');
                     jQuery('#pinverifybtn').addClass('d-none');
                     jQuery('#labelBalance').addClass('d-none');
                     jQuery('#balance').addClass('d-none');
                     jQuery('#link').addClass('d-none');
                     jQuery('#orderTotal').addClass('d-none');
                     jQuery('#otpResult').addClass('d-none');
                     jQuery('#loader').addClass('d-none');
                     jQuery('#loader2').addClass('d-none');
                  
                     jQuery("#SendOTP").click(function ()
                     {
                         jQuery('#loader').removeClass('d-none');
                         jQuery('#SendOTP').addClass('d-none');
                         jQuery('#balance').addClass('d-none');
                         jQuery('#link').addClass('d-none');
                      
                         var cardIDNumber = document.getElementById('idNumber').value;
                         var username = document.getElementById('FTusername').value;
                         var password = document.getElementById('FTpassword').value;
                         var clickBtnValue = $(this).val();
                         var orderTotal = document.getElementById('orderTotal').value;
                        
                       
                         var ajaxurl = '../wp-content/plugins/woocommerce-gateway-fevertree/Submit.php',
                         data =  {'action': clickBtnValue,'finalTotal': orderTotal,'idNumber':cardIDNumber,'userName':username,'Password':password  };
                         $.post(ajaxurl, data, function (response) {
                       // alert($.isNumeric(response));
                             if (response == true) {
                              
                                 jQuery('#loader').addClass('d-none');
                                 jQuery('#SendOTP').removeClass('d-none');
                                 jQuery('#link').addClass('d-none');
                                 jQuery('#pinverifylabel').removeClass('d-none');
                                 jQuery('#OTP').removeClass('d-none');
                                 jQuery('#pinverifybtn').removeClass('d-none');
                             }
                             else if(response != true && $.isNumeric(response) == false)
                             {
                              
                                 jQuery('#loader').addClass('d-none');
                                 jQuery('#SendOTP').removeClass('d-none');
                                 jQuery('#balance').removeClass('d-none');
                                 jQuery('#link').removeClass('d-none');
                                 document.getElementById('balance').style.color = "red";
                                 document.getElementById('link').style.color = "red";
                                 document.getElementById('balance').innerHTML = "There seemed to be an error :" + response + "";
                                document.getElementById('link').innerHTML = "Click here to open an account";
                              }
                             else if($.isNumeric(response) == true)
                             {
                                 jQuery('#loader').addClass('d-none');
                                 jQuery('#balance').removeClass('d-none');
                                 document.getElementById('balance').style.color = "red";
                                 document.getElementById('balance').innerHTML = "You do not have enough funds to process this transaction, Your available balance is : " + "<br />" +                   " R " + response + ". Please review your cart and retry.";
                             }
                         });
                      
                        });
                     jQuery("#pinverifybtn").click(function () 
                     {
                       
                          jQuery('#loader2').removeClass('d-none');
                          jQuery("#pinverifybtn").addClass('d-none')
                          var clickBtnValue = $(this).val();
                          var orderTotal = document.getElementById('orderTotal').value;
                          var cardIDNumber = document.getElementById('idNumber').value;
                          var OTPInput = document.getElementById('OTP').value;
                          var username = document.getElementById('FTusername').value;
                          var password = document.getElementById('FTpassword').value;
                        if (OTPInput == "") 
                        {
                            //document.getElementById('otpResult').style.display = "block";
                             jQuery('#link').addClass('d-none');
                            jQuery('#otpResult').removeClass('d-none');
                            document.getElementById('otpResult').style.color = "red";
                            document.getElementById('otpResult').innerHTML = "No pin entered, Please provide the pin SMS'd to you";
                            
                        } 
                        else
                        {
                   
                        var ajaxurl = '../wp-content/plugins/woocommerce-gateway-fevertree/Submit.php',
                        data =  {'action': clickBtnValue,'finalTotal': orderTotal,'idNumber' :cardIDNumber,'userName' :username,'Password':password,'OTP':OTPInput };
                        $.post(ajaxurl, data, function (response) {
                       
                       //  alert(response);
                             if (response == true) {
                              
                                jQuery('#loader2').addClass('d-none');
                                jQuery("#pinverifybtn").removeClass('d-none');
                                jQuery('#link').addClass('d-none');
                                document.getElementById('pinSuccess').value = response;  
                                jQuery('#balance').removeClass('d-none');
                                document.getElementById('balance').style.color = "green";
                                document.getElementById('balance').innerHTML = "Pin verified succesfully,You may continue.";
                                document.getElementById('OTP').style.borderColor = "green";
                                 
                             }
                             else if (response == false)
                             {
                              
                                jQuery('#loader2').addClass('d-none');
                                jQuery("#pinverifybtn").removeClass('d-none');
                                 jQuery('#link').addClass('d-none');
                                 jQuery('#balance').removeClass('d-none');
                                 document.getElementById('balance').style.color = "red";
                                 document.getElementById('balance').innerHTML = "The OTP pin entered is incorrect, Please enter the correct pin sent to you.If your cell number is not registered to your account you cannot proceed.For queries, please call 0861 007 709";
                                    
                                  document.getElementById('OTP').style.borderColor = "red";
                            }
                         });
                     }
                  });
            });   
               
 </script>  
        <?php
    }
        public function payment_scripts()
        {
             $value = $_COOKIE["age"];
            print_r($_POST);
            setcookie("age",  $value , time()+3600, "/", "", 0);
           
            if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order'])) {
                return;
            }
          
            if ('no' === $this->enabled) {
                return;
            }
         
            if (empty($this->private_key) || empty($this->publishable_key)) {
                return;
            }
         
            if (!$this->testmode && !is_ssl()) {
                return;
            }
      
            wp_localize_script('woocommerce_fevertree', 'fevertree_params', array(
                'publishableKey' => $this->publishable_key
            ));
            wp_enqueue_script('woocommerce_fevertree');
        }
      
        public function validate_fields()
        {
           
            if (empty($_POST['billing_first_name'])) {
                wc_add_notice('First name is required!', 'error');
                return false;
            }
             if (empty($_POST['idNumber'])) {
                wc_add_notice('Your ID Number is required!', 'error');
                return false;
            }
              if (empty($_POST['pinSuccess'])) {
                wc_add_notice('Your pin has not been verified - Please try and send OTP again.', 'error');
                return false;
            }
        }
       
        public function process_payment($order_id)
        {   
            print_r($_POST);
            //print_r($_POST['idNumber']);
            
            $idNumber = $_POST['idNumber'];
            
            global $woocommerce;
            $order = new WC_Order($order_id);
            
            $float = WC_Payment_Gateway::get_order_total();
            $paymentNo = openssl_random_pseudo_bytes(16);
            $paymentNo[6] = chr(ord($paymentNo[6]) & 0x0f | 0x40); // set version to 0100
            $paymentNo[8] = chr(ord($paymentNo[8]) & 0x3f | 0x80); // set bits 6-7 to 10
            $GUIDPayment = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($paymentNo), 4));
            
           
            
            $partnerReference = "WC-" . $order_id;
            $finalTotal = number_format((float)$float, 2, '.', '');
         
            $data = array(
                "TransactionID" => $GUIDPayment,
                "CardOrIDNumber" => $_POST['idNumber'],
                "Amount" => $finalTotal,
                "PartnerReference" => $partnerReference
            );
            $data_string = json_encode($data);
            $username = $this->get_option('publishable_key');
            $password = $this->get_option('private_key');
    
            
             $ch = curl_init("https://api.fevertreefinance.co.za/FTIntegration.svc/ProcessTransaction");
             curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Authorization: Basic '. base64_encode( $username .":" .$password ) ,
                    'Accept: application/json, text/plain, */*' ,
                    'Content-Length: ' . strlen($data_string))
            );
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT , 0);
            $result = curl_exec($ch);
            // Check if any error occurred
            if(curl_errno($ch))
            {
               $this->failureAction();
            }
            $obj = json_decode($result,true);
            //Mage::log($result,true);
             $tranactionSuccess =  $obj['Success'];
             $transactionMessage = $obj['Message'];
            // print_r($tranactionSuccess);
            // print_r($transactionMessage);
            if ($tranactionSuccess == true)
             {
                 
                   $order->reduce_order_stock();
                   $order->update_status('on-hold', '');
                    // Remove cart
                    $woocommerce->cart->empty_cart();
                    // Return thankyou redirect        
                    return array(
                        'result' => 'success',
                        'redirect' => $this->get_return_url($order)
                        );
            }
        else
        {
           wc_add_notice( $transactionMessage);
        }
            curl_close($ch);
        }
        /*
         * In case you need a webhook, like PayPal IPN etc
         */
        public function webhook()
        {
            $order = wc_get_order($_GET['id']);
            $order->payment_complete();
            $order->reduce_order_stock();
            update_option('webhook_debug', $_GET);
        }
    }
}
