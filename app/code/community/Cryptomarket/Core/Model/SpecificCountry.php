<?php
/**
 * @license Copyright 2016-2018 CryptoMarket Inc., MIT License
 * @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
 */

class Cryptomarket_Core_Model_SpecificCountry
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $country = \Mage::getModel('adminhtml/system_config_source_country')->toOptionArray();
        
        $allowCountry = array();
        foreach($country as $v)
        {
            if($v['value'] != '' && $v['value'] != 'SY' && $v['value'] != 'IR' && $v['value'] != 'KP' && $v['value'] != 'SD' && $v['value'] != 'CU')
            {
                $allowCountry[] = $v;
            }
        }
        
        return $allowCountry;
    }
}
