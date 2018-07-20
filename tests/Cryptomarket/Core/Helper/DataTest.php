<?php
/**
 * @license Copyright 2016-2018 CryptoMarket Inc., MIT License
 * @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
 */

class Cryptomarket_Core_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    protected static $faker;

    public static function setUpBeforeClass()
    {
        self::$faker = Faker\Factory::create();
    }

    public function testGetLogFile()
    {
        $this->assertSame(
            'payment_cryptomarket.log',
            Mage::helper('cryptomarket')->getLogFile()
        );
    }

    public function testDebugData()
    {
        Mage::helper('cryptomarket')->debugData('Testing');
    }

    public function testIsDebugMode()
    {
        Mage::app()->getStore()->setConfig('payment/cryptomarket/debug', null);
        $this->assertFalse(Mage::helper('cryptomarket')->isDebug());

        Mage::app()->getStore()->setConfig('payment/cryptomarket/debug', false);
        $this->assertFalse(Mage::helper('cryptomarket')->isDebug());

        Mage::app()->getStore()->setConfig('payment/cryptomarket/debug', true);
        $this->assertTrue(Mage::helper('cryptomarket')->isDebug());
    }

    public function testHasTransactionSpeedFalse()
    {
        Mage::app()->getStore()->setConfig('payment/cryptomarket/speed', null);

        $this->assertFalse(Mage::helper('cryptomarket')->hasTransactionSpeed());
    }

    public function testHasTransactionSpeedTrue()
    {
        Mage::app()->getStore()->setConfig('payment/cryptomarket/speed', 'low');

        $this->assertTrue(Mage::helper('cryptomarket')->hasTransactionSpeed());
    }

    /**
     * Location where CryptoMarket IPNs will go
     */
    public function testGetNotificationUrl()
    {
        $this->assertSame(
            'http://www.localhost.com/cryptomarket/payorder/',
            Mage::helper('cryptomarket')->getNotificationUrl()
        );
    }

    public function testGetRedirectUrl()
    {
        $this->assertSame(
            'http://www.localhost.com/checkout/onepage/success/',
            Mage::helper('cryptomarket')->getRedirectUrl()
        );
    }

    public function testRegisterAutoloader()
    {
        Mage::helper('cryptomarket')->registerAutoloader();
    }

    public function testGenerateAndSaveKeys()
    {
        Mage::helper('cryptomarket')->generateAndSaveKeys();
    }

    public function testGetSinKey()
    {
        Mage::helper('cryptomarket')->getSinKey();
    }

    private function createInvalidIpn()
    {
        $ipn = new Cryptomarket_Core_Model_Ipn();
        $ipn->setData(
            array(
                'quote_id'        => '',
                'order_id'        => '',
                'invoice_id'      => '',
                'url'             => '',
                'pos_data'        => '',
                'status'          => '',
                'btc_price'       => '',
                'price'           => '',
                'currency'        => '',
                'invoice_time'    => '',
                'expiration_time' => '',
                'current_time'    => '',
            )
        );
        $ipn->save();
        $ipn->load($ipn->getId());

        return $ipn;
    }

    private function createExpiredIpn()
    {
        $order = $this->createOrder();
        $ipn   = new Cryptomarket_Core_Model_Ipn();
        $ipn->setData(
            array(
                'quote_id'        => '',
                'order_id'        => $order->getIncrementId(),
                'invoice_id'      => '',
                'url'             => '',
                'pos_data'        => '',
                'status'          => '',
                'btc_price'       => '',
                'price'           => '',
                'currency'        => '',
                'invoice_time'    => '',
                'expiration_time' => '',
                'current_time'    => '',
            )
        );
        $ipn->save();
        $ipn->load($ipn->getId());

        return $ipn;
    }

    private function createOrder()
    {
        $product = $this->createProduct();
        $quote   = $this->createQuote();
        $quote->addProduct(
            $product,
            new Varien_Object(
                array(
                    'qty' => 1,
                )
            )
        );
        $address = array(
            'firstname'            => self::$faker->firstName,
            'lastname'             => self::$faker->lastName,
            'company'              => self::$faker->company,
            'email'                => self::$faker->email,
            'city'                 => self::$faker->city,
            'region_id'            => '',
            'region'               => 'State/Province',
            'postcode'             => self::$faker->postcode,
            'telephone'            => self::$faker->phoneNumber,
            'country_id'           => self::$faker->state,
            'customer_password'    => '',
            'confirm_password'     => '',
            'save_in_address_book' => 0,
            'use_for_shipping'     => 1,
            'street'               => array(
                self::$faker->streetAddress,
            ),
        );

        $quote->getBillingAddress()
            ->addData($address);

        $quote->getShippingAddress()
            ->addData($address)
            ->setShippingMethod('flatrate_flatrate')
            ->setPaymentMethod('checkmo')
            ->setCollectShippingRates(true)
            ->collectTotals();

        $quote
            ->setCheckoutMethod('guest')
            ->setCustomerId(null)
            ->setCustomerEmail($address['email'])
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);

        $quote->getPayment()
            ->importData(array('method' => 'checkmo'));

        $quote->save();

        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();
        $order = $service->getOrder();

        $order->save();
        $order->load($order->getId());

        return $order;
    }

    private function createProduct()
    {
        $product = Mage::getModel('catalog/product');

        $product->addData(
            array(
                'attribute_set_id'  => 1,
                'website_ids'       => array(1),
                'categories'        => array(),
                'type_id'           => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                'sku'               => self::$faker->randomNumber,
                'name'              => self::$faker->name,
                'weight'            => self::$faker->randomDigit,
                'status'            => Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
                'tax_class_id'      => 2,
                'visibility'        => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                'price'             => self::$faker->randomFloat(2),
                'description'       => self::$faker->paragraphs,
                'short_description' => self::$faker->sentence,
                'stock_data'        => array(
                    'is_in_stock' => 1,
                    'qty'         => 100,
                ),
            )
        );

        $product->save();
        $product->load($product->getId());

        return $product;
    }

    private function createQuote()
    {
        return Mage::getModel('sales/quote')
            ->setStoreId(Mage::app()->getStore('default')->getId());
    }
}
