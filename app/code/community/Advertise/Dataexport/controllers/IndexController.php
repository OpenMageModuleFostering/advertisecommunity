<?php
/**
 * IndexController.php
 */
class Advertise_Dataexport_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * index action
     */
    public function indexAction()
    {
        exit;
    }
    
    /**
     * We will post data here as a test.
     * We can juust log it!
     */
    public function debugAction()
    {
        $config = Mage::getModel('dataexport/config');
        $post = file_get_contents('php://input');
        
        //echo $post;
        
        if(empty($post)) {
            die('nothing to do.');
        }
        
        if( !is_dir($config->getTempFolder())) {
            mkdir($config->getTempFolder(), 0777);
        }
        
        $filename = $config->getTempFolder() . DS . 'debug_' . time() . '.xml';
        if( ! file_put_contents($filename, $post)) {
            Mage::log('Error writing debug file: ' . $filename);
        }
        
        unset($post);
    }
}