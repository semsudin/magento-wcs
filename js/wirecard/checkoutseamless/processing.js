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

var wirecarcdCheckoutSeamlessResponseData = [];

function toggleWirecardCheckoutSeamlessIFrame() {
    if ($('wirecard-checkoutseamless-iframe-div')) {
        var viewportHeight = document.viewport.getHeight(),
            docHeight = $$('body')[0].getHeight(),
            height = docHeight > viewportHeight ? docHeight : viewportHeight;
        $('wirecard-checkoutseamless-iframe-div').toggle();
        $('window-overlay-wirecard-checkoutseamless').setStyle({height: height + 'px'}).toggle();
    }
}

Event.observe(window, 'load', function () {
    if (typeof WirecardCEE_DataStorage == 'function') {

        payment.save = payment.save.wrap(function (origSaveMethod) {
            if (this.currentMethod
                && this.currentMethod.substr(0, 25) == 'wirecard_checkoutseamless'
                && $j('#' + this.currentMethod + '_new').val() == '1') {
                var paymentData = null;

                $j("#payment_form_" + this.currentMethod + ' .has-wcs-data').each(function () {
                    var fieldname = $j(this).attr('data-wcs-fieldname');
                    if (typeof fieldname == 'undefined')
                        return true;

                    if (paymentData === null) {
                        paymentData = {};
                    }
                    paymentData[fieldname] = $j(this).val();
                });

                if (paymentData === null && $j('#' + this.currentMethod + '_pci3').val() == '1')
                    paymentData = { };

                if (paymentData !== null)
                    paymentData.paymentType = $j("#payment_form_" + this.currentMethod + ' .wcs-paymentmethod').val();

                if (paymentData === null
                    || (typeof wirecarcdCheckoutSeamlessResponseData[this.currentMethod] !== 'undefined'
                    && wirecarcdCheckoutSeamlessResponseData[this.currentMethod] !== false)) {
                    origSaveMethod();
                    return;
                }

                if (checkout.loadWaiting != false) return;

                var validator = new Validation(this.form);
                var valid = validator.validate();
                if (this.validate() && valid) {
                    checkout.setLoadWaiting('payment');
                    var application = new wirecardCheckoutSeamlessApplication();
                    application.sendRequest(paymentData);
                }

            } else {
                origSaveMethod();
            }
        });
    }

    Review.prototype.nextStep = Review.prototype.nextStep.wrap(function (next, transport) {
        outerTransport = transport;
        nextStep = next;
        var outerResponse = eval('(' + outerTransport.responseText + ')');
        if (typeof outerResponse.redirect == 'undefined') {
            nextStep(outerTransport);
        }
        else {
            var params = {'paymentMethod': payment.currentMethod};

            var request = new Ajax.Request(
                wirecardCheckoutSeamlessRedirectUrl,
                {
                    method: 'get',
                    parameters: params,
                    onSuccess: function (innerTransport) {
                        if (innerTransport && innerTransport.responseText) {
                            try {
                                var innerResponse = eval('(' + innerTransport.responseText + ')');
                                var outerResponse = eval('(' + outerTransport.responseText + ')');
                            }
                            catch (e) {
                                innerResponse = {};
                            }
                            if (innerResponse.url) {
                                //show iframe and set link
                                //toggleWirecardCheckoutSeamlessIFrame();
                                //$('wirecard-checkoutseamless-iframe').src = innerResponse.url;
                                var oPopup = new Window({
                                    id:'popup_window',
                                    className: 'magento',
                                    url: innerResponse.url,
                                    width: 500,
                                    height: 500,
                                    minimizable: false,
                                    maximizable: false,
                                    closable: false,
                                    showEffectOptions: {
                                        duration: 0.4
                                    },
                                    hideEffectOptions:{
                                        duration: 0.4
                                    },
                                    destroyOnClose: true
                                });
                                oPopup.setZIndex(100);
                                oPopup.showCenter(true);
                                console.log(oPopup);
                            }
                            else {
                                nextStep(outerTransport);
                            }
                        }
                    },
                    onFailure: function (innerTransport) {
                        nextStep(outerTransport);
                    }
                });
        }
    });

});

var wirecardCheckoutSeamlessApplication = Class.create();
wirecardCheckoutSeamlessApplication.prototype = {

    initialize: function () {
    },

    sendRequest: function (data) {

        var wirecardCee = new WirecardCEE_DataStorage;
        var request = wirecardCee.storePaymentInformation(data, function (response) {

            processResponse(response);

            if (response.getStatus() == 0) {

                new Ajax.Request(
                    wirecardCheckoutSeamlessSaveSessInfo,
                    {
                        method: 'post',
                        parameters: Form.serialize(payment.form)
                    }
                );

                var request = new Ajax.Request(
                    payment.saveUrl,
                    {
                        method: 'post',
                        onComplete: payment.onComplete,
                        onSuccess: payment.onSave,
                        onFailure: checkout.ajaxFailure.bind(checkout),
                        parameters: Form.serialize(payment.form)
                    }
                );
            }
        });

        // no postMessage support, make read request to datastore to check for stored data
        if (request === null) {
            new Ajax.Request(
                wirecardCheckoutSeamlessReadDatastorage,
                {
                    method: 'post',
                    parameters: Form.serialize(payment.form),
                    onComplete: function (resp) {
                        var response = window.JSON.parse(resp.responseText);

                        if (response.status == 1) {
                            new Ajax.Request(
                                payment.saveUrl,
                                {
                                    method: 'post',
                                    onComplete: payment.onComplete,
                                    onSuccess: payment.onSave,
                                    onFailure: checkout.ajaxFailure.bind(checkout),
                                    parameters: Form.serialize(payment.form)
                                }
                            );
                        } else {
                            alert('no stored paymentinformation found');
                            checkout.setLoadWaiting(false);
                        }
                    }
                });
        }
    }
}

function processResponse(response) {

    if (response.getErrors()) {
        var errorMsg = '';
        var errors = response.response.error;
        for (var i = 0; i <= response.response.errors; i++) {
            if (typeof errors[i] === 'undefined') {
                continue;
            }
            errorMsg += errors[i].consumerMessage.strip() + "\n\r";
        }
        //we have to htmlentities decode this
        alert(html_entity_decode(errorMsg));
        checkout.setLoadWaiting(false);

    } else {

        if (payment.currentMethod.substr(26) == 'cc' || payment.currentMethod.substr(26) == 'ccMoto') {

            // pci3 mode
            if ($(payment.currentMethod + '_pan') === null)
                return;
        }

        prepareSubmittedFields(response.response);
        wirecarcdCheckoutSeamlessResponseData[payment.currentMethod] = true;
    }
}

function html_entity_decode(str) {
    //jd-tech.net
    var tarea = document.createElement('textarea');
    tarea.innerHTML = str;
    return tarea.value;
}

function prepareSubmittedFields(response) {
    if (payment.currentMethod.substr(26) == 'cc' || payment.currentMethod.substr(26) == 'ccMoto') {
        enterAnonData(response.paymentInformation);
        $(payment.currentMethod + '_saved_data').show();
        $(payment.currentMethod + '_new_data').hide();
        emptyPaymentFields();
    } else {

        $$('#payment_form_' + payment.currentMethod + ' .no-submit').each(function (el) {
            el.observe('change', function (el) {
                $(payment.currentMethod + '_new').value = '1';
                wirecarcdCheckoutSeamlessResponseData[payment.currentMethod] = false;
            });
        });
    }

    var elements = $('payment_form_' + payment.currentMethod).select('input[type="hidden"]').each(function (el) {
        switch (el.name) {
            case 'payment[cc_owner]':
            case 'payment[ccMoto_owner]':
                el.value = response.paymentInformation.cardholdername;
                break;
            case 'payment[cc_type]':
            case 'payment[ccMoto_type]':
                el.value = response.paymentInformation.brand;
                break;
            case 'payment[cc_number]':
            case 'payment[ccMoto_number]':
                el.value = response.paymentInformation.anonymousPan;
                break;
            case 'payment[cc_exp_month]':
            case 'payment[ccMoto_exp_month]':
                el.value = response.paymentInformation.expiry.substr(0, response.paymentInformation.expiry.lastIndexOf('/'));
                break;
            case 'payment[cc_exp_year]':
            case 'payment[ccMoto_exp_year]':
                el.value = response.paymentInformation.expiry.substr(response.paymentInformation.expiry.lastIndexOf('/') + 1);
                break;
            case payment.currentMethod + '_new':
                el.value = '0';
                break;
            default:
                break;
        }
    });

    if (payment.currentMethod.substr(26) == 'sepadd' || payment.currentMethod.substr(26) == 'paybox'
        || payment.currentMethod.substr(26) == 'voucher' || payment.currentMethod.substr(26) == 'giropay') {
        emptyPaymentFields();
    }
}

function changePaymentData() {

    new Ajax.Request(
        wirecardCheckoutSeamlessDeleteSessInfo,
        {
            method: 'post',
            onSuccess: function () {
                emptyHiddenFields();
                wirecarcdCheckoutSeamlessResponseData[payment.currentMethod] = false;
                $(payment.currentMethod + '_new').value = '1';
                $(payment.currentMethod + '_saved_data').hide();
                $(payment.currentMethod + '_new_data').show();
            }
        }
    );
}

function emptyPaymentFields() {
    $j('#payment_form_' + payment.currentMethod + ' .no-submit').each(function (el) {
        this.value = '';
    });
}

function emptyHiddenFields() {
    $j('#payment_form_' + payment.currentMethod + ' .wcs-anon-data-hidden').each(function (el) {
        this.value = '';
    });
}

function enterAnonData(data) {
    $j('#' + payment.currentMethod + '_saved_data span').each(function (el) {
        this.innerHTML = data[this.id];
    });
}