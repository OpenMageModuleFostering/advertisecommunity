<?php

define('LICENSE_SERVER','https://i.adverti.se/magento/gateway/');



class Advertise_Account_LicenseController extends Mage_Core_Controller_Front_Action {

    function do_post_request($url, $postdata) {
        $data = "";
        $boundary = "---------------------" . substr(md5(rand(0, 32000)), 0, 10);

        //Collect Postdata
        foreach ($postdata as $key => $val) {
            $data .= "--$boundary\n";
            $data .= "Content-Disposition: form-data; name=\"" . $key . "\"\n\n" . $val . "\n";
        }

        $data .= "--$boundary\n";

        $params = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: multipart/form-data; boundary=' . $boundary,
                'content' => $data
            )
        );

        $ctx = stream_context_create($params);
        $fp = fopen($url, 'rb', false, $ctx);

        if (!$fp) {
            echo json_encode(array('status' => "1", "error_message" => "Problem with $url", "data" => "{}"));
            die;
        }

        $response = @stream_get_contents($fp);
        if ($response === false) {
            echo json_encode(array('status' => "1", "error_message" => "Problem with $url", "data" => "{}"));
            die;
        }

        return $response;
    }


    function retrieveAction() {
        foreach ($_POST as $key => $value)
            $$key = $value;





        $get_online_license = $this->getRequest()->getParam('online');

        $name = $this->getRequest()->getParam('name');
        $firstname = $this->getRequest()->getParam('firstname');
        $email = $this->getRequest()->getParam('email');
        $base_url = Mage::getStoreConfig("web/secure/base_url");

        $license_request = array(
            "domain"=>$base_url,
            "name"=>$name,
            "firstname"=>$firstname,
            "email"=>$email
        );

        $license_server = LICENSE_SERVER;



        if ($get_online_license == '0') {
            //echo "Location : ".str_replace(':_:','/',$license_server)."?".http_build_query(array_merge(array("method"=>"post"),$license_request));
            echo "<script>document.location.href = '".str_replace(':_:','/',$license_server)."?".http_build_query(array_merge(array("method"=>"post"),$license_request))."';</script>";
            die;
        } else {

            $activation_code = $this->do_post_request($license_server,array_merge(array("method"=>"get"),$license_request));
            $activation_code = str_replace(array('\n','\r','\r\n'),array('','',''),$activation_code);
            //echo $activation_code;
            $result = json_decode($activation_code);
            switch ($result->status) {
                case "success" :
                    Mage::getConfig()->saveConfig("advertise/account/license", $result->license, "default", "0");
                    Mage::getConfig()->cleanCache();
                    echo json_encode(array("status" => "1", "data" => $result->license, "message" => $result->message));
                    break;
                case "error" :
                    Mage::getSingleton("adminhtml/session")->addError(Mage::helper("advertise")->__($result->message));
                    Mage::getConfig()->saveConfig("advertise/account/license", "", "default", "0");
                    Mage::getConfig()->cleanCache();
                    echo json_encode(array("status" => "2", "data" => "", "message" => $result->message));
                    break;
                default :
                    Mage::getConfig()->saveConfig("advertise/account/license", "", "default", "0");
                    Mage::getConfig()->cleanCache();
                    echo json_encode(array("status" => "2", "data" => "", "message" => "An error occurs while connecting samsara license server (500).<br>
                        <a target='_blank' href='".$license_server."?" . http_build_query(array_merge(array("method"=>"post"),$license_request)) . "'>Get a license now!</a>"));
                    break;
            }
        }
    }

}
