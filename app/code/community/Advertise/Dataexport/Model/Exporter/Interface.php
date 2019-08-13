<?php
/**
 * Interface.php
 * 
 * Interface for export "adapters"
 * 
 * @package     Dataexport
 */
interface Advertise_Dataexport_Model_Exporter_Interface
{
    //public function getCollection();
    
    /**
     * Write any items to the given writer
     * 
     * @param   XMLWriter
     * @return  int
     */
    public function write(XMLWriter $writer);
    
    /**
     * Get the name of the items we're writing in this adpater.
     * 
     * @return  string 
     */
    //public function getObjectName();
}