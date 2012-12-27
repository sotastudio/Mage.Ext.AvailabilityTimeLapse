<?php

/**
 * Init and start setup
 */

$installer = $this;
$installer->startSetup();


/**
 * Configuration
 */

# Get Entity Type Id fot catalog categories
# Alternative: directly use Mage_Catalog_Model_Category::ENTITY instead of resolved $catalogEntityTypeId.
$productEntityTypeId = $installer->getEntityTypeId('catalog_product');

# For Development purposes only
//$installer->removeAttribute($productEntityTypeId, 'time_lapse_begin');
//$installer->removeAttribute($productEntityTypeId, 'time_lapse_end');

# Group definition for this module
$defaultAttributeGroup = 'Availability';

# Add Group for fields
$installer->addAttributeGroup($productEntityTypeId, 'Default', $defaultAttributeGroup , 100);
# Add necessary fields into group

# Attribute default config - every property can be overridden
$defaultAttributeConfig = array(
	'type' 						=> 'datetime',
	'input'						=> 'date',
	'backend'					=> 'eav/entity_attribute_backend_datetime',
	'frontend'  				=> 'eav/entity_attribute_frontend_datetime',
	'visible' 					=> true,
	'visible_on_front' 			=> false,
	'required' 					=> false,
	'user_defined' 				=> false,
	'used_in_product_listing' 	=> true,
	'group' 					=> $defaultAttributeGroup,
	'global' 					=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'apply_to'					=> 'bundle,simple,configurable',
	# Other
	'default'                    => null,
	'source'                     => null,
	'searchable'                 => false,
	'visible_in_advanced_search' => false,
	'filterable'                 => false,
	'filterable_in_search'       => false,
	'comparable'                 => false,
	'is_html_allowed_on_front'   => true,
	'is_configurable'            => false,
	'used_for_sort_by'           => false,
	'position'                   => 0,
	'used_for_promo_rules'       => false,
);


/**
 * Attribute definitions
 */

$installer->addAttribute(
	$productEntityTypeId,
	'time_lapse_begin',
	array_merge($defaultAttributeConfig, array(
		'label' 	=> 'Time-lapse begin',
		'sort'		=> 10,
	))
);

$installer->addAttribute(
	$productEntityTypeId,
	'time_lapse_end',
	array_merge($defaultAttributeConfig, array(
		'label' 	=> 'Time-lapse end',
		'sort'		=> 20,
	))
);


/**
 * End setup
 */

$installer->endSetup();