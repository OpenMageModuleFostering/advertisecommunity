<?php
/**
 * Order.php
 * 
 * @package     Dataexport
 */
class Advertise_Dataexport_Model_Exporter_Order extends Varien_Object implements Advertise_Dataexport_Model_Exporter_Interface
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
    
    /**
     * Get the orders we need as a collection
     * 
     * @param   int
     * @return  Varien_Data_Collection
     */
    public function getCollection($storeId = NULL)
    {
        //$storeId = Mage::app()->getStore($storeId)->getId();
        $downloadStatus = Mage::getModel('dataexport/config')->getOrderExportStatus(); //array('complete', 'pending');
        $orderCollection = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('*')
            ->addFieldToFilter('status', array('or' => $downloadStatus))
            //->addFieldToFilter('store_id', array('eq' => $storeId))
        ;
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
            $orderCollection->addAttributeToFilter('created_at', $dateParams);
        }
        
        return $orderCollection;
    }
    
    /**
     * Write any items to the given writer
     * 
     * @param   XMLWriter 
     * @return  int
     */
    public function write(XMLWriter $writer)
    {
        $writer->startElement('Orders');
        $count = 0;
        
        foreach($this->getCollection() as $order) {
            /* @var $order Mage_Sales_Model_Order */
            //$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
            /*$data = array(
                'Id'                        => $order->getCustomerId(),
                'CompanyName'               => $order->getBillingAddress()->getCompany(),
                'CustomerInvoiceAddress'    => $this->_getConvertedAddress($order->getBillingAddress()),
                'CustomerDeliveryAddress'   => $this->_getConvertedAddress($order->getShippingAddress()),
            );*/
            $data = $order->toArray();
            $writer->writeArray($data, 'Order');
            $this->_writeOrderItems($writer, $order);
            $count++;
        }
        
        $writer->endElement(); // Order
        
        return $count;
    }
    
    /**
     * Convert the address from Magento format to an array we need
     *
     * @param   Mage_Sales_Model_Order_Address $address
     * @return  array
     */
    protected function _getConvertedAddress(Mage_Sales_Model_Order_Address $address) 
    {
        $company = $address->getCompany();
        $company = ! empty($company)
            ? $address->getCompany()
            : $address->getFirstname() . ' ' . $address->getLastname();

        return array(
            'Id'            => $address->getId(),
            'Title'         => $address->getPrefix(),
            'Forename'      => $address->getFirstname(),
            'Surname'       => $address->getLastname(),
            'Company'       => $company,
            'Address1'      => $address->getStreet1(),
            'Address2'      => $address->getStreet2(),
            'Town'          => $address->getCity(),
            'County'        => $address->getRegion(),
            'Country'       => $address->getData('country_id'), //getCountry()?
            'Postcode'      => $address->getPostcode(),
            'Email'         => $address->getCustomerEmail(),
            'Telephone'     => $address->getTelephone(),
        );
    }
    
    /**
     * Write iterms out too
     * 
     * @param   XmlWriter
     * @param   Mage_Core_Sales_Order
     */
    protected function _writeOrderItems($writer, $order) 
    {
        $writer->startElement('OrderItems');
        //foreach($order->getItemsCollection() as $item) {
        foreach($order->getAllVisibleItems() as $item) {
            //$totalNet = $item->getQtyOrdered() * $item->getPrice(); // basePrice breaks with multi-currency
            //$to = Varien_Object_Mapper::accumulateByMap($from, $to, $map); // example mapper
            /*$data = array(
                'Id'                    => $item->getId(),
                'Sku'                   => $this->_getItemSkuCode($item),
                'Name'                  => $item->getName(),
                'QtyOrdered'            => $item->getQtyOrdered(),
                'TaxRate'               => $item->getData('tax_percent'),
                'UnitPrice'             => $item->getPrice(),
                'UnitDiscountAmount'    => $item->getData('discount_amount'),
                'UnitDiscountPercentage'=> $item->getData('discount_percent'),
            );*/
            
            $data = $item->getData();
            $writer->writeArray($data, 'Item');
        }
        $writer->endElement(); // OrderItems
    }
    
    /**
     * Get the Sku code to use for a particular item.
     * If we are using bundles / configurable items we need to search
     * because by default we get the parents Sku...
     * 
     * @param   Mage_Sales_Model_Order_Item
     * @return  string
     */
    protected function _getItemSkuCode($item)
    {
        /**
         * Use Child SKU if it's a configurable product..etc
         */
        if($options = $item->getData('product_options')) {
            $optionsData = unserialize($options);
            if(isset($optionsData['simple_sku'])) {
                return $optionsData['simple_sku'];
            }
        }
        
        return $item->getSku();
    }
}