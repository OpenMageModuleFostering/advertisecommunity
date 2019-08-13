<?php

require("app/code/community/Advertise/Account/Model/Account.php");

/**
 * All actions must return a json string 
 * { status : $x , error_message : $y, data : $z }
 * where $x is 0 for ok, 1 for fail
 * where $y is the error message if $x=1
 * where $z is a embedded json string which contains the data to return to the control panel
 */
class Advertise_Account_WebserviceController extends Mage_Core_Controller_Front_Action {

    /**
     * transform an object to a json string
     * @param $something : the object to transform
     * */
    private function to_json($something) {
        return json_encode($something);
        // TODO : check pour les erreurs json
    }


    /**
     * Function to check the url of the webservice
     * */
    public function checkAction() {
        foreach ($_POST as $key => $value) $$key = $value;
        $license = $this->getRequest()->getParam('license');
        $stored_license = Mage::getStoreConfig("advertise/account/license");
        if ($stored_license != $license) {
            header("Status : 404 Not Found"); header('HTTP/1.0 404 Not Found');
            
            echo $this->to_json(array("status"=>"0","error_message"=>"","data"=>"{}"));
        } else {
            echo $this->to_json(array("status"=>"1","error_message"=>"","data"=>"{}"));
        }
    }

    /**
     * Get the documentation of the webservice
     * */
    public function docAction() {
        echo "<h1>Advertise webservice documentation</h1>";
        echo "<h2>General functions</h2>";
        echo "<h3><i>sample</i> function</h3>";
        echo "<h3><i>generate</i> function</h3>";
        echo "<h2>Getters</h2>";
        echo "<h3>get_filename</h3>";
        echo "<h3>get_filepath</h3>";
        echo "<h3>get_stores</h3>";
        echo "<h3>get_store</h3>";
        echo "<h3>get_website_url</h3>";
        echo "<h3>get_template</h3>";
        echo "<h2>Setters</h2>";
        echo "<h3>update_filename</h3>";
        echo "<h3>update_filepath</h3>";
        echo "<h3>update_store</h3>";
        echo "<h3>update_website_url</h3>";
        echo "<h3>update_template</h3>";
    }

    /* Getters ============================================================== */



    /**
     * generic action 
     */
    private function get_generic($what_to_retrieve) {
        foreach ($_POST as $key => $value)$$key = $value;
        try {
            $id = $this->getRequest()->getParam('id');
            $is_activated = Mage::getStoreConfig("advertise/account/activated", $id);
            if ($is_activated) {

                $license = $this->getRequest()->getParam('license');
                // check license number
                $stored_license = Mage::getStoreConfig("advertise/account/license");
                if ($stored_license != $license) {
                    header("Status : 404 Not Found"); header('HTTP/1.0 404 Not Found');
                    echo $this->to_json(array("status"=>"1","error_message"=>"Bad license number","data"=>"{}"));
                    return;
                }

                // specific traitement for cron schedule
                if ($what_to_retrieve == "cron_schedule") {
                    $wtr = Mage::getStoreConfig("advertise/account/cron_schedule_1", $id);
                    $wtr .= " ".Mage::getStoreConfig("advertise/account/cron_schedule_2", $id);
                    $wtr .= " *";
                    $wtr .= " *";
                    $wtr .= " ".Mage::getStoreConfig("advertise/account/cron_schedule_5", $id);
                } else {
                    $wtr = Mage::getStoreConfig("advertise/account/".$what_to_retrieve, $id);
                }
                echo $this->to_json(array("status" => "0", "error_message" => "", "data" => $this->to_json($wtr)));
            } else {
                echo $this->to_json(array("status"=>"1","error_message"=>"Retrieve ".$what_to_retrieve."'s feed : Store not activated!!","data"=>"{}"));
            }
        } catch (Exception $e) {
                echo $this->to_json(array("status"=>"1","error_message"=>"Retrieve ".$what_to_retrieve."'s feed : Store not activated!!","data"=>"{}"));
        }
    }


    private function rec_make_stores($data) {
        if (!isset($data['value'])) {
            $new_data = array();
            foreach ($data as $d) {
                $new_data[] = $this->rec_make_stores($d);
            }
            return $new_data;
        }
        if (is_array($data['value'])) {
            $new_data = array();
            foreach ($data['value'] as $d) {
                $new_data[] = $this->rec_make_stores($d);
            }
            $data['value'] = $new_data;
            return $data;
        } else { // is an id
            $is_activated = Mage::getStoreConfig("advertise/account/activated", $data['value']);
            if ($is_activated != null && $is_activated == 1) {
                return $data;
            }
            return null;
        }
    }

    public function get_storesAction() {
        try {
            $license = $this->getRequest()->getParam('license');
            $stored_license = Mage::getStoreConfig("advertise/account/license");
            if ($stored_license !== $license) {
                header("Status : 404 Not Found"); header('HTTP/1.0 404 Not Found');
                echo $this->to_json(array("status"=>"1","error_message"=>"Bad license number","data"=>"{}"));
                return;
            } else {
                $stores = Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm();
                echo $this->to_json(array("status" => "0", "error_message" => "", "data" => $this->to_json($this->rec_make_stores($stores))));
            }
        } catch (Exception $e) {
            echo $this->to_json(array("status" => "1", "error_message" => $e->getMessage(), "data" => "{}"));
        }
    }


    /**
     * Get the template for a data feed
     * @param advertise_id : the id of the data feed
     * */
    public function get_templateAction() {
        $this->get_generic('template');
    }

    
    /* Setters ============================================================== */

    private function update_generic($what_to_update) {
        foreach ($_POST as $key => $value) $$key = $value;

        $license = $this->getRequest()->getParam('license');
        $stored_license = Mage::getStoreConfig("advertise/account/license");
        if ($stored_license != $license) {
            header("Status : 404 Not Found"); header('HTTP/1.0 404 Not Found');
            echo $this->to_json(array("status"=>"1","error_message"=>"Bad license number","data"=>"{}"));
            return;
        }

        $id = $this->getRequest()->getParam('id');
        $new_value = $this->getRequest()->getParam($what_to_update);
        
        /* Traitements speciaux */
        
        // traitement special pour le file path
        switch ($what_to_update) {
            case "filepath":
                // check du path du feed
                $io = new Varien_Io_File();
                $realPath = $io->getCleanPath(Mage::getBaseDir() . '/' . $new_value);
                if (!$io->fileExists($realPath, false)) {
                    die($this->to_json(array("status"=>"1","error_message"=>"The folder '$new_value' doesn't exist.","data"=>"{}")));                    
                }
                if (!$io->isWriteable($realPath)) {
                    die($this->to_json(array("status"=>"1","error_message"=>"You don't have the right to write in the folder '$new_value'.","data"=>"{}")));
                }
                $new_value = rtrim(str_replace(str_replace('\\', '/', Mage::getBaseDir()), '', $realPath), '/') . '/';
        }
        
        
        
        // retour a la normal
        try {
            $is_activated = Mage::getStoreConfig("advertise/account/activated", $id);
            if ($is_activated) {
                $config = new Mage_Core_Model_Config();
                $config->saveConfig('advertise/account/'.$what_to_update, $new_value, 'stores', $id);
                echo $this->to_json(array("status"=>"0","error_message"=>"","data"=>"{}"));
            } else {
                echo $this->to_json(array("status"=>"1","error_message"=>"Retrieve $what_to_update's feed : Store not activated!!","data"=>"{}"));
            }
        } catch (Mage_Core_Exception $e) {
            echo $this->to_json(array("status"=>"1","error_message"=>$e->getMessage(),"data"=>"{}"));
        } catch (Exception $e) {
            echo $this->to_json(array("status"=>"1","error_message"=>$e->getMessage(),"data"=>"{}"));
        }
    }



    /**
     * Update the template for a data feed
     * @param advertise_id : the id of the data feed
     * @param template : the new template
     * */
    public function update_templateAction() {
        $this->update_generic('template');
    }



    /* Others actions ======================================================= */

    
    
    
    
    /**
     * Webservice function : sample
     * @desc : get a sample of the generated data feed
     * @param advertise_id : the id of the feed
     * @param limit : the number of product to include in the sample (default is 10)
     * */
    public function sampleAction() {
        foreach ($_POST as $key => $value) $$key = $value;


        $sendl = $this->getRequest()->getParam('license');

        try {
            $id = $this->getRequest()->getParam('id');
            $ad = new Advertise();
            echo $this->to_json(array("status"=>"0","error_message"=>"","data"=>$this->to_json($ad->x10($id,$sendl, 10))));
        } catch (Mage_Core_Exception $e) {
            header("Status : 404 Not Found"); header('HTTP/1.0 404 Not Found');
            echo $this->to_json(array("status"=>"1","error_message"=>$e->getMessage(),"data"=>"{}"));
        } catch (Exception $e) {
            header("Status : 404 Not Found"); header('HTTP/1.0 404 Not Found');
            echo $this->to_json(array("status"=>"1","error_message"=>$e->getMessage(),"data"=>"{}"));
        }
    }

    /**
     * Webservice function : generate
     * @desc : get a generate of the generated data feed
     * @param advertise_id : the id of the feed
     * */
    public function generateAction() {
        foreach ($_POST as $key => $value) $$key = $value;

        $sendl = $this->getRequest()->getParam('license');

        try {
            $id = $this->getRequest()->getParam('id');
            $ad = new Account();
            header ("content-type: text/xml");
            echo $ad->x10($id,$sendl);
        } catch (Mage_Core_Exception $e) {
            header("Status : 404 Not Found"); header('HTTP/1.0 404 Not Found');
            echo $this->to_json(array("status"=>"1","error_message"=>$e->getMessage(),"data"=>"{}"));
        } catch (Exception $e) {
            header("Status : 404 Not Found"); header('HTTP/1.0 404 Not Found');
            echo $this->to_json(array("status"=>"1","error_message"=>$e->getMessage(),"data"=>"{}"));
        }
    }

}
