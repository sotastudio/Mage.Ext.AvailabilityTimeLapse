<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   SotaStudio
 * @package    SotaStudio_AvailabilityTimeLapse
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Source Model for Backend field
 *
 * @author     Andy Hausmann <andy@sota-studio.de>
 */
class SotaStudio_AvailabilityTimeLapse_Model_System_Config_CatalogInventory_ListDisplayModes
{
	/**
	 * Retrieve option values array
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		$options = array();
		$options = array(
			0 => array(
				'label' => 'Just frontend notifications for customers',
				'value' => 'notification'
			),
			1 => array(
				'label' => 'Frontend notifications and Time Lapse',
				'value' => 'full'
			)
		);
		return $options;
	}

	/**
	 * Retrieve Catalog Config Singleton
	 *
	 * @return Mage_Catalog_Model_Config
	 */
	protected function _getCatalogConfig() {
		return Mage::getSingleton('catalog/config');
	}
}
