<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

class Wirecard_CheckoutSeamless_Model_Observer
    extends Varien_Object
{
    /**
     * The given Order Object from Observer
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * Process the seamless Payment after Order is complete
     *
     * @param Varien_Event_Observer $observer
     *
     * @throws Exception
     * @return Wirecard_CheckoutSeamless_Model_Observer
     */
    public function salesOrderPaymentPlaceEnd(Varien_Event_Observer $observer)
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');

        /** @var Wirecard_CheckoutSeamless_Helper_Data $helper */
        $helper = Mage::helper('wirecard_checkoutseamless');

        $payment         = $observer->getPayment();
        $this->_order    = $payment->getOrder();

        /** @var Wirecard_CheckoutSeamless_Model_Abstract $paymentInstance */
        $paymentInstance = $payment->getMethodInstance();

        if(!($paymentInstance instanceof Wirecard_CheckoutSeamless_Model_Abstract)) {
            return $this;
        }

        $init = $paymentInstance->initPayment($session->getWirecardCheckoutSeamlessStorageId(), $session->getQuoteId());
        if($init->getStatus() == WirecardCEE_QMore_Response_Initiation::STATE_SUCCESS) {
            $helper->log(__METHOD__ . ':setting redirect url:' . $init->getRedirectUrl());
            Mage::getSingleton('core/session')->unsWirecardCheckoutSeamlessPaymentInfo();
            $session->setWirecardCheckoutSeamlessRedirectUrl($init->getRedirectUrl());
        } else {
            Mage::getSingleton('core/session')->unsWirecardCheckoutSeamlessPaymentInfo();
        }

        return $this;
    }
}