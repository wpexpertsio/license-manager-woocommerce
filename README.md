![License Manager for WooCommerce](https://ps.w.org/license-manager-for-woocommerce/assets/banner-772x250.jpg)

## Description ##
The License Manager for WooCommerce allows you to easily sell and manage Digital License Keys. With features like the bulk importer, automatic delivery, automatic stock management, and database encryption, your business will now run easier than ever.

## Features ##
* Automatically sell and deliver license keys through WooCommerce
* Automatically manage the stock for licensed products
* Activate, deactivate, and check your licenses through the REST API
* Manually resend license keys
* Add and import license keys and assign them to WooCommerce products
* Import license keys by file upload
* Export license keys as PDF or CSV
* Manage the status of your license keys
* Create license key generators with custom parameters
* Assign a generator to one (or more!) WooCommerce product(s), these products then automatically create a license key whenever they are sold.

## API Documentation ##
The plugin also offers additional endpoints for manipulating licenses and generator resources. These routes are authorized via API keys (generated through the plugin settings) and accessed via the WordPress API. An extensive [API documentation](https://www.licensemanager.at/docs/rest-api/getting-started/api-keys) is also available.

## Important Notice ##
The plugin will create two files inside the wp-content/uploads/lmfwc-files folder. These files (defuse.txt and secret.txt) contain cryptographic secrets which are automatically generated if they don’t exist. These cryptographic secrets are used to encrypt, decrypt and hash your license keys. Once they are generated please back them up somewhere safe. In case you lose these two files your encrypted license keys inside the database will remain forever lost!

## Contribute ##
* Helping users resolve issues realated to the plugin on WordPress.org support as contribution will be appreciated the most
* Before making a huge contribution in code please contact us once to discuss the road map and code related guidelines
