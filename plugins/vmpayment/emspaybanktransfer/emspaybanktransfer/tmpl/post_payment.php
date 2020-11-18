<?php
defined('_JEXEC') or die();

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

?>

<div class="vmpayment_ginger_end" id="vmpayment_ginger_end">
	<span class="vmpayment_banktransfer_end_message" id="vmpayment_banktransfer_end_message">
        <?php 
            echo $viewData["description"].'<br/>'.
            vmText::_('EMSPAY_LIB_ORDER_IS_COMPLETE').'<br/>'.
            vmText::_('PLG_VMPAYMENT_EMSPAYBANKTRANSFER_BANK_NOTICE').'<br/>'.
            vmText::_('EMSPAY_LIB_PLEASE_TRANSFER_MONEY')." ".$viewData['total_to_pay'].'<br/>'.
            vmText::_('PLG_VMPAYMENT_EMSPAYBANKTRANSFER_PAYMENT_REFERENCE')." ".$viewData['reference'].'<br/>'.
            $viewData['bank_information']. '<br/><br/>'.
            "<p>". JText::_('EMSPAY_LIB_THANK_YOU_FOR_YOUR_ORDER'). "</p>";
        ?>
	</span>
</div>






