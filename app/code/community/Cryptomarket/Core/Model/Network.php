<?php
/**
 * @license Copyright 2016-2018 CryptoMarket Inc., MIT License
 * @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
 */

/**
 * Used to display bitcoin networks
 */
class Cryptomarket_Core_Model_Network
{
    const NETWORK_LIVENET = 'livenet';
    const NETWORK_TESTNET = 'testnet';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::NETWORK_LIVENET, 'label' => \Mage::helper('cryptomarket')->__(ucwords(self::NETWORK_LIVENET))),
            array('value' => self::NETWORK_TESTNET, 'label' => \Mage::helper('cryptomarket')->__(ucwords(self::NETWORK_TESTNET))),
        );
    }
}
