<?php
set_time_limit(0);
ini_set('memory_limit', '512M');
//Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
/**
 * Exporter.php
 * 
 * @package     Dataexport
 */
class Advertise_Dataexport_Model_Exporter extends Varien_Object
{
    /**
     * @var Xmlwriter
     */
    protected $_writer;
    /**
     * @var Advertise_Dataexort_Model_Config 
     */
    protected $_config;
    /**
     * Exporters to use
     * 
     * @var array
     */
    protected $_exporters = array();
    /**
     * @var string
     */
    protected $_filname;
    /**
     * Remove temp data files after import?
     * 
     * @var bool
     */
    protected $_removeTempFiles = FALSE;
    
    /**
     * Constructor 
     */
    public function __construct() 
    {
        parent::__construct();
        
        /**
         * @todo change this!!
         */
        $this->_filename = Mage::getModel('dataexport/config')->getTempFolder() . DS . 'generate_' . time() . '.xml';
        
        $this->_writer = new Advertise_Dataexport_Model_Xmlwriter($this->_filename);
        $this->_config = Mage::getModel('dataexport/config');
    }

    /**
     * Do the export! GO GO GO
     * 
     * @return  void
     */
    public function export()
    {
        $totalItems = 0;
        /**
         * 1) Generate the XML feed as a file
         */
        foreach($this->_getExporters() as $exporter) {
            /* @var $exporter Advertise_Dataexport_Model_Exporter_Interface */
            $totalItems += $exporter->write($this->_getWriter());
        }
        $this->_getWriter()->writeDocument();
        /**
         * 2) Send the feed
         */
        $this->_sendFeed($this->_filename);
        Mage::log('Data export Completed.');
        
        return $totalItems;
    }
    
    /**
     * Send the feed!
     * 
     * @param   filename
     */
    protected function _sendFeed($filename)
    {
        if( ! file_exists($filename)) {
            Mage::throwException($this->__('Feed Not Found! ' . $filename));    
        }
        
        $target = $this->_getConfig()->getExportUrl();    
        //$filename = $this->_getConfig()->getTempFolder() . DS . 'test.xml';
        $putFileSize   = filesize($filename);
        $putFileHandle = fopen($filename, "r");

        $adapter = new Zend_Http_Client_Adapter_Curl();
        $client = new Zend_Http_Client($target);
        $client->setAdapter($adapter);
        //$client->setHeaders('Content-Type','text/xml');
        
        $adapter->setConfig(array(
            'curloptions' => array(
                CURLOPT_INFILE          => $putFileHandle,
                CURLOPT_INFILESIZE      => (string) $putFileSize,
                //CURLOPT_SSL_VERIFYHOST  => 0,
                //CURLOPT_SSL_VERIFYPEER  => 0,
            )
        ));
        
        $response = $client->request(Zend_Http_Client::PUT);
    }
    
    /**
     * Add an exporter
     * 
     * @param Advertise_Dataexport_Model_Exporter_Interface
     */
    public function addExporter(Advertise_Dataexport_Model_Exporter_Interface $exporter)
    {
        $this->_exporters[] = $exporter;
        
        return $this;
    }
    
    /**
     * Get our exporters
     * 
     * @return array
     */
    protected function _getExporters()
    {
        return $this->_exporters;
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
            $ids[] = Mage::app()->getStore($_eachStoreId)->getId();
        }
        
        return $ids;
    }
    
    /**
     * Get the config model
     * 
     * @return Advertise_Dataexport_Model_Config
     */
    protected function _getConfig()
    {
        return $this->_config;
    }
    
    /**
     * Get the writer
     * 
     * @return Xmlwriter
     */
    protected function _getWriter()
    {
        return $this->_writer;
    }
}