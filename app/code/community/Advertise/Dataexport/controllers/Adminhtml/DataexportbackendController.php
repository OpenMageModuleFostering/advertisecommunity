<?php
/**
 * DataexportbackendController.php
 */
class Advertise_Dataexport_Adminhtml_DataexportbackendController extends Mage_Adminhtml_Controller_Action
{
    /**
     * index action
     */
    public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Adverti.se Retail Intelligence"));
	   $this->renderLayout();
    }
    
    /**
     * Run the export 
     */
    public function exportAction()
    {
        $config = Mage::getModel('dataexport/config');
        
        if( ! $config->getIsEnabled()) {
            Mage::throwException($this->__('Module Disabled!'));
        }
        
        if($this->getRequest()->isPost()) { 
            try {
                //$post = $this->getRequest()->getPost();
                $exporter = Mage::getModel('dataexport/exporter');
                /* @var $exporter Advertise_Dataexport_Model_Exporter */
                /**
                 * Add Order Export
                 */
                if($config->isOrderExportEnabled()) {
                    $toDate = $this->getRequest()->getParam('date_to', NULL);
                    $fromDate = $this->getRequest()->getParam('date_from', NULL);
                    
                    $exportAdapter = Mage::getModel('dataexport/exporter_order');
                    $exportAdapter->setDateRange($fromDate, $toDate);
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

            $this->_redirect('*/*');
        }
        else {
            Mage::throwException($this->__('Invalid form data.'));
        }
    }
}