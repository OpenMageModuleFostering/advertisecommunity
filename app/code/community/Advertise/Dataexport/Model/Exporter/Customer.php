<?php
/**
 * Customer.php
 * 
 * @package     Dataexport
 */
class Advertise_Dataexport_Model_Exporter_Customer extends Varien_Object implements Advertise_Dataexport_Model_Exporter_Interface
{
    /**
     * Get the collection
     * 
     * @return 
     */
    public function getCollection()
    {
        $collection = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('*')
        ;
        
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
        $writer->startElement('Customers');
        $count = 0;
        
        foreach($this->getCollection() as $customer) {
            /* @var $customer Mage_Customer_Model_Customer */
            $data = $customer->toArray();
            $billing = $customer->getDefaultBillingAddress();
            $billing = $billing === FALSE 
                ? FALSE
                : $billing->toArray();
            
            $shipping =  $customer->getDefaultShippingAddress();
            $shipping = $shipping === FALSE 
                ? FALSE
                : $shipping->toArray();
            
            $data['billing_address'] = $billing;
            $data['shipping_address'] = $shipping;
            $writer->writeArray($data, 'Customer');
            $count++;
        }
        
        $writer->endElement(); // Customer
        
        return $count;
    }
}