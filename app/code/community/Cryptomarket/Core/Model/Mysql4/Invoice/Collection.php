<?php
/**
 * @license Copyright 2016-2018 CryptoMarket Inc., MIT License
 * @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
 */

/**
 */
class Cryptomarket_Core_Model_Mysql4_Invoice_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_isPkAutoIncrement = false;

    /**
     */
    protected function _construct()
    {
        $this->_init('cryptomarket/invoice');
    }
}
