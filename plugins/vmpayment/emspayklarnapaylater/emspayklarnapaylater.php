<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

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

class plgVmPaymentEmspayklarnaPayLater extends EmspayVmPaymentPlugin
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
	  $this->name = 'emspayklarnapaylater';
    }

    /**
     * This shows the plugin for choosing in the payment list of the checkout process.
     *
     * @param VirtueMartCart $cart
     * @param type $selected
     * @param array $htmlIn
     * @return boolean
     * @since v1.0.0
     */
    public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn)
    {
        if ($this->getPluginMethods($cart->vendorId) === 0) {
            if (empty($this->_name)) {
                $app = JFactory::getApplication();
                $app->enqueueMessage(vmText::_('COM_VIRTUEMART_CART_NO_' . strtoupper($this->_psType)));
                return false;
            } else {
                return false;
            }
        }
        $method_name = $this->_psType . '_name';
        vmLanguage::loadJLang('com_virtuemart', true);
        foreach ($this->methods as $this->_currentMethod) {
            if ($this->checkConditions($cart, $this->_currentMethod, $cart->cartPrices)) {
                $cartPrices = $cart->cartPrices;
                $methodSalesPrice = $this->setCartPrices($cart, $cartPrices, $this->_currentMethod);
                $this->_currentMethod->$method_name = $this->renderPluginName($this->_currentMethod);
                $html = $this->getPluginHtml($this->_currentMethod, $selected, $methodSalesPrice);
                $htmlIn[] = [$html];
            }
        }
        return $this->isPaymentSelected($selected);
    }

    /**
     * check if current method is selected
     *
     * @param int $selected
     * @return boolean
     * @since v1.0.0
     */
    private function isPaymentSelected($selected)
    {
        $method = array_shift($this->methods);
        if (is_object($method)) {
            return $method->virtuemart_paymentmethod_id === $selected;
        }
        return false;
    }
            
    /**
     * This is for checking the input data of the payment method within the checkout
     *
     * @param VirtueMartCart $cart
     * @return type
     * @since v1.0.0
     */
    public function plgVmOnCheckoutCheckDataPayment(VirtueMartCart $cart)
    {
        if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
            return null; // Another method was selected, do nothing
        }

        if (!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
            return false;
        }
        return true;
    }

    /**
     * This is for adding the input data of the payment method to the cart, after selecting
     *
     * @author Valerie Isaksen
     *
     * @param VirtueMartCart $cart
     * @return null if payment not selected; true if infos are correct
     * @since v1.0.0
     */
    public function plgVmOnSelectCheckPayment(VirtueMartCart $cart, &$msg)
    {
        if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
            return null; // Another method was selected, do nothing
        }

        if (!($currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
            return false;
        }
        return true;
    }

    /**
    * @param VirtueMartCart $cart
    * @param array $cart_prices
    * @param string $payment_name
    * @return bool|null
    * @since v1.0.0
    */
    public function plgVmOnSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$payment_name)
    {
        if (!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
            return null; // Another method was selected, do nothing
        }

        if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
            return false;
        }

        if (!$this->checkConditions($cart, $this->_currentMethod, $cart_prices)) {
            return false;
        }
        $payment_name = $this->renderPluginName($this->_currentMethod);
        $this->setCartPrices($cart, $cart_prices, $this->_currentMethod);

        return true;
    }

    
    /**
     * @param $cart
     * @param $order
     * @return bool|null
     * @since v1.0.0
     */
    public function plgVmConfirmedOrder(VirtueMartCart $cart, $order)
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
        $orderLines = $this->getOrderLines($cart, $currency_code_3);
	  $returnUrl = EmspayHelper::getReturnUrl(intval($order['details']['BT']->virtuemart_paymentmethod_id));

        try {
            $response = $this->getGingerClient()->createOrder(array_filter([
			'amount' => $totalInCents,                           // Amount in cents
			'currency' => $currency_code_3,                      // Currency
			'transactions' => [
				[
					'payment_method' => 'klarna-pay-later'   // Payment method
				]
			],
			'merchant_order_id' => $orderId,                     // Merchant Order Id
			'description' => $description,                       // Description
			'return_url' => $returnUrl,                          // Return URL
			'customer' => $customer->toArray(),                  // Customer Information
			'extra' => ['plugin' => $plugin],                    // Extra information
			'webhook_url' => $webhook,                           // Webhook URL
			'order_lines' => $orderLines                         // Order lines
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

        $html = "<p>" . EmspayHelper::getOrderDescription($virtuemart_order_number) . "</p>";
        if ($statusSucceeded) {
            $this->emptyCart(null, $virtuemart_order_id);
            $html .= "<p>". JText::_('EMSPAY_LIB_THANK_YOU_FOR_YOUR_ORDER'). "</p>";
            vRequest::setVar('html', $html);
		JFactory::getApplication()->redirect($response['transactions'][0]['payment_url']);
        }
        $html .= "<p>" . JText::_("EMSPAY_LIB_ERROR_STATUS") . "</p>";
        $this->processFalseOrderStatusResponse($html);
    }

    /**
     *
     * @param VirtuemartCart $cart
     * @param string $currency_code_3
     * @return array
     * @since v1.0.0
     */
    public function getOrderLines($cart, $currency_code_3)
    {
        $orderLines = [];

        foreach ($cart->products as $product) {
            $orderLines[] = array_filter([
                'name' => $product->product_name,
                'type' => EmspayHelper::PHYSICAL,
                'amount' => EmspayHelper::getAmountInCents($product->prices['salesPrice']),
                'currency' => $currency_code_3,
                'quantity' => $product->quantity,
                'vat_percentage' => $this->caclucalteVatTax($product->prices['VatTax']),
                'merchant_order_line_id' => $product->virtuemart_product_id
                    ], function ($var) {
                        return !is_null($var);
                    });
        }

        if (isset($cart->cartPrices['salesPriceShipment']) && $cart->cartPrices['salesPriceShipment'] > 0) {
            $orderLines[] = array_filter([
                'name' => isset($cart->cartData['shipmentName']) ? $cart->cartData['shipmentName'] : '',
                'type' => EmspayHelper::SHIPPING_FEE,
                'amount' => EmspayHelper::getAmountInCents($cart->cartPrices['salesPriceShipment']),
                'currency' => EmspayHelper::getPaymentCurrency(),
                'vat_percentage' => isset($cart->cartPrices[0]['VatTax']) ? $this->caclucalteVatTax($cart->cartPrices[0]['VatTax']) : 0,
                'merchant_order_line_id' => $cart->virtuemart_shipmentmethod_id,
                'quantity' => 1], function ($var) {
                    return !is_null($var);
                });
        }

        return count($orderLines) > 0 ? $orderLines : null;
    }

    /**
     * extract data from array an get the tax value
     *
     * @param type $vatTax
     * @return type
     * @since v1.0.0
     */
    protected function caclucalteVatTax($vatTax)
    {
        if (is_array($vatTax) && count($vatTax)) {
            $tax = array_shift($vatTax);
            if (isset($tax[1])) {
                return round($tax[1], 2);
            }
        }
        return round(0, 2);
    }

    /**
     * on order status shipped
     *
     * @param array $_formData
     * @return boolean
     * @since v1.0.0
     */
    public function plgVmOnUpdateOrderPayment($_formData)
    {
        if (!($method = $this->getVmPluginMethod($_formData->virtuemart_paymentmethod_id))) {
            return null; // Another method was selected, do nothing
        }
    
        if (!$this->selectedThisElement($method->payment_element)) {
            return false;
        }

        try {
            if ($_formData->order_status === $this->methodParametersFactory()->statusCaptured() && $gingerOrderId = $this->getGingerOrderIdByOrderId($_formData->virtuemart_order_id)) {
                $ginger = $this->getGingerClient();
		    $ginger_order = $ginger->getOrder($gingerOrderId);
		    $transaction_id = !empty(current($ginger_order['transactions'])) ? current($ginger_order['transactions'])['id'] : null;
		    $ginger->captureOrderTransaction($ginger_order['id'],$transaction_id);
            }
        } catch (\Exception $ex) {
            JFactory::getApplication()->enqueueMessage($ex->getMessage(), 'error');
            return false;
        }
        return true;
    }

    /**
     * Get Gigner order id from the plugin table
     *
     * @param int $orderId
     * @return string
     * @since v1.0.0
     */
    protected function getGingerOrderIdByOrderId($orderId)
    {
        $query = "SELECT `ginger_order_id` FROM " . $this->_tablename . "  WHERE `virtuemart_order_id` = '" . $orderId . "'";
        $db = \JFactory::getDBO();
        $db->setQuery($query);
        $r = $db->loadObject();
        if (is_object($r)) {
            return $r->ginger_order_id;
        }
        return null;
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
     * create a client instance
     *
     * @return \Ginger\ApiClient
     * @since v1.0.0
     */
    protected function getGingerClient()
    {
        $params = $this->methodParametersFactory();

        return \Ginger\Ginger::createClient(
		  \EmspayHelper::GINGER_ENDPOINT,
		  $params->getAfterpayApiKey(),
		  ($params->bundleCaCert() == true) ?
			  [
				  CURLOPT_CAINFO => \EmspayHelper::getCaCertPath()
			  ] : []
	  );
    }

    /**
     * This method is fired when showing the order details in the frontend.
     * It displays the method-specific data.
     *
     * @param integer $order_id The order ID
     * @return mixed Null for methods that aren't active, text (HTML) otherwise
     * @author Valerie Isaksen
     */
    public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name)
    {
        $this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
        return true;
    }

    /**
     * This method is fired when showing when priting an Order
     * It displays the the payment method-specific data.
     *
     * @param integer $_virtuemart_order_id The order ID
     * @param integer $method_id  method used for this order
     * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
     * @author Valerie Isaksen
     */
    public function plgVmOnShowOrderPrintPayment($order_number, $method_id)
    {
        return parent::onShowOrderPrint($order_number, $method_id);
    }

    public function plgVmDeclarePluginParamsPaymentVM3(&$data)
    {
        return $this->declarePluginParams('payment', $data);
    }

    public function plgVmSetOnTablePluginParamsPayment($name, $id, &$table)
    {
        return $this->setOnTablePluginParams($name, $id, $table);
    }
}
