<?php

/**
 *
 * @category   Advertise
 * @package    Advertise_Account
 */

class Varien_Data_Form_Element_Advertiselicense extends Varien_Data_Form_Element_Abstract {

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
        
        $user = ($_SESSION['admin']['user']);
        $user_data = $user->getData();
        $user['fn'] =$user_data['firstname']; 
       // echo "<pre>";print_r($_SESSION);echo "</pre>";
        $user['n'] =$user_data['lastname']; 
        $user['e'] =$user_data['email']; 
        
        $html="

        <script>
            function get_license() {
                $('advertise_advertise_license').value = '';
                $('msg').style.display = 'none';
                $('msg_err').style.display = 'none';
                //Element.show('loadingmask');
                
                var online_l = $('advertise_advertise_get_online_license').value;
                
                if (online_l==1) {
                    new Ajax.Request('".Mage::getUrl('account/license/retrieve')."', {
                        method: 'post',
                        parameters: {
                            online:online_l,
                            name:$('adv_name').value,
                            firstname:$('adv_firstname').value,
                            email:$('adv_email').value,
                            version:$('advertise_advertise_version').innerHTML
                        },
                        onComplete: function(transport) {

                            var res = eval ('('+transport.responseText+')');
                            if (res['status']==0) {
                                $('msg').style.display = 'block';
                                $('msg_cnt').innerHTML = res['message'];
                            } else if (res['status']==1) {
                                $('msg').style.display = 'block';
                                $('msg_cnt').innerHTML = res['message'];
                                $('advertise_advertise_license').value = res['data'];
                            } else if (res['status']==2) {
                                $('msg_err').style.display = 'block';
                                $('msg_err_cnt').innerHTML = res['message'];
                            }
                        }
                    });
                } else {
                    ";
                    
                    $html .= "window.open('".Mage::getUrl('account/license/retrieve')."online/0/url/'+'/name/'+$('adv_name').value+'/firstname/'+$('adv_firstname').value+'/email/'+$('adv_email').value+'/version/'+$('advertise_advertise_version').innerHTML);
                }
                
            }
        </script>

        <input type='hidden' value='".$user['fn']."' id='adv_firstname'/>
        <input type='hidden' value='".$user['n']."' id='adv_name'/>
        <input type='hidden' value='".$user['e']."' id='adv_email'/>

         <input class=' input-text' style='width:150px' type='text' id='".$this->getHtmlId()."' name='".$this->getName()."' value='".$this->getEscapedValue()."' '".$this->serialize($this->getHtmlAttributes())."/>
         
        <button class='scalable' style='' onclick=\"get_license()\" type='button'>
            <span>Get a license</span>
        </button>

<div id='msg' style='display:none'>
    <ul class='messages'>
        <li class='success-msg'>
            <ul>
                <li>
                    <span id='msg_cnt'></span>
                </li>
            </ul>
        </li>
    </ul>
</div>
<div id='msg_err' style='display:none'>
    <ul class='messages'>
        <li class='error-msg'>
            <ul>
                <li>
                    <span id='msg_err_cnt'></span>
                </li>
            </ul>
        </li>
    </ul>
</div>

        ";
        
        

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
