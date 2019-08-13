<?php

/**
 *
 * @category   Advertise
 * @package    Advertise_Account
 */

class Varien_Data_Form_Element_Advertisetemplate extends Varien_Data_Form_Element_Abstract {

    /**
     * Init Element
     *
     * @param array $attributes
     */
    public function __construct($attributes=array()) {
        parent::__construct($attributes);
        $this->setType('text');
        $this->setExtType('text');
    }

    /**
     * Retrieve allow attributes
     *
     * @return array
     */
    public function getHtmlAttributes() {
        return array('type', 'name', 'class', 'style', 'checked', 'onclick', 'onchange', 'disabled');
    }

    /**
     * Prepare value list
     *
     * @return array
     */
    protected function _prepareValues() {
        $options = array();
        $values = array("");
        return $values;
    }

    /**
     * Retrieve HTML
     *
     * @return string
     */
    public function getElementHtml() {
        $values = $this->_prepareValues();

        if (!$values) {
            return '';
        }
        $id = $this->getHtmlId();
       
        $html="

        <script>
            new Event.observe(window, 'load', function() { $('row_advertise_advertise_template').style.display='none'; });
        </script>

         <input class=' input-text'  type='hidden' id='".$this->getHtmlId()."' name='".$this->getName()."' value='".$this->getEscapedValue()."' '".$this->serialize($this->getHtmlAttributes())."/>
         
        ";
        
        
         $html .= $this->getAfterElementHtml();

        return $html;
    }

    public function getOnclick($value) {
        $this->setValue("ok");
        if ($onclick = $this->getData('onclick')) {
            return str_replace('$value', $value, $onclick);
        }
        return;
    }

}
