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

class Wirecard_CheckoutSeamless_Block_Seamless_Cc extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
        parent::_construct();

        /** @var Wirecard_CheckoutSeamless_Helper_Data $helper */
        $helper = Mage::helper('wirecard_checkoutseamless');
        if (!$helper->getConfigData('ccard/pci3_dss_saq_a_enable'))
            $this->setTemplate('wirecard/checkoutseamless/seamless/cc.phtml');
        else
            $this->setTemplate('wirecard/checkoutseamless/seamless/cc-nopci.phtml');

    }

    public function showIssueDate()
    {
        return Mage::helper('wirecard_checkoutseamless')->getConfigData('ccard/showissuedate');
    }

    public function showIssueNumber()
    {
        return Mage::helper('wirecard_checkoutseamless')->getConfigData('ccard/showissuenumber');
    }

    public function showCardholder()
    {
        return Mage::helper('wirecard_checkoutseamless')->getConfigData('ccard/showcardholder');
    }

    public function showCvc()
    {
        return Mage::helper('wirecard_checkoutseamless')->getConfigData('ccard/showcvc');
    }

    public function getSessionData()
    {
        $data = Mage::getSingleton('core/session')->getWirecardCheckoutSeamlessPaymentInfo();
        if ($data) {
            return $data;
        }
        return false;
    }

}