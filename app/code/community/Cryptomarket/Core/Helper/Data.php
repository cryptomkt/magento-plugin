<?php
/**
 * @license Copyright 2016-2018 CryptoMarket Inc., MIT License
 * @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
 */

/**
 */
class Cryptomarket_Core_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_autoloaderRegistered;
    protected $_cryptomarket;
    protected $_client;
    protected $_dataSaveAllowed = false;

    /**
     * @param mixed $debugData
     */
    public function debugData($debugData)
    {
        //log information about the environment
        $phpVersion = explode('-', phpversion())[0];
        $extendedDebugData = array(
            '[PHP version] ' . $phpVersion,
            '[Magento version] ' . \Mage::getVersion(),
            '[CryptoMarket plugin version] ' . $this->getExtensionVersion(),
        );
        foreach($extendedDebugData as &$param)
        {
            $param = PHP_EOL . "\t\t" . $param;
        }

        if (true === isset($debugData) && false === empty($debugData)) {
            \Mage::getModel('cryptomarket/method_redirect')->debugData($extendedDebugData);
            \Mage::getModel('cryptomarket/method_redirect')->debugData($debugData);
        }
    }

    /**
     * @return boolean
     */
    public function isDebug()
    {
        return (boolean) \Mage::getStoreConfig('payment/cryptomarket/debug');
    }

    /**
     * Returns the URL where the IPN's are sent
     *
     * @return string
     */
    public function getNotificationUrl()
    {
        return \Mage::getUrl('cryptomarket/updater');
    }

    /**
     * Returns the URL where customers are redirected
     *
     * @return string
     */
    public function getRedirectUrl()
    { error_log('[INFO] getRedirectUrl()');
        return \Mage::getUrl(\Mage::getStoreConfig('payment/cryptomarket/redirect_url'));
    }

    /**
     * Returns the URL where customers are redirected
     *
     * @return string
     */
    public function getFailureUrl()
    {error_log('[INFO] getFailureUrl()');
        return \Mage::getUrl(\Mage::getStoreConfig('payment/cryptomarket/failure_url'));
    }

    /**
     * Registers the CryptoMarket autoloader to run before Magento's. This MUST be
     * called before using any cryptomarket classes.
     */
    public function registerAutoloader()
    {
        if (true === empty($this->_autoloaderRegistered)) {
            $autoloader_filename = \Mage::getBaseDir('lib').'/Cryptomkt/Autoloader.php';

            if (true === is_file($autoloader_filename) && true === is_readable($autoloader_filename)) {
                require_once $autoloader_filename;
                \Cryptomkt\Autoloader::register();
                $this->_autoloaderRegistered = true;
                $this->debugData('[INFO] In Cryptomarket_Core_Helper_Data::registerAutoloader(): autoloader file was found and has been registered.');
            } else {
                $this->_autoloaderRegistered = false;
                $this->debugData('[ERROR] In Cryptomarket_Core_Helper_Data::registerAutoloader(): autoloader file was not found or is not readable. Cannot continue!');
                throw new \Exception('In Cryptomarket_Core_Helper_Data::registerAutoloader(): autoloader file was not found or is not readable. Cannot continue!');
            }
        }
    }

    /**
     * @return Cryptomarket\Client
     */
    public function getCryptomarketClient()
    {
        if (false === empty($this->_client)) {
            return $this->_client;
        }

        if (true === empty($this->_autoloaderRegistered)) {
            $this->registerAutoloader();
        }
        $payment_receiver = \Mage::getStoreConfig('payment/cryptomarket/payment_receiver');
        $apikey = \Mage::getStoreConfig('payment/cryptomarket/cryptomkt_apikey');
        $apisecret = \Mage::getStoreConfig('payment/cryptomarket/cryptomkt_apisecret');

        if ( true === empty($payment_receiver) || true === empty($apikey) || true === empty($apisecret) ) {
            $this->debugData('[ERROR] In Cryptomarket_Core_Helper_Data::getCryptomarketClient(): Credentials not defined. Cannot continue!');
            throw new \Exception('In Cryptomarket_Core_Helper_Data::getCryptomarketClient(): Credentials not defined. Cannot continue!');
        } else {
            $this->debugData('[INFO] In Cryptomarket_Core_Helper_Data::getCryptomarketClient(): Valid credentials.');
        }

        $configuration = Cryptomkt\Configuration::apiKey(
            \Mage::getStoreConfig('payment/cryptomarket/cryptomkt_apikey'),
            \Mage::getStoreConfig('payment/cryptomarket/cryptomkt_apisecret')
        );

        $this->_client = Cryptomkt\Client::create($configuration);

        if (false === isset($this->_client) || true === empty($this->_client)) {
            $this->debugData('[ERROR] In Cryptomarket_Core_Helper_Data::getCryptomarketClient(): could not create new CryptoMarket Client object. Cannot continue!');
            throw new \Exception('In Cryptomarket_Core_Helper_Data::getCryptomarketClient(): could not create new CryptoMarket Client object. Cannot continue!');
        } else {
            $this->debugData('[INFO] In Cryptomarket_Core_Helper_Data::getCryptomarketClient(): successfully created new CryptoMarket Client object.');
        }

        return $this->_client;
    }

    /**
     * @return string
     */
    public function getLogFile()
    {
        return "payment_cryptomarket.log";
    }

    public function getExtensionVersion()
    {
        return (string) \Mage::getConfig()->getNode()->modules->Cryptomarket_Core->version;
    }

    public function checkResponseSignature($hash, $id, $status)
    {
        return Cryptomkt\Authentication\ApiKeyAuthentication::checkHash($hash, $id.$status, \Mage::getStoreConfig('payment/cryptomarket/cryptomkt_apisecret'));
    }
}
