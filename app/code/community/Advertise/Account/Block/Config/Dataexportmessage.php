<?php

/**
 * Sets message to explain data export on account settings save
 * @package    Advertise_Account
 */

class Advertise_Account_Block_Config_Dataexportmessage extends Mage_Adminhtml_Block_System_Config_Form_Field 
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        /*
        $modulesString = "";
        $modules = Mage::getConfig()->getNode('modules')->children();
        foreach ($modules as $mod) {
            if (stripos($mod->getName(), "Advertise_", 0) === 0) {
                $modulesString = $modulesString . $mod->getName() . " , version " . Mage::getConfig()->getModuleConfig($mod->getName())->version;
                if ($mod->is('active')) {
                    $modulesString = $modulesString . " (enabled)<br />";
                } else {
                    $modulesString = $modulesString . " (disabled)<br />";
                }
            }
        }
        */
        $message = "<b>Saving this configuration page will create an Adverti.se account for the supplied Email.<br /><br />
                At the same time Magento will export the required product data to Adverti.se.</b>
                <br />
                When your product range changes you can use the button found on the page <br />
                <i>Catalog -> Adverti.se -> Retail Intelligence</i><br />
                to send us updated product data.";
        /*
        if (strlen($modulesString) > 0) {
            $message .= "This data is required for the following installed modules to function:<br />".$modulesString."</b>";
        }
         */
        return $message;
    }
}

?>
