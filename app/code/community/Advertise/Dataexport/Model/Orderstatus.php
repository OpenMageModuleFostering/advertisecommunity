<?php
/**
 * Orderstatus.php
 *
 * @package     dataexport
 */
class Advertise_Dataexport_Model_Orderstatus
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {        
        $collection = Mage::getResourceModel('sales/order_status_collection');
        return $collection->toOptionArray();
    }
}