<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Qenta Payment CEE GmbH
 * (abbreviated to Qenta CEE) and are explicitly not part of the Qenta CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Qenta CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Qenta CEE does not guarantee their full
 * functionality neither does Qenta CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Qenta CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

class Qenta_CheckoutSeamless_Model_Invoiceb2b extends Qenta_CheckoutSeamless_Model_Abstract
{
    /**
    * unique internal payment method identifier
    *
    * @var string [a-z0-9_]
    **/
    protected $_code = 'qenta_checkoutseamless_invoiceb2b';
    protected $_paymentMethod = QentaCEE_Stdlib_PaymentTypeAbstract::INVOICE;

    protected $_forceSendAdditionalData = true;

    /**
     * translate method title shown in payment selection
     * the methode code is the key, if no transaltion found
     * use the title setting
     *
     * @return string
     */
    public function getTitle()
    {
        /** @var Qenta_CheckoutPage_Helper_Data $helper */
        $helper = Mage::helper('qenta_checkoutseamless');

        $translated = $helper->__('INVOICEB2B');
        if ($translated == 'INVOICEB2B') {
            return parent::getTitle();
        }

        return $translated;
    }

    /**
     * @param Mage_Sales_Model_Quote|null $quote
     * @return bool
     * @see Mage_Payment_Model_Method_Abstract::isAvailable()
     */
    public function isAvailable($quote = null)
    {
        //NOTE: NEVER return true in here. the parent check should do this!
        if($quote == null)
        {
            $quote = $this->_getQuote();
        }

        if($quote->hasVirtualItems())
        {
            return false;
        }

        if (!$this->compareAddresses($quote))
            return false;

        if($quote->getQuoteCurrencyCode() != 'EUR')
        {
            return false;
        }

        $billingAddress = $quote->getBillingAddress();
        if (!strlen($billingAddress->getCompany()))
            return false;

        $vat_id = $billingAddress->getData('vat_id');
        if (!strlen($vat_id))
            return false;

        return parent::isAvailable($quote);
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        $result = parent::assignData($data);
        $key = 'qenta_checkoutseamless_invoiceb2b_company_trade_reg_number';
        if (is_array($data)) {
            $this->getInfoInstance()->setAdditionalInformation($key, isset($data[$key]) ? $data[$key] : null);
        }
        elseif ($data instanceof Varien_Object) {
            $this->getInfoInstance()->setAdditionalInformation($key, $data->getData($key));
        }

        $this->getInfoInstance()->save();
        return $result;
    }

    /**
     * @return array|mixed|null|string
     */
    public function getCompanyTradeRegistrationNumber() {
        $additionalInfo = $this->getInfoInstance();
        $field = 'company_trade_reg_number';

        if($additionalInfo->hasAdditionalInformation('qenta_checkoutseamless_invoiceb2b_company_trade_reg_number'))
        {
            $userCtrn = $additionalInfo->getAdditionalInformation('qenta_checkoutseamless_invoiceb2b_company_trade_reg_number');
            if (Mage::getSingleton('customer/session')->getId() !== null) {
                $customer = Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getId());
                $customer->setData($field, $userCtrn)->getResource()->saveAttribute($customer, $field);
            }
            return $userCtrn;
        }
        else
        {
            return "";
        }
    }

    protected function _getConsumerData()
    {
        $consumerData = parent::_getConsumerData();

        $billingAddress  = $this->getOrder()->getBillingAddress();

        $consumerData->setCompanyName($billingAddress->getCompany());

        if (strlen($billingAddress->getData('vat_id')))
            $consumerData->setCompanyVatId($billingAddress->getData('vat_id'));

        if (strlen($this->getCompanyTradeRegistrationNumber()))
            $consumerData->setCompanyTradeRegistryNumber($this->getCompanyTradeRegistrationNumber());

        return $consumerData;
    }
}
