<?php 
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'SendOTP':
                BalanceCheckFunc();
                break;
            case 'VerifyOTP':
                checkOTPField();
                break;
        }
    } 
function BalanceCheckFunc()
    {
            //$float = WC_Payment_Gateway::get_order_total();
            $data3 = openssl_random_pseudo_bytes(16);
            $data3[6] = chr(ord($data3[6]) & 0x0f | 0x40); // set version to 0100
            $data3[8] = chr(ord($data3[8]) & 0x3f | 0x80); // set bits 6-7 to 10
            $GUID = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data3), 4));
            
           
           $idNumber = $_POST['idNumber']; 
           $finalTotal = $_POST['finalTotal'];
           //$finalTotal = number_format((float)$float, 2, '.', '');
         
            $data = array(
                "BalanceID" => $GUID,
                "CardOrIDNumber" =>  $idNumber 
               
            );
            $data_string = json_encode($data);
            $username = $_POST['userName'];
            $password = $_POST['Password'];
    
            
        $ch = curl_init("https://api.fevertreefinance.co.za/FTIntegration.svc/BalanceLookup");
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
         $AvailableBalance = $obj['Available'];
        if ($tranactionSuccess == true) {
             if ($finalTotal > $AvailableBalance) 
             {
                 echo $AvailableBalance;
             }
             else if($finalTotal <= $AvailableBalance)
             {
                 sendOTPFunc();
             }
        }
        else if($tranactionSuccess == false)
        {
             echo $transactionMessage;
        }
      
       
        curl_close($ch);
    }
    function sendOTPFunc()
    {
         $data3 = openssl_random_pseudo_bytes(16);
            $data3[6] = chr(ord($data3[6]) & 0x0f | 0x40); // set version to 0100
            $data3[8] = chr(ord($data3[8]) & 0x3f | 0x80); // set bits 6-7 to 10
            $GUID = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data3), 4));
            $idNumber = $_POST['idNumber'];
            $data = array(
                "TransactionID" => $GUID,
                "CardOrIDNumber" => $idNumber
            );
            $data_string = json_encode($data);
            $username = $_POST['userName'];
            $password = $_POST['Password'];
    
            
        $ch = curl_init("https://api.fevertreefinance.co.za/FTIntegration.svc/SendOTP");
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
        
        if ($tranactionSuccess == true) {
             
             echo $tranactionSuccess;
            
        }
        else if($tranactionSuccess == false){
            echo $transactionMessage;
        }
      
        curl_close($ch);
    }
   function checkOTPField()
   {
         $data3 = openssl_random_pseudo_bytes(16);
            $data3[6] = chr(ord($data3[6]) & 0x0f | 0x40); // set version to 0100
            $data3[8] = chr(ord($data3[8]) & 0x3f | 0x80); // set bits 6-7 to 10
            $GUID = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data3), 4));
        
            $OTP = $_POST['OTP'];
            $idNumber = $_POST['idNumber'];
            $data = array(
                "TransactionID" => $GUID,
                "CardOrIDNumber" => $idNumber,
                "OTP" => $OTP
               
            );
            $data_string = json_encode($data);
            $username = $_POST['userName'];
            $password = $_POST['Password'];
    
            
        $ch = curl_init("https://api.fevertreefinance.co.za/FTIntegration.svc/CheckOTP");
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
        
        if ($tranactionSuccess == 1) {
             
             echo $tranactionSuccess;
            
        }
        else{
            echo $transactionMessage;
        }
      
        curl_close($ch);
   }
 ?>
