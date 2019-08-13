<?php

$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->addAttributeGroup('catalog_product', 'Default', 'Advertise', 1000);

// the attribute added will be displayed under the group/tab Special Attributes in product edit page
$setup->addAttribute('catalog_product', 'advertise_search', array(
    'group'         => 'Advertise',
    'input'         => 'text',
    'type'          => 'text',
    'label'         => 'advertise_search',
    'backend'       => '',
    'visible'       => 1,
    'required'      => 0,
    'user_defined' => 1,
    'searchable' => 1,
    'filterable' => 0,
    'comparable'    => 1,
    'visible_on_front' => 1,
    'visible_in_advanced_search'  => 0,
    'is_html_allowed_on_front' => 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));
$setup->addAttribute('catalog_product', 'advertise_display', array(
    'group'         => 'Advertise',
    'input'         => 'text',
    'type'          => 'text',
    'label'         => 'advertise_display',
    'backend'       => '',
    'visible'       => 1,
    'required'      => 0,
    'user_defined' => 1,
    'searchable' => 1,
    'filterable' => 0,
    'comparable'    => 1,
    'visible_on_front' => 1,
    'visible_in_advanced_search'  => 0,
    'is_html_allowed_on_front' => 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));
$setup->addAttribute('catalog_product', 'advertise_fb', array(
    'group'         => 'Advertise',
    'input'         => 'text',
    'type'          => 'text',
    'label'         => 'advertise_fb',
    'backend'       => '',
    'visible'       => 1,
    'required'      => 0,
    'user_defined' => 1,
    'searchable' => 1,
    'filterable' => 0,
    'comparable'    => 1,
    'visible_on_front' => 1,
    'visible_in_advanced_search'  => 0,
    'is_html_allowed_on_front' => 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));
$setup->addAttribute('catalog_product', 'advertise_cpc', array(
    'group'         => 'Advertise',
    'input'         => 'text',
    'type'          => 'text',
    'label'         => 'advertise_cpc',
    'backend'       => '',
    'visible'       => 1,
    'required'      => 0,
    'user_defined' => 1,
    'searchable' => 1,
    'filterable' => 0,
    'comparable'    => 1,
    'visible_on_front' => 1,
    'visible_in_advanced_search'  => 0,
    'is_html_allowed_on_front' => 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$installer->endSetup();



