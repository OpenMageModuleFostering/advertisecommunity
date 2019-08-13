<?php
set_time_limit(0);
ini_set('memory_limit', '512M');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
/**
 * Scheduler.php
 * 
 * @package     Dataexport
 */
class Advertise_Dataexport_Model_Scheduler extends Varien_Object
{
    /**
     * Lets do this!
     */
    public static function export()
    {
        $config = Mage::getModel('dataexport/config');
        
        if( ! ($config->getIsEnabled() && $config->isCronEnabled())) {
            return;
            //Mage::throwException($this->__('Module Disabled!'));
        }
        
        try {
            //$post = $this->getRequest()->getPost();
            $exporter = Mage::getModel('dataexport/exporter');
            /* @var $exporter Advertise_Dataexport_Model_Exporter */
            /**
             * Add Order Export
             */
            if($config->isOrderExportEnabled()) {
                //$toDate = $this->getRequest()->getParam('date_to', NULL);
                //$fromDate = $this->getRequest()->getParam('date_from', NULL);

                $exportAdapter = Mage::getModel('dataexport/exporter_order');
                //$exportAdapter->setDateRange($fromDate, $toDate);
                $exporter->addExporter($exportAdapter);
            }
            /**
             * Add Customer Export
             */
            if($config->isCustomerExportEnabled()) {
                $exportAdapter = Mage::getModel('dataexport/exporter_customer');
                $exporter->addExporter($exportAdapter);
            }
            /**
             * Add Cart Export
             */
            if($config->isCartExportEnabled()) {
                $exportAdapter = Mage::getModel('dataexport/exporter_cart');
                $exporter->addExporter($exportAdapter);
            }

            /**
             * Do it!
             */
            $totalItems = $exporter->export();

            $message = $this->__('Your form has been submitted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
            Mage::getSingleton('adminhtml/session')->addSuccess("{$totalItems} Items successfully Exported.");
        } 
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }
}