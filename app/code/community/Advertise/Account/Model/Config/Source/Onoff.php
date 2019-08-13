<?php
/**
 * Used in creating options for On|Off config value selection
 *
 */
class Advertise_Account_Model_Config_Source_Onoff
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('On')),
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Off')),
        );
    }

}