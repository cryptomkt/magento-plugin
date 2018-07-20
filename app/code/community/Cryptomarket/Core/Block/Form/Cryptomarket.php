<?php
/**
 * @license Copyright 2016-2018 CryptoMarket Inc., MIT License
 * @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
 */

class Cryptomarket_Core_Block_Form_Cryptomarket extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $payment_template = 'cryptomarket/form/cryptomarket.phtml';

        $this->setTemplate($payment_template);

        parent::_construct();
    }
}
