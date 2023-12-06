=== License Manager for WooCommerce ===
Contributors: wpexpertsio
Tags: license key, license, key, software license, serial key, manager, woocommerce, wordpress
Requires at least: 4.7
Tested up to: 6.4.1
Stable tag: 3.0.5
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Easily sell and manage software license keys through your WooCommerce shop

== Description ==
The **License Manager for WooCommerce** allows you to easily sell and manage all of your digital license keys. With features like the bulk importer, automatic delivery, automatic stock management, and database encryption, your shop will now run easier than ever.

[Plugin & API Documentation](https://www.licensemanager.at/docs)

#### Key features

* Display the license keys section inside WooCommerce ‘s My Account Page
* Allow users to activate/deactivate their license keys
* Allow users to download license certificates 
* Admins can add a company logo on a license certificate
* Admins can do a one-click migration of the License Key from the Digital License Manager
* Admin can generate licenses for all past orders
* Automatically sell and deliver license keys through WooCommerce.
* Automatically manage the stock for licensed products.
* Activate, deactivate, and check your licenses through the REST API.
* Manually resend license keys.
* Add and import license keys and assign them to WooCommerce products.
* All licenses are encrypted to prevent unauthorized use.
* Administrators can activate or deactivate user accounts.
* Allows users to add duplicate license keys into the database.
* The order status tab provides license key delivery settings.
* Import license keys by file upload.
* Export license keys as PDF or CSV. 
* Manage the status of your license keys.
* Create license key generators with custom parameters.
* Assign a generator to one (or more!) WooCommerce product(s), these products then automatically create a license key whenever they are sold.

= License Manager for WooCommerce Pro = 
License Manager for WooCommerce Pro allows you to enhance the capabilities for your eCommerce website with features like:

* **Download Expires** - Download expired products and generates new license keys.
* **Product Download Detail** - Enters a change log and product version from the settings.
* **Validate Customer Licenses** - Validate customer licenses using their ID.
* **Ping Request** - Create a ping request to check the client-server connection.

View License Manager for WooCommerce Pro [pricing plans](https://www.licensemanager.at/pricing/).

#### API

The plugin also offers additional endpoints for manipulating licenses and generator resources. These routes are authorized via API keys (generated through the plugin settings) and accessed via the WordPress API. An extensive [API documentation](https://www.licensemanager.at/docs/rest-api/getting-started/api-keys) is also available.

#### Need help?

If you have any feature requests, need more hooks, or maybe have even found a bug, please let us know in the support forum or e-mail us at <support@wpexperts.io>. We look forward to hearing from you!

You can also check out the documentation pages, as they contain the most essential information on what the plugin can do for you.

#### Important

The plugin will create two files inside the `wp-content/uploads/lmfwc-files` folder. These files (`defuse.txt` and `secret.txt`) contain cryptographic secrets which are automatically generated if they don't exist. These cryptographic secrets are used to encrypt, decrypt and hash your license keys. Once they are generated please **back them up somewhere safe**. In case you lose these two files your encrypted license keys inside the database will remain forever lost!

If you would like to contribute to any of these [libraries](https://www.licensemanager.at/docs/rest-api/libraries/nodejs) in these languages (Node.js, Python, PHP, Ruby, .NET, C, C#, C++, and Golang), please visit our library page for more details.

#### Note

Few features like user license display on account page and license certification are fork from Digital License Manager plugin by Darko Gjorgjijoski and we have changed the code according to our need.

== Installation ==

#### Manual installation

1. Upload the plugin files to the `/wp-content/plugins/license-manager-for-woocommerce` directory, or install the plugin through the WordPress *Plugins* page directly.
1. Activate the plugin through the *Plugins* page in WordPress.
1. Use the *License Manager* → *Settings* page to configure the plugin.

#### Installation through WordPress

1. Open up your WordPress Dashboard and navigate to the *Plugins* page.
1. Click on *Add new*
1. In the search bar type "License Manager for WooCommerce"
1. Select this plugin and click on *Install now*

#### Important

The plugin will create two files inside the `wp-content/uploads/lmfwc-files` folder. These files (`defuse.txt` and `secret.txt`) contain cryptographic secrets which are automatically generated if they don't exist. These cryptographic secrets are used to encrypt, decrypt and hash your license keys. Once they are generated please **back them up somewhere safe**. In case you lose these two files your encrypted license keys inside the database will remain forever lost!

== Frequently Asked Questions ==

= Is there a documentation? =

Yes, there is! An extensive documentation describing the plugin features and functionality in detail can be found on the [plugin homepage](https://www.licensemanager.at/docs/).

= What about the API documentation? =

Again, yes! Here you can find the [API Documentation](https://www.licensemanager.at/docs/rest-api/getting-started/requirements) detailing all the new endpoint requests and responses. Have fun!

= Does the plugin work with variable products? =

Yes, the plugin can assign licenses or generators to individual product variations.

= Can I sell my own license keys with this plugin? =

Yes, the plugin allows you to import an existing list of license keys via the file upload (CSV or TXT).

= Can I use this plugin to provide a licensing system for my own software? =

Of course! The plugin comes with REST API routes which allow you to activate, deactivate, and validate license keys.

== Screenshots ==

1. What's New
2. The license key overview page.
3. Add a single license key.
4. Add multiple license keys in bulk.
5. WooCommerce simple product options.
6. WooCommerce variable product options.
7. The generators overview page.
8. Create a new license key generator.
9. REST API

== Changelog ==

[See changelog for all versions](https://raw.githubusercontent.com/wpexpertsio/license-manager-woocommerce/main/CHANGELOG.md).

== Upgrade Notice ==

= 3.0.5 =
- Fixed - Settings were not updating.
- Fixed - Php warning in some cases.

= 3.0.4 =
- Fixed - License key was not appearing on My Account page.
- Fixed - Php notices in some cases.
- Fixed - Code optimization.
- Fixed - License keys not receiving when order is processing.
- Fixed - PDF not downloading until the order is not completed.

= 3.0.3 =
- Fixed - Php warnings appears in some cases.

= 3.0.2 =
- Fixed - License not activating via API

= 3.0.1 =
- Fixed - Resolved PHP errors that occurred in certain cases.

= 3.0 =
Tested up to WooCommerce v8.2 and WordPress v6.4
Added - License Activations
Added - License and Generator delete endpoints
Added - License PDF Certificates 
Added - Migration and Past Order License Generator tools
Added - License Expiration Format
Added - Single License Page in My account
Fixed - UserId variable in lmfwc_add_license function
Fixed - OrderBy query Vulnerability 

= 2.2.11 =
* Improvement - Clean up the SQL query to make it secure.

= 2.2.10 =
The reported vulnerability has been resolved by updating the Feedback SDK to the latest version.

= 2.2.9 =
Tested up to WooCommerce v7.8.0 and WordPress v6.2.2

= 2.2.8 =
Update - Upgrade Menu Added

= 2.2.7 =
Plugin structural changes

= 1.2.1 =
Please deactivate the plugin and reactivate it.

= 1.1.1 =
Copy your previously backed up `defuse.txt` and `secret.txt` to the `wp-content/uploads/lmfwc-files/` folder. Overwrite the existing files, as those are incompatible with the keys you already have in your database. If you did not backup these files previously, then you will need to completely delete (not deactivate!) and install the plugin anew.

= 1.0.0 =
There is no specific upgrade process for the initial release. Simply install the plugin and you're good to go!