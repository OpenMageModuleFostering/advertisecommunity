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
    protected $_filename;
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
        // Set a basic filename just in case...
        $this->_filename = Mage::getModel('dataexport/config')->getTempFolder() . DS . 'generate_' . $_SERVER['HTTP_HOST'] . '_' . date('Y-m-d_His') . '.xml';
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
        Mage::log("export()ing file: ".$this->_filename);
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
        
        try {
            // Zip file before transfer
            $zip = new ZipArchive(); // Load zip library
            $zip_name = $filename.".zip"; // Zip name
            if($zip->open($zip_name, ZIPARCHIVE::CREATE)!==TRUE)
            {
                // Failed to open zip file to load
                Mage::log("Could not create zip file of Adverti.se feed export.");
            } else {
                $filenamenopath = basename($filename);
                $zip->addFile($filename, $filenamenopath); // Adding file into zip
                $zip->close();
                if(file_exists($zip_name))
                {
                    $filename = $zip_name;
                }
            }
        } catch (Exception $ex) {
            Mage::log("Error creating zip file: ".$ex);
        }
        
        
        $urlSuffix = '?filename='.substr($filename, strrpos($filename, DS) + 1);
        $target = $this->_getConfig()->getExportUrl() . $urlSuffix;    
        $putFileSize   = filesize($filename);
        $putFileHandle = fopen($filename, 'rb');

        fseek($putFileHandle, 0); // Unnecessary???
        $referrer = Mage::getBaseUrl();
        //Connecting to website.
        $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $target);
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch, CURLOPT_REFERER, $referrer);
            curl_setopt($ch, CURLOPT_UPLOAD, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 86400); // 1 Day Timeout
            curl_setopt($ch, CURLOPT_INFILE, $putFileHandle);
            curl_setopt($ch, CURLOPT_INFILESIZE, $putFileSize);
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_BUFFERSIZE, 128);
         
        $response = curl_exec ($ch);
        if (curl_errno($ch))
            $msg = curl_error($ch);
        else
            $msg = 'File uploaded successfully.';
        $info = curl_getinfo($ch);
        fclose($putFileHandle);
        curl_close ($ch);
        
        // TODO: Add error handling here, check response, etc.
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
     * Set export type; used for filename prefix
     * 
     * @param String
     */
    public function setExportType($exporttype)
    {
        $this->_filename = Mage::getModel('dataexport/config')->getTempFolder() . DS . $exporttype . '_' . $_SERVER['HTTP_HOST'] . '_' . date('Y-m-d_His') . '.xml';
        $this->_writer->setFilename($this->_filename);
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