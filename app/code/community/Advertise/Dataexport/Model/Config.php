<?php
/**
 * Config.php
 *
 * @package     Dataexport
 */
class Advertise_Dataexport_Model_Config extends Varien_Object
{
    /**
     * Config keys
     */
    const ENABLED           = 'dataexport/dataexport_group/enabled';
    const ENABLED_CRON      = 'dataexport/dataexport_group/enabled_cron';
    const EXPORT_URL        = 'dataexport/dataexport_group/export_url';
    const CUSTOMERS_ENABLED = 'dataexport/dataexport_group/export_customers';
    const ORDERS_ENABLED    = 'dataexport/dataexport_group/export_orders';
    const ORDER_STATUS      = 'dataexport/dataexport_group/order_status';
    const CARTS_ENABLED     = 'dataexport/dataexport_group/export_carts';
    const ADVERTISE_EMAIL   = 'advertise_settings/settings/settings_email';

    // No need for this as use ORDERS_ENABLED for same thing
    //const ENABLE_IDS_HEAD   = 'dataexport/dataexport_group/export_productids';

    /**
     * Is the module enabled?
     * 
     * @return  bool
     */
    public function getIsEnabled()
    {
        return (bool) Mage::getStoreConfig(self::ENABLED);
    }
    
    /**
     * Is the module enabled?
     * 
     * @return  bool
     */
    public function isCronEnabled()
    {
        return (bool) Mage::getStoreConfig(self::ENABLED_CRON);
    }
    
    /**
     * Do we want to output the product ids to the head of the doc?
     * 
     * @return bool
     */
    public function isHeadProductIdsEnabled()
    {
        //return (bool) Mage::getStoreConfig(self::ENABLE_IDS_HEAD);
        return (bool) Mage::getStoreConfig(self::ORDERS_ENABLED);
    }
    
    /**
     * Customer exportr enabled?
     * 
     * @return bool
     */
    public function isCustomerExportEnabled()
    {
        return (bool) Mage::getStoreConfig(self::CUSTOMERS_ENABLED);
    }
    
    /**
     * orders exportr enabled?
     * 
     * @return bool
     */
    public function isOrderExportEnabled()
    {
        return (bool) Mage::getStoreConfig(self::ORDERS_ENABLED);
    }
    
    /**
     * cart exportr enabled?
     * 
     * @return bool
     */
    public function isCartExportEnabled()
    {
        return (bool) Mage::getStoreConfig(self::CARTS_ENABLED);
    }
    
    /**
     * Get the advertise Email settings.
     * 
     * @return  string
     */
    public function getAdvertiseEmail()
    {
        return Mage::getStoreConfig(self::ADVERTISE_EMAIL);
    }
    
    /**
     * Get the status we want to download products as
     *
     * @return array
     */
    public function getOrderExportStatus()
    {
        $status = Mage::getStoreConfig(self::ORDER_STATUS);

        return explode(',', $status);
    }
    
    /**
     * Get the base url
     * 
     * @return string
     */
    public function getBaseUrl()
    {
        return str_replace('http://', '', Mage::getStoreConfig('web/unsecure/base_url'));
    }
   
    /**
     * Get export URL
     * 
     * @return string 
     */
    public function getExportUrl()
    {
        // Hard-coded URL to export data for all stores to
        return 'http://related.adverti.se:8888/servlets/uploadfiles';

        // Use this version to take export URL from config settings
        //return Mage::getStoreConfig(self::EXPORT_URL);
    }
    
    /**
     * GEt a temp folder we'll use to store the exported data.
     * 
     * @return  string
     */
    public function getTempFolder()
    {
        return Mage::getBaseDir('var') . DS . 'advertisedata';
    }
    
    /**
     * Is the temp folder there and writable??
     * 
     * @return  bool
     */
    public function tempFolderWritable()
    {
        return is_writable($this->getTempFolder());
    }
    
    /**
     * Create the temp folder we need
     */
    public function createTempFolder()
    {
        if( ! is_dir($this->getTempFolder())) {
            mkdir($this->getTempFolder(), 0777);
        }
    }
}