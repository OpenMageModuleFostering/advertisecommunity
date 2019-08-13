<?php
/**
 * Xmlwriter.php
 *
 * Extension to PHPs native XML Writer.
 * We are writing directly to the output buffer every X elements.
 * We are only counting Objects or Arrays in the flush count as "elements"
 *
 * We are currently using the MemoryBuffer, but we could use
 * direct output if we wanted, maybe tinker with this in the future:
 * out->openURI('php://output')
 * We are using memorybuffers as it's easier to change to file writing
 * for debug/loggin.
 * 
 * @package     Dataexport
 */
class Advertise_Dataexport_Model_Xmlwriter extends XMLWriter
{
    /**
     * Number of entries before each flush
     * @var int
     */
    protected $_flushAfter = 20;
    /**
     * Keep count of written elements before we flush
     * @var int
     */
    protected $_flushCount = 0;
    /**
     * Enable flushing buffer? If we disable
     * we won't write the buffer until the end!
     * @var bool
     */
    protected $_flushEnabled = TRUE;
    /**
     * Name of our root element
     * @var string
     */
    protected $_rootElement = 'Advertise';
    /**
     * filename
     * @var string
     */
    protected $_filename;

    /**
     * Constructor
     * 
     * @param   string
     */
    public function  __construct($filename)
    {      
        $this->_filename = $filename;
        
        $this->openMemory();
        //$this->openURI($this->_getFilename());
        $this->startDocument('1.0', 'UTF-8');
        /** only indent in development mode **/
        if(Mage::getIsDeveloperMode()) {
            $this->setIndent(TRUE);
            $this->setIndentString('    ');
        }
        else {
            $this->setIndent(FALSE);
        }
        $this->startElement($this->_rootElement);
    }
    
    /**
     * Get the filename we'll use
     * 
     * @return  string
     */
    protected function _getFilename()
    {
        //if($this->_filename === NULL) {
            // generate??
        //}
        
        return $this->_filename;
    }
    
    /**
     * Set the filename to use
     * 
     * @param   string
     */
    public function setFilename($filename)
    {
        $this->_filename = $filename;
        
        return $this;
    }
    
    /**
     * Write a collection to the XML stream
     *
     * @param   Varien_Data_Collection
     * @param   string                  node name
     * @return  $this
     */
    public function writeCollection(Varien_Data_Collection $collection, $nodeName)
    {
        $this->startElement($nodeName);
        foreach($collection as $item) {
            $this->writeObject($item, $this->_getSingularName($nodeName));
        }
        $this->endElement();

        return $this;
    }
    
    /**
     * Just an alias to save me hunting through the code and changing
     * references to write array.. lazy
     * 
     * @param   mixed
     * @param   string
     */
    public function writeArray($item, $nodeName = NULL)
    {
        $this->writeObject($item, $nodeName);
    }

    /**
     * Recursive function to replace the garbage above..
     * 
     * @param   mixed
     * @param   string
     */
    public function writeObject($item, $nodeName = NULL)
    {
        if(isset($nodeName)) {
            $this->startElement($nodeName);
        }
        /** Array **/
        if(is_array($item)) {
            foreach($item as $field => $val) {
                $this->writeObject($val, $field);
            }
        }
        /** Object */
        elseif($item instanceof Varien_Object) {
            foreach($item->getData() as $field => $val) {
                $this->writeObject($val, $field);
            }
        }
        /** collection **/
        elseif($item instanceof Varien_Data_Collection) {
            
        }
        /** node **/
        else {
            $this->text((string) $item);
        }
        
        if(isset($nodeName)) {
            $this->endElement();
        }
        $this->_doFlush();
        
        return $this;
    }

    /**
     * Generate the Xml document
     */
    public function writeDocument()
    {
        $this->endElement();
        $this->endDocument();
        
        //return $this->outputMemory();
        
        /**
         * @todo if Writing to file:
         */
        file_put_contents($this->_getFilename(), $this->flush(true), FILE_APPEND);
        //return $this->flush();
    }

    /**
     * Check if we need to flush, if we do then
     * let's do it!
     */
    protected function _doFlush()
    {
        if($this->_flushEnabled && $this->_flushCount >= $this->_flushAfter) {
            //echo $this->outputMemory();
            file_put_contents($this->_getFilename(), $this->flush(true), FILE_APPEND);
            $this->_flushCount = 0;
        }

        $this->_flushCount++;
    }

    /**
     * Get the singular version of a collection
     * eg. Customers will return Customer
     *
     * @param   string $name
     * @return  string - singlular name
     */
    protected function _getSingularName($name)
    {
        /** not handle irregular names yet.. **/
        return rtrim($name, 's');
    }
}