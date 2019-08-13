<?php
/**
 * Data.php
 */
class Advertise_Dataexport_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $_initialVector = '0123456789123456';
    protected $_secretKey = 'S1xt33NcHaRsl0Ng';

    public function getAdvertiseHeaderScript()
    {
        if(Mage::registry('current_product')) {
            $prodid = Mage::registry('current_product')->getId();
        } else {
            $prodid = '';
        }
        if(Mage::getModel('dataexport/config')->isHeadProductIdsEnabled()) {
            $basket=$this->getProductDataString();
        } else {
            $basket = '';
        }

        $jsoutput = "
            var adv_store_base_url = '".Mage::getBaseUrl()."';
            var adv_reload = true;
            adv_upsell_reload = true;
            adv_crosssell_reload = true;
            var adv_productid = '".$prodid."';
            var adv_bsk = '".$basket."';
            ";
            // var cartcount = '".Mage::helper('checkout/cart')->getCart()->getItemsCount()."';

        return $jsoutput;
    }

    /**
     * Get the product IDs to output, but first check we want to show them
     * just a shortcut for displaying inside template files
     *
     * @return  string
     */
    public function getHeaderProductIds()
    {
        if(Mage::getModel('dataexport/config')->isHeadProductIdsEnabled()) {
            return $this->getProductDataString();
        }

        return "";
    }

    /**
     * Get product data as a string
     *
     * @return string|FALSE
     */
    public function getProductDataString()
    {
        if(Mage::helper('checkout/cart')->getCart()->getItemsCount() < 1) {
            return FALSE;
        }

        //$cartHelper = Mage::helper('checkout/cart');
        //$items = $cartHelper->getCart()->getItems();
        //$cart = Mage::helper('checkout/cart')->getCart()->getItemsCount();
        $session = Mage::getSingleton('checkout/session');
        $products = array();
        //$productIds = Mage::getModel('checkout/cart')->getProductIds();
        //return implode(',', $productIds);

        // getAllItems OR getAllVisibleItems()
        foreach ($session->getQuote()->getAllVisibleItems() as $item) {
            $products[] = $item->getProductId();
        }

        if( ! empty($products)) {
            //return Mage::helper('core')->encrypt(implode(',', $products));
            $encrypted = $this->encrypt('['.implode(',', $products).']', $this->_initialVector, $this->_secretKey);
            return $encrypted;
        }

        return FALSE;
    }

    /**
     * Get product ids from encrypted string
     *
     * @param   string
     * @return  array|FALSE
     */
    public function getProductIdsFromString($dataString)
    {
        $return = array();
        $productString = $this->decrypt($dataString);

        if( ! empty($productString)) {
            $return = explode(',', $productString);
        }

        return $return;
    }

    function encrypt($message, $initialVector, $secretKey) {
        return base64_encode(
                mcrypt_encrypt(
                        MCRYPT_RIJNDAEL_128,
                        md5($secretKey),
                        $message,
                        MCRYPT_MODE_CFB,
                        $initialVector
                )
        );
    }

    function decrypt($message, $initialVector, $secretKey) {
        return mcrypt_decrypt(
                    MCRYPT_RIJNDAEL_128,
                    md5($secretKey),
                    base64_decode($message),
                    MCRYPT_MODE_CFB,
                    $initialVector
        );
    }
}