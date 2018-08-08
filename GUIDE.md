# CryptoCompra by CryptoMarket User Guide

[![N|CryptoCompra](https://www.cryptocompra.com/img/logo.png)](https://www.cryptocompra.com/)

## Prerequisites
You must have a CryptoMarket account to use this plugin. You can register free in [Sign-up](https://www.cryptomkt.com/account/register).


## Server Requirements

* Last Cart Version Tested: 1.9.3.9
* [Magento CE](http://magento.com/resources/system-requirements) 1.9.0.1 or higher. Older versions might work, however this plugin has been validated to work against the 1.9.0.1 Community Edition release.
* [OpenSSL](http://us2.php.net/openssl) Must be compiled with PHP and is used for certain cryptographic operations.
* [PHP](http://us2.php.net/downloads.php) 5.4 or higher. This plugin will not work on PHP 5.3 and below.

## Installation

**From the Releases Page:**

Visit the [Releases](https://github.com/cryptomkt/magento-plugin/releases) page of this repository and download the latest version. Once this is done, you can just unzip the contents and use any method you want to put them on your server. The contents will mirror the Magento directory structure.

**WARNING:** It is good practice to backup your database before installing extensions. Please make sure you Create Backups.

## Configuration

Configuration can be done using the Administrator section of your Megento store. Once Logged in, you will find the configuration settings under **System > Configuration > Sales > Payment Methods**.

![CryptoCompra Magento Settings](https://raw.githubusercontent.com/cryptomkt/magento-plugin/master/docs/MagentoSettings.png "CryptoCompra Magento Settings")

In this point you need to have a CryptoMarket Account. You can register free in [Sign-up](https://www.cryptomkt.com/account/register).

## Usage

Once enabled, your customers will be given the option to pay with cryptocurrencies. Once they checkout they are redirected to CryptoMarket Payment Page to pay the order.

As a merchant, the orders in your Magento store can be treated as any other order.
