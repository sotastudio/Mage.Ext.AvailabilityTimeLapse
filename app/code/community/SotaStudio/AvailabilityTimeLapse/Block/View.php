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
	// System Config Paths for this module
	const confPath_Enable 		= 'cataloginventory/time_lapse/enable';
	const confPath_DateMode 	= 'cataloginventory/time_lapse/date_mode';
	const confPath_DisplayMode 	= 'cataloginventory/time_lapse/display_mode';


	/**
	 * Small helper to get Store Config Flags.
	 *
	 * @param $flag
	 * @return bool
	 */
	protected function getStoreConfigFlag($flag)
	{
		return Mage::getStoreConfig($flag);
	}

	/**
	 * Checks whether the module is enabled or not.
	 * This is being set up via backend (System -> Config).
	 *
	 * @return bool
	 */
	protected function isEnabled()
	{
		return ($this->getStoreConfigFlag(self::confPath_Enable)) ? true : false;
	}

	/**
	 * Return the backend setting of the date mode for this module.
	 * This is being set up via backend (System -> Config).
	 *
	 * Return value 'day' means "to the day": the day is important to any calculations.
	 * Return value 'month' means "to the month": the day is irrelevant to calculations.
	 *  Begin at the fist day of the start month; end at the last day of the end month.
	 *
	 * @return string day|month
	 */
	protected function getDateMode()
	{
		return $this->getStoreConfigFlag(self::confPath_DateMode);
	}

	/**
	 * Checks both dates for validity
	 *
	 * @return bool
	 */
	protected function hasValidDates()
	{
		return ( $this->isValidDate($this->getProduct()->getTimeLapseBegin())
			     && $this->isValidDate($this->getProduct()->getTimeLapseEnd()) )
			? true
			: false;
	}

	/**
	 * Returns an array from the given date.
	 * The format reads as follows:
	 *  array('y' => x, 'm' => y, 'd' => z)
	 *
	 * @param string  $date  The given date as string.
	 * @return array  The generated Array from date.
	 */
	protected function getArrayFromDate($date)
	{
		$date = strtotime($date);
		$dateArray = array(
			'y' => date('y', $date),
			'm' => date('m', $date),
			'd' => date('d', $date),
		);
		return $dateArray;
	}

	/**
	 * Evaluated a date
	 *
	 * @param $date
	 * @return bool
	 * @todo Maybe throw it into the HelperCollection
	 */
	protected function isValidDate($date)
	{
		$date = $this->getArrayFromDate($date);
		return ( checkdate($date['m'], $date['d'], $date['y'] && (int)$date['y'] > 70) ) ? true : false;
	}

	/**
	 * Adds teh specified amount of months to the given month of the date.
	 *
	 * @param mixed  $date  Months of the Date.
	 * @param integer  $amount  Amount of Months.
	 */
	protected function addMonthsToDate(&$date, $amount)
	{
		if (strlen($date) === 4) {
			list($m, $d) = array(substr($date, 0, 2), substr($date, 2, 2));
			$date = strval($m+$amount) . $d;
		} else if (strlen($date) === 2) {
			$date = $date + $amount;
		}
	}

	/**
	 *
	 *
	 * @return bool
	 */
	public function isWithinAvailability()
	{
		$dateMode_ToTheDay = ($this->getDateMode() == 'day') ? true : false;

		list($currentDate, $timeLapseBegin, $timeLapseEnd) = array(
			Mage::helper('core')->formatDate(),
			$this->getProduct()->getTimeLapseBegin(),
			$this->getProduct()->getTimeLapseEnd()
		);

		$arrDates = array(
			'current' 	=> $this->getArrayFromDate($currentDate),
			'begin'		=> $this->getArrayFromDate($timeLapseBegin),
			'end'		=> $this->getArrayFromDate($timeLapseEnd)
		);

		if ( $arrDates['begin']['m'] == $arrDates['end']['m'] && $dateMode_ToTheDay == false
			|| $dateMode_ToTheDay == true ) {
			list($arrDates['current']['m'], $arrDates['begin']['m'], $arrDates['end']['m']) = array(
				(string)$arrDates['current']['m'] . $arrDates['current']['d'],
				(string)$arrDates['begin']['m'] . $arrDates['begin']['d'],
				(string)$arrDates['end']['m'] . $arrDates['end']['d']
			);
		}

		// If end date's lower then start date, it seems to be within the next year.
		// Additionally, if the CURRENT date's lower then the end date, we need to populate both by 12 months.
		if ( (int)$arrDates['end']['m'] < (int)$arrDates['begin']['m']
			 && (int)$arrDates['current']['m'] < (int)$arrDates['end']['m'] ) {
			$this->addMonthsToDate($arrDates['current']['m'], 12);
			$this->addMonthsToDate($arrDates['end']['m'], 12);

		// If end date's lower then start date, it seems to be within the next year.
		// Therefore we need to add 12 months to the end date.
		} else if ( intval($arrDates['end']['m']) < intval($arrDates['begin']['m']) ) {
			$this->addMonthsToDate($arrDates['end']['m'], 12);

		}

		$status = ( $arrDates['current']['m'] >= $arrDates['begin']['m']
				    && ($arrDates['current']['m'] <= $arrDates['end']['m'] ) )
			? true
			: false;

		return $status;
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


	/**
	 * Checks whether this products has Time-lapse data at all.
	 *
	 * @return bool
	 */
	public function hasTimeLapseConstraint()
	{
		return ( $this->isEnabled() && $this->hasValidDates() ) ? true : false;
	}

	/**
	 * Returns formatted begin date of Time-lapse.
	 *
	 * @param string  $format  short|medium|long|full
	 * @return string  Formatted date - depends on chosen timezone.
	 */
	public function getTimeLapseBegin($format = 'short')
	{
		return Mage::helper('core')->formatDate( $this->getProduct()->getTimeLapseBegin(), $format );
	}

	/**
	 * Returns formatted end date of Time-lapse.
	 *
	 * @param string  $format  short|medium|long|full
	 * @return string  Formatted date - depends on chosen timezone.
	 */
	public function getTimeLapseEnd($format = 'short')
	{
		return Mage::helper('core')->formatDate( $this->getProduct()->getTimeLapseEnd(), $format );
	}

}