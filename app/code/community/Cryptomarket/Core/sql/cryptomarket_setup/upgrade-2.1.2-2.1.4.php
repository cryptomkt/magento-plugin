<?php
/**
 * @license Copyright 2016-2018 CryptoMarket Inc., MIT License
 * @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
 */
$this->startSetup();

/**
 * IPN Log Table, used to keep track of incoming IPNs
 * 
 * Fixes `curent_time` typo
 */
$ipnTable = new Varien_Db_Ddl_Table();
$this->getConnection()->changeColumn($this->getTable('cryptomarket/payorder'), 'curent_time', 'current_time', array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER));

$this->endSetup();
