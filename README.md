# Magento 1: Custom Payment Module

This Custom Payment Module only allow payment from admin backend (capture invoice payment)

## To install
- Overwrite this app folder of your Magento 1.8+ installation
- Flush the cache, enable the Payment Method

## Payment Gateway Reference Number
Reference Number are storing into below 2 Magento tables
- mage_sales_flat_order_payment :: showpo_ref
- mage_sales_flat_quote_payment :: showpo_ref

## Screenshots
Title can be configurable from Admin Payment Method
- admin-payment-methods.png : After successful installing this module
- showpo_payment.png : Payment Info section under capture Invoice
