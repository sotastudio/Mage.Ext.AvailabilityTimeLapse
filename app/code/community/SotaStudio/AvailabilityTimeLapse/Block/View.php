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
 * @category    SotaStudio
 * @package     SotaStudio_AvailabilityTimeLapse
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product View block
 *
 * @author   Andy Hausmann <andy@sota-studio.de>
 */
class SotaStudio_AvailabilityTimeLapse_Block_View extends Mage_Catalog_Block_Product_Abstract
{
	// Field definitions
	const attrTimeLapseBegin	= 'time_lapse_begin';
	const attrTimeLapseEnd		= 'time_lapse_end';
	// System Config Paths for this module
	const confPath_Enable 			= 'cataloginventory/time_lapse/enable';
	const confPath_DisplayMode 		= 'cataloginventory/time_lapse/display_mode';
	const confPath_BuyerProtection 	= 'cataloginventory/time_lapse/buyer_protection';
	const format_BuyerProtection	= 'day';
	// Date format constants
	const dateFormat_Raw	= 'raw';
	const dateFormat_Short	= 'short';
	const dateFormat_Medium	= 'medium';


	protected $isApplicable		= null;
	protected $currentDate		= null;
	protected $timeLapseBegin	= null;
	protected $timeLapseEnd		= null;


	public function __construct()
	{
		if ($this->hasTimeLapseConstraint()) {
			$this->isApplicable = true;
			setlocale(LC_TIME, Mage::getModel('core/locale')->getLocale());
			$this->initAvailabilityTimeLapse();
		} else {
			$this->isApplicable = false;
		}
	}


	public function isApplicable()
	{
		return ($this->isApplicable != null) ? $this->isApplicable : false;
	}

	/**
	 * Checks whether this products has Time-lapse data at all.
	 *
	 * Most important function, because it checks whether this module should be called or not.
	 * Ensures the module is set to active in the backend and current product has Time-lapse valid definitions.
	 *
	 * @return bool
	 */
	public function hasTimeLapseConstraint()
	{
		return ( $this->isEnabled() && $this->hasValidDates() ) ? true : false;
	}

	/**
	 * Checks whether the module is enabled or not.
	 * This is being set up via backend (System -> Config).
	 *
	 * @return bool
	 */
	protected function isEnabled()
	{
		return ($this->getStoreConfig(self::confPath_Enable)) ? true : false;
	}

	/**
	 * Checks both dates for validity
	 *
	 * @return bool
	 */
	protected function hasValidDates()
	{
		list($tlb, $tle) = array(
			$this->getProduct()->getAttributeText(self::attrTimeLapseBegin),
			$this->getProduct()->getAttributeText(self::attrTimeLapseEnd)
		);
		return ( !empty($tlb) && !empty($tle) ) ? true : false;
	}

	/**
	 *
	 *
	 * @param bool  $extend  Flag which extends the start date by the buyer protection if set to true.
	 * @return bool
	 */
	protected function isWithinAvailability($extend = false)
	{
		$dateMode_ToTheDay = true;
		$initialDateFormat = self::dateFormat_Medium;

		list($currentDate, $timeLapseBegin, $timeLapseEnd) = array(
			Mage::helper('core')->formatDate($this->currentDate, $initialDateFormat),
			$this->getTimeLapseBegin($initialDateFormat),
			$this->getTimeLapseEnd($initialDateFormat)
		);

		if ($extend) {
			$bp = $this->getBuyerProtection();
			$timeLapseBegin = strtotime('-' . $bp, strtotime($timeLapseBegin));
			$timeLapseBegin = Mage::helper('core')->formatDate(
				date('Y-m-d', $timeLapseBegin),
				$initialDateFormat
			);
		}

		/*
		echo '<pre>';
		var_dump($currentDate, $timeLapseBegin, $timeLapseEnd);
		echo '</pre>';
		*/

		list($currentDate, $timeLapseBegin, $timeLapseEnd) = array(
			Mage::helper('helper_collection/date')->getArrayFromDate($currentDate),
			Mage::helper('helper_collection/date')->getArrayFromDate($timeLapseBegin),
			Mage::helper('helper_collection/date')->getArrayFromDate($timeLapseEnd)
		);

		$arrDates = array(
			'current' 	=> $currentDate['y'] . $currentDate['m'] . $currentDate['d'],
			'begin'		=> $timeLapseBegin['y'] . $timeLapseBegin['m'] . $timeLapseBegin['d'],
			'end'		=> $timeLapseEnd['y'] . $timeLapseEnd['m'] . $timeLapseEnd['d']
		);

		/*
		echo '<pre>';
		var_dump($arrDates['current'], $arrDates['begin'], $arrDates['end']);
		echo '</pre>';
		*/

		$status = ( $arrDates['current'] >= $arrDates['begin']
			&& ($arrDates['current'] <= $arrDates['end'] ) )
			? true
			: false;

		return $status;
	}

	protected function initAvailabilityTimeLapse()
	{
		// Time Lapse Raw Data - had to give it a shortcut.
		$tlrd = array(
			'begin' => explode(' - ', $this->getProduct()->getAttributeText(self::attrTimeLapseBegin)),
			'end'	=> explode(' - ', $this->getProduct()->getAttributeText(self::attrTimeLapseEnd))
		);

		$this->currentDate 		= date('Y-m-d');
		$this->timeLapseBegin 	= date('Y-m-d', strtotime(date('Y') . '-' . $tlrd['begin'][0]));
		$this->timeLapseEnd 	= date('Y-m-t', strtotime(date('Y') . '-' . $tlrd['end'][0]));

		// Will apply if end date's lower than start date
		// In this case, end date is within the next year
		if ($tlrd['begin'][0] > $tlrd['end'][0]) {
			$this->timeLapseBegin = Mage::helper('helper_collection/date')->calcDate($this->timeLapseBegin, '-1 year');
		}
	}

	/**
	 * Small helper to get Store Config Flags.
	 *
	 * @param $flag
	 * @return bool
	 */
	protected function getStoreConfig($flag)
	{
		return Mage::getStoreConfig($flag);
	}

	protected function getBuyerProtection()
	{
		return $this->getStoreConfig(self::confPath_BuyerProtection) . ' ' . self::format_BuyerProtection;
	}

	/**
	 * Returns the defined display mode for notifications and hints.
	 *
	 * @return bool
	 * @deprecated
	 */
	public function getDisplayMode()
	{
		return $this->getStoreConfig(self::confPath_DisplayMode);
	}

	/**
	 * Returns formatted begin date of Time-lapse.
	 *
	 * @param string  $format  short|medium|long|full
	 * @return string  Formatted date - depends on chosen timezone.
	 */
	public function getTimeLapseBegin($format = self::dateFormat_Short)
	{
		return ($format === self::dateFormat_Raw)
			? $this->timeLapseBegin
			: Mage::helper('core')->formatDate(
				$this->timeLapseBegin,
				$format
			);
	}

	/**
	 * Returns formatted end date of Time-lapse.
	 *
	 * @param string  $format  short|medium|long|full
	 * @return string  Formatted date - depends on chosen timezone.
	 */
	public function getTimeLapseEnd($format = self::dateFormat_Short)
	{
		return ($format === self::dateFormat_Raw)
			? $this->timeLapseEnd
			: Mage::helper('core')->formatDate(
				$this->timeLapseEnd,
				$format
			);
	}

	public function getMonthNameFromDate($date)
	{
		return strftime('%B', strtotime($date));
	}

	/**
	 * Retrieve current product model
	 *
	 * @return Mage_Catalog_Model_Product
	 */
	public function getProduct()
	{
		if (!Mage::registry('product') && $this->getProductId()) {
			$product = Mage::getModel('catalog/product')->load($this->getProductId());
			Mage::register('product', $product);
		}
		return Mage::registry('product');
	}

}