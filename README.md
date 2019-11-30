# EMS Online plugin for Joomla VirtueMart

## About

By integrating your webshop with EMS Online you can accept payments from your customers in an easy and trusted manner with all relevant payment methods supported.

## Version number
Version v1.0.4

## Pre-requisites to install the plug-ins 
* PHP v5.4 and above
* MySQL v5.4 and above

## Installation
Manual installation of the EMS VirtueMart plugin using (s)FTP

1. Upload the plugin ZIP file in VirtueMart. Go to your Joomla admin environment and select ´Administrator´ > ´Extensions´ > ´Manage´ > ´Install´ > ´Upload Package File´.
2. Select ‘Extensions’ > ‘Manage’ > ‘Manage’.
3. Select the EMS Online payment methods you would like to enable.
Enable a payment method by clicking on the ‘x’ icon next to the payment method name
4. Select ‘VirtueMart’ > ‘Payment Methods’ and add new Payment Methods.
5. Configure the added payment methods - tab ‘Payment Method Information’ and ‘Configuration’.
- Set the ‘Published’ field to ‘Yes’.
- Copy the API key to `API key´ field.
- Set ‘Use cURL CA bundle’ field to ‘Yes’.
This fixes a cURL SSL Certificate issue that appears in some web-hosting environments where you do not have access to the PHP.ini file and therefore are not able to update server certificates.
- Enable the ‘Generate webhook URL’.
The plugin can automatically generate a webhook URL when a message is sent to the EMS API for a new order. To enable this option set ‘Generate webhook URL´ to ‘Yes’.
- Afterpay specific configuration
For the payment method Afterpay there are several specific settings:
	- Order shipped
	Configure the Order shipped status to Shipped. This ensures that if you change the status of a Afterpay order to Shopped the order is automatically captured to Afterpay for further processing.
	- Afterpay test API key Copy the API Key of your test webshop in the Test API key field.
	When your Afterpay application was approved an extra test webshop was created for you to use in your test with Afterpay. The name of this webshop starts with ‘TEST Afterpay’.
	- IP Address Filtering
	You can choose to offer Afterpay only to a limited set of whitelisted IP addresses. You can use this for instance when you are in the testing phase and want to make sure that Afterpay is not available yet for your customers.
	To do this enter the IP addresses that you want to whitelist, separate the addresses by a comma (“,”). The payment method Afterpay will only be presented to customers who use a whitelisted IP address.
	If you want to offer Afterpay to all your customers, you can leave the field empty.
	
- Klarna specific configuration
For the payment method Klarna; see all the specific settings for Afterpay.
- In your Joomla admin environment click ‘Save’ when you have finished configuring the plugin.
6. Compatibility: Joomla 3.9.11 and VirtueMart 3.6.0
7. Install languages and populate tables in the database for new languages
- Select ‘Extensions’ > ‘Languages’ > ‘Installed’ and Install languages
- Select ‘VirtueMart’ > ‘Configuration’ and add Shop Language
- After that, tables for additional languages will be created. For example: 
prefix_virtuemart_products_nl_nl (page VirtueMart’ > ‘Products’)
prefix_virtuemart_vendors_nl_nl (page VirtueMart’ > ‘Shop’)
prefix_virtuemart_categories_nl_nl (page VirtueMart’ > ‘Product Categories’)
prefix_virtuemart_manufacturers_nl_nl (page VirtueMart’ > ‘Manufacturers’)
prefix_virtuemart_manufacturercategories_nl_nl (page VirtueMart’ > ‘Manufacturers Categories’)
prefix_virtuemart_paymentmethods_nl_nl (page VirtueMart’ > ‘Payment Methods’)
prefix_virtuemart_shipmentmethods_nl_nl(page VirtueMart’ > ‘Shipment Methods’)

- Go to each of these pages -> select an item from the list -> go to its editing 
- Switch the language (Select over the left column of the Virtuemart menu) -> fill in the fields according to the language and click the Save button
