<?php
/**
 * Cart.php
 * 
 * @package     Dataexport
 */
class Advertise_Dataexport_Model_Exporter_Cart extends Varien_Object implements Advertise_Dataexport_Model_Exporter_Interface
{
    /**
     * @var string
     */
    protected $_from;
    /**
     * @var string
     */
    protected $_to;
    
    /**
     * Get the abandoned carts
     */
    public function getCollection()
    {
        $collection = Mage::getResourceModel('reports/quote_collection');
        $data = array();
        $storeIds = $this->_getAllStoreIds();
        
        $collection->prepareForAbandonedReport($storeIds); // $data param can be passed too

        /**
         * Handle to and from dates!
         */
        if($this->_from || $this->_to) {
            $dateParams = array('date' => true);
            if($this->_from) {
                $dateParams['from'] = date('Y-m-d', strtotime($this->_from));
            }
            if($this->_to) {
                $dateParams['to'] = date('Y-m-d', strtotime($this->_to));
            }
            $collection->addAttributeToFilter('created_at', $dateParams);
        }
        
        return $collection;
    }
    
    /**
     * Write any items to the given writer
     * 
     * @param   XMLWriter 
     * @return  int
     */
    public function write(XMLWriter $writer)
    {
        $writer->startElement('Carts');
        $count = 0;
        
        foreach($this->getCollection() as $cart) {
            /* @var $cart Mage_Sales_Model_Quote */
            $data = $cart->toArray();
            $writer->writeArray($data, 'Cart');
            $this->_writeItems($writer, $cart);
            $count++;
        }
        
        $writer->endElement(); // Carts
        
        return $count;
    }
    
    /**
     * Write the queote items.
     * 
     * @param   Xmlwriter
     * @param   Mage_Sales_Model_Quote
     */
    protected function _writeItems($writer, $cart)
    {
        $writer->startElement('QuoteItems');
        
        foreach($cart->getItemsCollection() as $item) {
            /* @var $cart Mage_Sales_Model_Quote_Item */            
            $data = $item->getData();
            $writer->writeArray($data, 'Item');
        }
        
        $writer->endElement(); // OrderItems
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
    
    /**
     * Set the date range
     * 
     * @param   string
     * @param   string
     */
    public function setDateRange($from, $to)
    {
        $this->_from = $from;
        $this->_to = $to;
        
        return $this;
    }
}