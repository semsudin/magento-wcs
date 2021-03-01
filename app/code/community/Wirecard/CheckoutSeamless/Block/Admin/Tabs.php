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

class Qenta_CheckoutSeamless_Block_Admin_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('qenta_checkout_seamless_tabs');
        $this->setDestElementId('support_form');
        $this->setTitle(Mage::helper('qenta_checkoutseamless')->__('Wirecard Checkout Seamless'));
        $this->addTab('config', array(
            'label' => Mage::helper('qenta_checkoutseamless')->__('Configuration'),
            'title' => Mage::helper('qenta_checkoutseamless')->__('Configuration'),
            'url'   => $this->getUrl('adminhtml/system_config/edit/section/qenta_checkoutseamless')
        ));

        $this->addTab('support_request', array(
            'label' => Mage::helper('qenta_checkoutseamless')->__('Support request'),
            'title' => Mage::helper('qenta_checkoutseamless')->__('Support request'),
            'url'   => $this->getUrl('adminhtml/qentacheckoutseamless/contactsupport')
        ));

        $this->addTab('backto_system', array(
            'label' => Mage::helper('qenta_checkoutseamless')->__('Back to system config'),
            'title' => Mage::helper('qenta_checkoutseamless')->__('Back to system config'),
            'url'   => $this->getUrl('adminhtml/system_config')
        ));

    }

}
