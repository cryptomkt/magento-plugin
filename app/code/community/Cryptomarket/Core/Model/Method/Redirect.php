<?php
/**
* @license Copyright 2016-2018 CryptoMarket Inc., MIT License
* @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
*/

/**
* Redirect payment method support by CryptoMarket
*/
class Cryptomarket_Core_Model_Method_Redirect extends Mage_Payment_Model_Method_Abstract
{
    protected $_code                        = 'cryptomarket';
    protected $_formBlockType               = 'cryptomarket/form_cryptomarket';
    protected $_infoBlockType               = 'cryptomarket/info';

    protected $_isGateway                   = true;
    protected $_canAuthorize                = true;
    protected $_canCapture                  = false;
    protected $_canUseInternal              = false;
    protected $_isInitializeNeeded          = false;
    protected $_canFetchTransactionInfo     = false;
    protected $_canManagerRecurringProfiles = false;
    protected $_canUseCheckout              = false;
    protected $_canUseForMultishipping      = true;
    protected $_canCapturePartial           = false;
    protected $_canRefund                   = false;
    protected $_canVoid                     = false;

    protected $_debugReplacePrivateDataKeys = array();

    protected static $_redirectUrl;

    /**
    * This makes sure that the merchant has setup the extension correctly
    * and if they have not, it will not show up on the checkout.
    *
    * @see Mage_Payment_Model_Method_Abstract::canUseCheckout()
    * @return bool
    */
    public function canUseCheckout()
    {
        $token = \Mage::getStoreConfig('payment/cryptomarket/cryptomkt_apikey');

        if (false === isset($token) || true === empty($token)) {
            $this->debugData('[ERROR] In Cryptomarket_Core_Model_Method_Redirect::canUseCheckout(): There was an error retrieving the token store param from the database or this Magento store does not have a CryptoMarket token.');

            return false;
        }

        $this->debugData('[INFO] Leaving Cryptomarket_Core_Model_Method_Redirect::canUseCheckout(): token obtained from storage successfully.');

        return true;
    }

    /**
    * This is called when a user clicks the `Place Order` button
    *
    * @return string
    */
    public function getOrderPlaceRedirectUrl()
    {
        $this->debugData('[INFO] In cryptomkt_Core_Model_Method_Bitcoin::getOrderPlaceRedirectUrl(): $_redirectUrl is ' . self::$_redirectUrl);
        return self::$_redirectUrl;
    }

    /**
    * setRedirectToCryptoMarket Define the redirect url
    * @param String $url
    */
    public function setRedirectToCryptoMarket($url)
    {
        self::$_redirectUrl = $url;
    }
}
