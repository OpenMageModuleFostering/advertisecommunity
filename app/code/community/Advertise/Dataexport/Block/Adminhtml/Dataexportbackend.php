<?php  

class Advertise_Dataexport_Block_Adminhtml_Dataexportbackend extends Mage_Adminhtml_Block_Template 
{
    /**
     * @var Advertise_Dataexport_Model_Config 
     */
    protected $_config;
    
    /**
     * Get the config model
     * 
     * @return  Advertise_Dataexport_Model_Config
     */
    protected function _getConfig()
    {
        if($this->_config === NULL) {
            $this->_config = Mage::getModel('dataexport/config');
        }
        
        return $this->_config;
    }
    
    /**
     * Are the orders enabled
     * 
     * @return string 
     */
    protected function _isOrdersEnabledText()
    {
        return $this->_getConfig()->isOrderExportEnabled() ? 'Enabled' : 'Disabled';
    }
    
    /**
     * Are the customers export enabled?
     * 
     * @return string 
     */
    protected function _isCustomersEnabledText()
    {
        return $this->_getConfig()->isCustomerExportEnabled() ? 'Enabled' : 'Disabled';
    }
   
    
    /**
     * Are the customers export enabled?
     * 
     * @return string 
     */
    protected function _isCartsEnabledText()
    {
        return $this->_getConfig()->isCartExportEnabled() ? 'Enabled' : 'Disabled';
    }
    
    /**
     * Is th emodule enabled??
     * 
     * @return string 
     */
    protected function _isEnabled()
    {
        return $this->_getConfig()->getIsEnabled() ? 'Enabled' : 'Disabled';
    }
    
    /**
     * Get the form action 
     * 
     * @return  string
     */
    protected function _getFormAction()
    {
        return Mage::getUrl('*/*/export');
    }
}