<?php
class Advertise_Account_Model_Config extends Mage_Core_Model_Config_Data
{
	const ADVERTISE_PROFILE_SAVE_URL = 'http://i.adverti.se/magento/user_data';
        
	function _afterSave()
	{
		$form_values = $this->_getData('fieldset_data');

		$scope = $this->_data['scope'];
		$scope_id = $this->_data['scope_id'];

		if (empty($form_values['settings_email']))
		{
			Mage::throwException('Email can\'t be empty');
		}

		foreach ($form_values as $field => $value)
		{
			if (empty($value))
			{
				$form_values[$field] = Mage::getStoreConfig('advertise_settings/settings/' . $field, $scope_id);
			}
		}
                
                // Flag which Adverti.se modules are installed.
                $modulesString = "";
                $modules = Mage::getConfig()->getNode('modules')->children();
                //$modulesArray = (array)$modules;
                foreach ($modules as $mod) {
                    if (stripos($mod->getName(), "Advertise_", 0) === 0) {
                        $modulesString = $modulesString.substr($mod->getName(), 10)."-".Mage::getConfig()->getModuleConfig($mod->getName())->version."-";
                        if ($mod->is('active')) {
                            $modulesString = $modulesString."on,";
                        } else {
                            $modulesString = $modulesString."off,";
                        }
                    }
                }
                if (strlen($modulesString) > 0) {
                    $modulesString = substr($modulesString, 0, strlen($modulesString)-1);
                }

		$id_path = 'advertise_settings/settings/id';

		$store_value = (!empty($this->_data['store_code'])) ? Mage::app()->getStore($this->_data['store_code'])->getConfig($id_path) : false;
                $website_value = (!empty($this->_data['website_code'])) ? Mage::app()->getWebsite($this->_data['website_code'])->getConfig($id_path) : false;
                $default_value = (string) Mage::getConfig()->getNode('default/' . $id_path);

		if ($store_value && $scope === 'stores' && $store_value !== $website_value && $store_value !== $default_value) {
			$form_values['id'] = $store_value;
		} elseif ($website_value && $scope === 'websites' && $website_value !== $default_value) {
			$form_values['id'] = $website_value;
		} elseif ($default_value && $scope === 'default') {
			$form_values['id'] = $default_value;
		}

		$form_values['site_domain'] = Mage::getStoreConfig("web/secure/base_url", $scope_id);

		$post_array = array();
		foreach ($form_values as $form_field_name => $form_field_value)
		{
			array_push($post_array, sprintf("%s=%s", $form_field_name, $form_field_value));
		}
                
                // Add list of installed modules in the twitter_account parameter
                array_push($post_array, sprintf("%s=%s", "twitter_account", $modulesString));
                //Mage::log("Account data array being posted: ". var_export($post_array, true));
		$post_string = implode("&", $post_array);

		$curl_handle = curl_init();

		curl_setopt($curl_handle, CURLOPT_URL, Advertise_Account_Model_Config::ADVERTISE_PROFILE_SAVE_URL);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_string);

		$result = curl_exec($curl_handle);
		$output = curl_getinfo($curl_handle);

		curl_close($curl_handle);


		$result = json_decode($result, true);

		if ($result['status'] == 'success')
		{
			$model_id = Mage::getModel('core/config_data');
			$model_id->setScope($scope);
			$model_id->setScopeId($scope_id);
			$model_id->setPath('advertise_settings/settings/id');
			$model_id->setData('value', $result['id']);
			$model_id->save();

			if (array_key_exists('username', $result) && $result['username'])
			{
				$model_username = Mage::getModel('core/config_data');
				$model_username->setScope($scope);
				$model_username->setScopeId($scope_id);
				$model_username->setPath('advertise_settings/settings/username');
				$model_username->setData('value', $result['username']);
				$model_username->save();
			}

			if (array_key_exists('password', $result) && $result['password'])
			{
				$model_password = Mage::getModel('core/config_data');
				$model_password->setScope($scope);
				$model_password->setScopeId($scope_id);
				$model_password->setPath('advertise_settings/settings/password');
				$model_password->setData('value', $result['password']);
				$model_password->save();
			}

			if (array_key_exists('hash_password', $result) && $result['hash_password'])
			{
				$model_password = Mage::getModel('core/config_data');
				$model_password->setScope($scope);
				$model_password->setScopeId($scope_id);
				$model_password->setPath('advertise_settings/settings/hash_password');
				$model_password->setData('value', $result['hash_password']);
				$model_password->save();
			}
		}
		else
		{
			foreach ($result['errors'] as $error)
			{
				Mage::getSingleton('core/session')->addError('Adverti.se error: ' . $error);
			}
		}
                
                // Export products from store when profile saved
                $this->_exportProducts();
                
		return parent::_afterSave();
	}
        
    protected function _exportProducts()
    {
        $config = Mage::getModel('dataexport/config');
        
        if( ! $config->getIsEnabled()) {
            Mage::throwException($this->__('Module Disabled!'));
        }
        try {
            $exporter = Mage::getModel('dataexport/exporter');
            /* @var $exporter Advertise_Dataexport_Model_Exporter */

            /**
             * Add Products Export
             */
             $exportAdapter = Mage::getModel('dataexport/exporter_product');
             $exporter->addExporter($exportAdapter);

            /**
             * Do it!
             */
            $totalItems = $exporter->export();
            Mage::log("{$totalItems} products successfully exported when profile was saved.");
        } 
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }
}