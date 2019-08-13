<?php
/**
 * IndexController.php
 */
class Advertise_Dataexport_TestController extends Mage_Core_Controller_Front_Action
{
   /**
     * Predispatch: should set layout area
     *
     * @return Mage_Core_Controller_Front_Action
     */
    public function preDispatch()
    {
        if( ! Mage::getIsDeveloperMode()) {
            die('no access.');
        }
        
        parent::preDispatch();
        
        return $this;
    }
    
    /**
     * Test product string
     */
    public function stringAction()
    {
        // shortcut method, to be used inside a template
        echo Mage::helper('advertise_relatedproducts')->getHeaderProductIds();
        
        // output encoded string
        $string = Mage::helper('advertise_relatedproducts')->getProductDataString();
        echo $string . "<br />";
        
        // get back the value we want
        var_dump(Mage::helper('advertise_relatedproducts')->getProductIdsFromString($string));
    }
    
    /**
     * index action
     */
    public function indexAction()
    {
        try {
            //$target = 'http://www.google.com/';
            $config = Mage::getModel('dataexport/config');
            $target = $config->getExportUrl();
            $adapter = new Zend_Http_Client_Adapter_Curl();
            $client = new Zend_Http_Client($target);
            $client->setAdapter($adapter);

            $adapter->setConfig(array(
                'timeout'       => 60,
                'curloptions'   => array(
                    CURLOPT_SSL_VERIFYHOST  => 0,
                    CURLOPT_SSL_VERIFYPEER  => 0,
                    CURLOPT_TIMEOUT         => 60,
                    //CURLOPT_ENCODING        => 'gzip',
                    //CURLOPT_CONNECTTIMEOUT  => 60,
                    //CURLOPT_POSTFIELDS      => 'data',
                    //CURLOPT_FOLLOWLOCATION => true
                    //CURLOPT_INFILE => $putFileHandle,
                    //CURLOPT_INFILESIZE => $putFileSize
                )
            ));
            //$client->setParameterPost('name', 'value');
            $client->setHeaders('Content-Type','text/xml');
            $client->setRawData('THIS IS A TEST!', 'text/xml');
            
            $response = $client->request(Zend_Http_Client::POST);
            //echo $client->read();
            echo $response->getBody(); 
        } 
        catch(Exception $e) {
            $debug['http_error'] = array(
                'error' => $e->getMessage(), 
                'code' => $e->getCode()
            );
            var_dump($debug);
            //throw $e;
        }
        exit;
    }
    
    /**
     * Test sending a file.
     */
    public function sendFileAction()
    {
        $config = Mage::getModel('dataexport/config');
        $target = $config->getExportUrl();
            
        $filename = $config->getTempFolder() . DS . 'test.xml';
        
        $putFileSize   = filesize($filename);
        $putFileHandle = fopen($filename, "r");

        $adapter = new Zend_Http_Client_Adapter_Curl();
        $client = new Zend_Http_Client($target);
        $client->setAdapter($adapter);
        
        $adapter->setConfig(array(
            'curloptions' => array(
                CURLOPT_INFILE          => $putFileHandle,
                CURLOPT_INFILESIZE      => (string) $putFileSize,
                //CURLOPT_SSL_VERIFYHOST  => 0,
                //CURLOPT_SSL_VERIFYPEER  => 0,
            )
        ));
        
        $response = $client->request(Zend_Http_Client::PUT);
        //echo $response->getBody(); 
        
        //$client->request("PUT");
    }
    
    /**
     * Test the customer feed
     */
    public function customerAction()
    {
        $collection = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('*')
        ;
        
        $result = array();
        
        foreach ($collection as $customer) {
            /* @var $customer Mage_Customer_Model_Customer */
            $data = $customer->toArray();
            $data['billing_address'] = $customer->getDefaultBillingAddress()->toArray();
            $data['shipping_address'] = $customer->getDefaultShippingAddress()->toArray();
            $result[] = $data;
        }
        
        var_dump($result);
    }
    
    /**
     * Test getting order feed.
     */
    public function orderAction()
    {
        //$post = $this->getRequest()->getPost();
        $exporter = Mage::getModel('dataexport/exporter');
        /* @var $exporter Advertise_Dataexport_Model_Exporter */

        $toDate = $this->getRequest()->getParam('date_to', NULL);
        $fromDate = $this->getRequest()->getParam('date_from', NULL);

        $exportAdapter = Mage::getModel('dataexport/exporter_order');
        $exportAdapter->setDateRange($fromDate, $toDate);
        $exporter->addExporter($exportAdapter);

        /**
         * Do it!
         */
        $totalItems = $exporter->export();
    }
    
    /**
     * Get the orders we need as a collection
     * 
     * @param   int
     * @return  Varien_Data_Collection
     */
    protected function _getOrderCollection($storeId = NULL)
    {
        $storeId = Mage::app()->getStore($storeId)->getId();
        $downloadStatus = Mage::getModel('dataexport/config')->getOrderExportStatus(); //array('complete', 'pending');
        $orderCollection = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('*')
            ->addFieldToFilter('status', array('or' => $downloadStatus))
            ->addFieldToFilter('store_id', array('eq' => $storeId))
        ;
        /**
         * Handle to and from dates
         * @todo refactor away from here...
         */
        $fromDate = $this->getRequest()->getParam('from', FALSE);
        $toDate = $this->getRequest()->getParam('to', FALSE);
        if($fromDate || $toDate) {
            $dateParams = array('date' => true);
            if($fromDate) {
                $dateParams['from'] = date('Y-m-d', strtotime($fromDate));
            }
            if($toDate) {
                $dateParams['to'] = date('Y-m-d', strtotime($toDate));
            }
            $orderCollection->addAttributeToFilter('created_at', $dateParams);
        }
        
        return $orderCollection;
    }
    
    /**
     * List abandoned carts!
     */
    public function cartAction()
    {
        $collection = Mage::getResourceModel('reports/quote_collection');
        $data = array();
        $storeIds = $this->_getAllStoreIds();
        
        if (!empty($data)) {
            $collection->prepareForAbandonedReport($storeIds, $data);
        } else {
            $collection->prepareForAbandonedReport($storeIds);
        }
        
        foreach($collection as $cart) {
            /* @var $cart Mage_Sales_Model_Quote */
            $cart->getItemsCollection();
            
            var_dump($cart->getData());
        }
    }
    
    /**
     * Get all sotre ids
     * 
     * @return array
     */
    protected function _getAllStoreIds()
    {
        $allStores = Mage::app()->getStores();
        $ids = array();
        foreach ($allStores as $_eachStoreId => $val) {
            //$_storeCode = Mage::app()->getStore($_eachStoreId)->getCode();
            //$_storeName = Mage::app()->getStore($_eachStoreId)->getName();
            //$_storeId = Mage::app()->getStore($_eachStoreId)->getId();
            $ids[] = Mage::app()->getStore($_eachStoreId)->getId();
        }
        
        return $ids;
    }
}