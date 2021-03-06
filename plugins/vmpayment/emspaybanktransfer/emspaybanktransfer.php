<?php

defined('_JEXEC') or die('Restricted access');

use Emspay\Lib\EmspayVmPaymentPlugin;

/**
 *   ╲          ╱
 * ╭──────────────╮  COPYRIGHT (C) 2018 GINGER PAYMENTS B.V.
 * │╭──╮      ╭──╮│
 * ││//│      │//││  This software is released under the terms of the
 * │╰──╯      ╰──╯│  MIT License.
 * ╰──────────────╯
 *   ╭──────────╮    https://www.gingerpayments.com/
 *   │ () () () │
 *
 * @category    Ginger
 * @package     Ginger Virtuemart
 * @author      Ginger Payments B.V. (plugins@gingerpayments.com)
 * @version     v1.3.1
 * @copyright   COPYRIGHT (C) 2018 GINGER PAYMENTS B.V.
 * @license     The MIT License (MIT)
 * @since       v1.0.0
 **/

ini_set('display_errors', 'Off');
if (!class_exists('vmPSPlugin')) {
    require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
}

JLoader::registerNamespace('Emspay', JPATH_LIBRARIES . '/emspay');
JImport('emspay.vendor.autoload');
JImport('emspay.emspayhelper');

class plgVmPaymentEmspaybanktransfer extends EmspayVmPaymentPlugin
{

    /**
     * Constructor
     *
     * @param object $subject The object to observe
     * @param array  $config  An array that holds the plugin configuration
     */
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
    }

    /**
     * @param $cart
     * @param $order
     * @return bool|null
     * @since v1.0.0
     */
    public function plgVmConfirmedOrder($cart, $order)
    {
        if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
            return null; // Another method was selected, do nothing
        }
        if (!$this->selectedThisElement($method->payment_element)) {
            return false;
        }

        if (!class_exists('VirtueMartModelOrders')) {
            require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
        }

        vmLanguage::loadJLang('com_virtuemart', true);
        vmLanguage::loadJLang('com_virtuemart_orders', true);
        
        $this->getPaymentCurrency($method, $order['details']['BT']->payment_currency_id);
        $currency_code_3 = shopFunctions::getCurrencyByID($method->payment_currency, 'currency_code_3');
        $email_currency = $this->getEmailCurrency($method);
        $totalInPaymentCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total, $method->payment_currency);

        if (!empty($method->payment_info)) {
            $lang = JFactory::getLanguage();
            if ($lang->hasKey($method->payment_info)) {
                $method->payment_info = vmText::_($method->payment_info);
            }
        }

        $totalInCents = EmspayHelper::getAmountInCents($totalInPaymentCurrency['value']);
        $orderId = $order['details']['BT']->virtuemart_order_id;
        $description = EmspayHelper::getOrderDescription($orderId);
        $customer = \Emspay\Lib\CommonCustomerFactory::create(
                        $order['details']['BT'],
                        \EmspayHelper::getLocale(),
                        filter_var(\JFactory::getApplication()->input->server->get('REMOTE_ADDR'), FILTER_VALIDATE_IP)
        );
        $plugin = ['plugin' => EmspayHelper::getPluginVersion()];
        $webhook =$this->getWebhookUrl(intval($order['details']['BT']->virtuemart_paymentmethod_id));
	  $returnUrl = EmspayHelper::getReturnUrl(intval($order['details']['BT']->virtuemart_paymentmethod_id));

	  try {
            $response = $this->getGingerClient()->createOrder(array_filter([
			'amount' => $totalInCents,                           // Amount in cents
			'currency' => $currency_code_3,                      // Currency
			'transactions' => [
				[
					'payment_method' => 'bank-transfer'      // Payment method
				]
			],
			'merchant_order_id' => $orderId,                     // Merchant Order Id
			'description' => $description,                       // Description
			'return_url' => $returnUrl,                          // Return URL
			'customer' => $customer->toArray(),                  // Customer Information
			'extra' => ['plugin' => $plugin],                    // Extra information
			'webhook_url' => $webhook                            // Webhook URL
		]));

        } catch (\Exception $exception) {
            $html = "<p>" . JText::_("EMSPAY_LIB_ERROR_TRANSACTION") . "</p><p>Error: ".$exception->getMessage()."</p>";
            $this->processFalseOrderStatusResponse($html);
        }

        if ($response['status'] == 'error') {
            $html = "<p>" . JText::_("EMSPAY_LIB_ERROR_TRANSACTION") . "</p><p>Error: ".$response['transactions'][0]['reason']."</p>";
            $this->processFalseOrderStatusResponse($html);
        }

        if (!$response['id']) {
            $html = "<p>" . JText::_("EMSPAY_LIB_ERROR_TRANSACTION") . "</p><p>Error: Response did not include id!</p>";
            $this->processFalseOrderStatusResponse($html);
        }

        $dbValues['payment_name'] = $this->renderPluginName($method) . '<br />' . $method->payment_info;
        $dbValues['order_number'] = $order['details']['BT']->order_number;
        $dbValues['virtuemart_paymentmethod_id'] = $order['details']['BT']->virtuemart_paymentmethod_id;
        $dbValues['cost_per_transaction'] = $method->cost_per_transaction;
        $dbValues['cost_min_transaction'] = $method->cost_min_transaction;
        $dbValues['cost_percent_total'] = $method->cost_percent_total;
        $dbValues['payment_currency'] = $currency_code_3;
        $dbValues['email_currency'] = $email_currency;
        $dbValues['payment_order_total'] = $totalInPaymentCurrency['value'];
        $dbValues['tax_id'] = $method->tax_id;
        $dbValues['ginger_order_id'] = $response['id'];

        $this->storePSPluginInternalData($dbValues);

        $virtuemart_order_id = $this->getOrderIdByGingerOrder($response['id']);
        $virtuemart_order_number = $this->getOrderNumberByGingerOrder(vRequest::get('order_id'));
        $statusSucceeded = $this->updateOrder($response['status'], $virtuemart_order_id);

        if ($statusSucceeded) {
            $html = $this->renderByLayout('post_payment', array(
                        'total_to_pay' => $totalInPaymentCurrency['display'],
                        'reference' => $this->getGingerPaymentReference($response),
                        'description' => "<p>" . EmspayHelper::getOrderDescription($virtuemart_order_id) . "</p>",
			      'bank_information' => "IBAN: ".$this->getGingerPaymentIban($response).
	                                          "<br/>BIC: ".$this->getGingerPaymentBic($response).
	                                          "<br/>Account holder: ".$this->getGingerPaymentHolderName($response).
	                                          "<br/>City: ".$this->getGingerPaymentHolderCity($response).
	                                          "<br/>Country: ".$this->getGingerPaymentHolderCountry($response)
            ));
            $this->emptyCart(null, $virtuemart_order_id);
            vRequest::setVar('html', $html);
            return true;
        }
        $html = "<p>" . EmspayHelper::getOrderDescription($virtuemart_order_number) . "</p>".
                "<p>" . JText::_("EMSPAY_LIB_ERROR_STATUS") . "</p>";
        $this->processFalseOrderStatusResponse($html);
    }

	protected function getGingerPaymentIban(array $gingerOrder)
	{
		return $gingerOrder['transactions'][0]['payment_method_details']['creditor_iban'];
	}

	protected function getGingerPaymentBic(array $gingerOrder)
	{
		return $gingerOrder['transactions'][0]['payment_method_details']['creditor_bic'];
	}

	protected function getGingerPaymentHolderName(array $gingerOrder)
	{
		return $gingerOrder['transactions'][0]['payment_method_details']['creditor_account_holder_name'];
	}

	protected function getGingerPaymentHolderCity(array $gingerOrder)
	{
		return $gingerOrder['transactions'][0]['payment_method_details']['creditor_account_holder_city'];
	}

	protected function getGingerPaymentHolderCountry(array $gingerOrder)
	{
		return $gingerOrder['transactions'][0]['payment_method_details']['creditor_account_holder_country'];
	}

    protected function getGingerPaymentReference(array $gingerOrder)
    {
        return $gingerOrder['transactions'][0]['payment_method_details']['reference'];
    }
    
    
    /**
     * Webhook action
     *
     * @return void
     * @since v1.0.0
     */
    public function plgVmOnPaymentNotification()
    {
        if (!($method = $this->getVmPluginMethod(vRequest::getInt('pm')))) {
            return null;
        }
        if (!$this->selectedThisElement($method->payment_element)) {
            return false;
        }

        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input['order_id']) || $input['event'] !== 'status_changed') {
            exit('Invalid input');
        }

        $gingerOrder = $this->getGingerClient()->getOrder($input['order_id']);

        $virtuemart_order_id = $this->getOrderIdByGingerOrder($input['order_id']);

        $this->updateOrder($gingerOrder['status'], $virtuemart_order_id);

        exit();
    }

    /**
     *
     * @param \VirtueMartCart $cart
     * @param type $selected
     * @param type $htmlIn
     * @return type
     * @since v1.0.0
     */
    public function plgVmDisplayListFEPayment(\VirtueMartCart $cart, $selected = 0, &$htmlIn)
    {
        return $this->displayListFE($cart, $selected, $htmlIn);
    }
    
    /**
    *
    * @param \VirtueMartCart $cart
    * @param array $cart_prices
    * @param type $cart_prices_name
    * @return type
    * @since v1.0.0
    */
    public function plgVmonSelectedCalculatePricePayment(\VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name)
    {
        return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
    }
    
    /**
     * before order is creted
     *
     * @param type $orderDetails
     * @since v1.0.0
     */
    public function plgVmOnUserOrder(&$orderDetails)
    {
        return true;
    }

    /**
     * This is for checking the input data of the payment method within the checkout
     *
     * @author Valerie Cartan Isaksen
     * @since v1.0.0
     */
    public function plgVmOnCheckoutCheckDataPayment(\VirtueMartCart $cart)
    {
        return true;
    }
    
    /**
      * This method is fired when showing the order details in the frontend.
      * It displays the method-specific data.
      *
      * @param integer $order_id The order ID
      * @return mixed Null for methods that aren't active, text (HTML) otherwise
      * @since v1.0.0
      */
    public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name)
    {
        $this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
    }
}

// No closing tag
