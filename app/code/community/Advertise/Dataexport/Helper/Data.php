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
        $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        if($orderId) {
            //Mage::log("Got order ID: ".$orderId);
        }
        else {
            //Mage::log("No order ID.");
            $orderId = '';
        }
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        if($customerId) {
            //Mage::log("Got customer ID: ".$orderId);
        }
        else {
            //Mage::log("No customer ID.");
            $customerId = '';
        }
        $relcount   = Mage::getStoreConfig('advertise_suggestedproducts_options/advertise_suggested_products/advertise_related_prod_count');
        $upcount    = Mage::getStoreConfig('advertise_suggestedproducts_options/advertise_suggested_products/advertise_upsell_prod_count');
        $crosscount = Mage::getStoreConfig('advertise_suggestedproducts_options/advertise_suggested_products/advertise_crosssell_prod_count');
        if($relcount == null) {
            $relcount = 0;
        }
        if($upcount == null) {
            $upcount = 0;
        }
        if($crosscount == null) {
            $crosscount = 0;
        }
        $jsoutput = "
            var adv_store_base_url = '".Mage::getBaseUrl()."';
            var adv_reload = true;
            adv_upsell_reload = true;
            adv_crosssell_reload = true;
            var adv_rel_count = '".$relcount."';
            adv_upsell_count = '".$upcount."';
            adv_crosssell_count = '".$crosscount."';
            var adv_productid = '".$prodid."';
            var adv_bsk = '".$basket."';
            var adv_oid = '".$orderId."';
            var adv_cst = '".$customerId."';
            ";

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
            // Add multiple copies of same ID if quantity > 1
            $qty = $item->getQty();
            for ($i = 0; $i < $qty; $i++) {
                $products[] = $item->getProductId();
            }
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

    // Not currently used
    function getReferrerPageProductID() {
        $referrerPath = $_SERVER ['HTTP_REFERER'];
        if (!strncmp($referrerPath, Mage::getBaseUrl(), strlen(Mage::getBaseUrl()))) {
            // If REFERRER starts with store base URL
            $referrerPath = substr($referrerPath, strlen(Mage::getBaseUrl()));
        } else {
            // Referrer not from this site so stop
            return;
        }
        try {
            $oRewrite = Mage::getModel('core/url_rewrite')
                            ->setStoreId(Mage::app()->getStore()->getId())
                            ->loadByRequestPath($referrerPath);
            $iProductId = $oRewrite->getProductId();
        } catch (Exception $e) {
            // No product ID - referrer not product page
            return;
        }
        // Gets product model object itself
        //$oProduct = Mage::getModel('catalog/product')->load($iProductId);
        return $iProductId;
    }
}