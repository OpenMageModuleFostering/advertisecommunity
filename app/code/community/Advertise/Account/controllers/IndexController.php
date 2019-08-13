<?php

class Advertise_Account_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function loginAction() {
        $auth_data = array(
            'username' => Mage::getStoreConfig('advertise_settings/settings/username'),
            'password' => Mage::getStoreConfig('advertise_settings/settings/hash_password')
        );

        echo json_encode($auth_data);
        exit();
    }

    public function checkAction() {
        
    }

}