<?php

defined('_JEXEC') or die('Restricted access');

class EmspayHelper
{

    /**
     * GINGER_ENDPOINT used for create Ginger client
     */
    const GINGER_ENDPOINT = 'https://api.online.emspay.eu';
    const PHYSICAL = 'physical';
    const SHIPPING_FEE = 'shipping_fee';

    /**
     * @param string $amount
     * @return int
     * @since v1.0.0
     */
    public static function getAmountInCents($amount)
    {
        return (int) round($amount * 100);
    }

    /**
     * @return mixed
     * @since v1.0.0
     */
    public static function getLocale()
    {
        $lang = JFactory::getLanguage();
        return str_replace('-', '_', $lang->getTag());
    }

    /**
     * Method obtains plugin information from the manifest file
     *
     * @param string $name
     * @return string
     * @since v1.0.0
     */
    public static function getPluginVersion($name)
    {
        $xml = JFactory::getXML(JPATH_SITE."/plugins/vmpayment/{$name}/{$name}.xml");

        return sprintf('Joomla Virtuemart v%s', (string) $xml->version);
    }
    
    /**
     * @param string $orderId
     * @return type
     * @since v1.0.0
     */
    public static function getOrderDescription($orderId) 
    {
        return sprintf(\JText::_("EMSPAY_LIB_ORDER_DESCRIPTION"), $orderId, JFactory::getConfig()->get('sitename'));
    }
    
     /**
     * @param string $orderId
     * @return type
     * @since v1.0.0
     */
    public static function getReturnUrl($orderId) 
    {
        return sprintf('%s?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&pm=%d', \JURI::base(), intval($orderId));
    }

    /**
     * Get CA certificate path
     *
     * @return bool|string
     */
    public static function getCaCertPath(){
	  return realpath(JPATH_LIBRARIES . '/emspay/ginger-php/assets/cacert.pem');
    }

    /**
     * Returns a new array with all elements which have a null value removed.
     *
     * @param array $array
     * @return array
     */
    public static function withoutNullValues(array $array)
    {
	  static $fn = __FUNCTION__;

	  foreach ($array as $key => $value) {
		if (is_array($value)) {
		    $array[$key] = self::$fn($array[$key]);
		}

		if (empty($array[$key]) && $array[$key] !== '0' && $array[$key] !== 0) {
		    unset($array[$key]);
		}
	  }

	  return $array;
    }

    /**
     * @return string
     */
    public static function getPaymentCurrency()
    {
	  return 'EUR';
    }
}
