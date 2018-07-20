<?php
/**
 * @license Copyright 2016-2018 CryptoMarket Inc., MIT License
 * @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
 */

/**
 */
class Cryptomarket_Core_Model_PayOrder extends Mage_Core_Model_Abstract
{
    /**
     */
    protected function _construct()
    {
    	parent::_construct();
        $this->_init('cryptomarket/payorder');
    }

    /**
     * @param string $quoteId
     * @param array  $statuses
     *
     * @return boolean
     */
    function GetStatusReceived($quoteId, $statuses)
    {
        if (!$quoteId)
        {
            return false;
        }

        $order = \Mage::getModel('sales/order')->load($quoteId, 'quote_id');

        if (false === isset($order) || true === empty($order)) {
            \Mage::helper('cryptomarket')->debugData('[DEBUG] Cryptomarket_Core_Model_Ipn::GetStatusReceived(), order not found for quoteId' . $quoteId);
            return false;
        }


        $orderId = $order->getIncrementId();

        if (false === isset($orderId) || true === empty($orderId)) {
            \Mage::helper('cryptomarket')->debugData('[DEBUG] Cryptomarket_Core_Model_Ipn::GetStatusReceived(), orderId not found for quoteId' . $quoteId);
            return false;
        }

        $collection = $this->getCollection();

        foreach ($collection as $i)
        {
            if ($orderId == json_decode($i->pos_data, true)['orderId']) {
                if (in_array($i->status, $statuses)) {
                    return true;
                }
            }
        }

        return false;		
    }

    /**
     * @param string $quoteId
     *
     * @return boolean
     */
    function GetQuotePaid($quoteId)
    {
        return $this->GetStatusReceived($quoteId, array('paid', 'confirmed', 'complete'));
    }

}
