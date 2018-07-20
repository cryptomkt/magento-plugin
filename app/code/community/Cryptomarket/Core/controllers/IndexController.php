<?php
/**
 * @license Copyright 2016-2018 CryptoMarket Inc., MIT License
 * @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
 */

/**
 * @route cryptomarket/index/
 */
class Cryptomarket_Core_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * @route cryptomarket/index/index?quote=n
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
        // \Mage::helper('cryptomarket')->registerAutoloader();
        // \Mage::helper('cryptomarket')->debugData($params);

	// $params  = $this->getRequest()->getParams();
	// $quoteId = $params['quote'];

	// if (!is_numeric($quoteId))
	// {
	//     return $this->getResponse()->setHttpResponseCode(400);
	// }

 //        $paid = \Mage::getModel('cryptomarket/payorder')->GetQuotePaid($quoteId);
 //        $this->loadLayout();
 //        $this->getResponse()->setHeader('Content-type', 'application/json');

 //        return $this->getResponse()->setBody(json_encode(array('paid' => $paid)));
 //    }
  }
}
