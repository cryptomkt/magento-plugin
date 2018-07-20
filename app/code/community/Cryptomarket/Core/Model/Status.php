<?php
/**
 * @license Copyright 2016-2018 CryptoMarket Inc., MIT License
 * @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
 */

class Cryptomarket_Core_Model_Status
{
    const STATUS_NEW       = 'new';
    const STATUS_PAID      = 'paid';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_COMPLETE  = 'complete';
    const STATUS_EXPIRED   = 'expired';
    const STATUS_INVALID   = 'invalid';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::STATUS_NEW, 'label'       => \Mage::helper('cryptomarket')->__(ucwords(self::STATUS_NEW))),
            array('value' => self::STATUS_PAID, 'label'      => \Mage::helper('cryptomarket')->__(ucwords(self::STATUS_PAID))),
            array('value' => self::STATUS_CONFIRMED, 'label' => \Mage::helper('cryptomarket')->__(ucwords(self::STATUS_CONFIRMED))),
            array('value' => self::STATUS_COMPLETE, 'label'  => \Mage::helper('cryptomarket')->__(ucwords(self::STATUS_COMPLETE))),
            array('value' => self::STATUS_EXPIRED, 'label'   => \Mage::helper('cryptomarket')->__(ucwords(self::STATUS_EXPIRED))),
            array('value' => self::STATUS_INVALID, 'label'   => \Mage::helper('cryptomarket')->__(ucwords(self::STATUS_INVALID))),
        );
    }
}
