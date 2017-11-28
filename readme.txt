=== Affiliates Ninja Forms ===
Contributors: itthinx, proaktion, eggemplo
Donate link: http://www.itthinx.com/shop/
Tags: affiliate, affiliates, affiliate marketing, referral, growth marketing, form, forms, ninja forms, affiliate plugin, affiliate tool, contact form, e-commerce, lead, lead, marketing, money, partner, referral links, referrer, sales, track, transaction
Requires at least: 4.0.0
Tested up to: 4.9
Requires PHP: 5.5.0
Stable tag: 2.0.1
License: GPLv3

Integrates [Affiliates](https://wordpress.org/plugins/affiliates/), [Affiliates Pro](https://www.itthinx.com/shop/affiliates-pro/) and [Affiliates Enterprise](https://www.itthinx.com/shop/affiliates-enterprise/) with [Ninja Forms](https://wordpress.org/plugins/ninja-forms/).

== Description ==

This plugin integrates [Affiliates](https://wordpress.org/plugins/affiliates/), [Affiliates Pro](https://www.itthinx.com/shop/affiliates-pro/) and [Affiliates Enterprise](https://www.itthinx.com/shop/affiliates-enterprise/) with [Ninja Forms](https://wordpress.org/plugins/ninja-forms/).

This integration features:
- Affiliates can sign up through forms handled with Ninja Forms.
- Form submissions that are referred through affiliates, can grant commissions to affiliates and record referral details.

Requirements:

- [Ninja Forms](https://wordpress.org/plugins/ninja-forms/) : Please note that you must use version 3 or later.
- [Affiliates](https://wordpress.org/plugins/affiliates/) or [Affiliates Pro](https://www.itthinx.com/shop/affiliates-pro/) or [Affiliates Enterprise](https://www.itthinx.com/shop/affiliates-enterprise/) : This integration works fully with all versions.
- [Affiliates Ninja Forms](https://wordpress.org/plugins/affiliates-ninja-forms) : This plugin.

Documentation:

- [Integration with Affiliates](http://docs.itthinx.com/document/affiliates/setup/settings/integrations/)
- [Integration with Affiliates Pro](http://docs.itthinx.com/document/affiliates-pro//setup/settings/integrations/)
- [Integration with Affiliates Enterprise](http://docs.itthinx.com/document/affiliates-enterprise/setup/settings/integrations/)

== Installation ==

1. Install and activate [Ninja Forms](https://wordpress.org/plugins/ninja-forms/) version 3 or later.
2. Install and activate [Affiliates](https://wordpress.org/plugins/affiliates/) or [Affiliates Pro](https://www.itthinx.com/shop/affiliates-pro/) or [Affiliates Enterprise](https://www.itthinx.com/shop/affiliates-enterprise/).
3. Install and activate this integration plugin [Affiliates Ninja Forms](https://wordpress.org/plugins/affiliates-ninja-forms).
4. Use form actions to enable affiliate registration and/or affiliate commissions through referrals for desired form. Please refer to the documentation for details.

Note that you can install the plugins from your WordPress installation directly: use the *Add new* option found in the *Plugins* menu.
You can also upload and extract them in your site's `/wp-content/plugins/` directory or use the *Upload* option.

== Frequently Asked Questions ==

== Screenshots ==

Please refer to the Documentation for details:

- [Integration with Affiliates](http://docs.itthinx.com/document/affiliates/setup/settings/integrations/)
- [Integration with Affiliates Pro](http://docs.itthinx.com/document/affiliates-pro//setup/settings/integrations/)
- [Integration with Affiliates Enterprise](http://docs.itthinx.com/document/affiliates-enterprise/setup/settings/integrations/)

== Changelog ==

= 2.0.1 =
* Added the changelog.txt
* Fixed : check that user is created (and not an error object) before logging in.
* Fixed : affiliate registration form also not to be shown for pending or deleted affiliates.
* Added a missing translation (referral description).
* Fixed : currency was only used from rate.
* Wordpress 4.9 compatible.

= 2.0.0 =
* Affiliates, Affiliates Pro and Affiliates Enterprise 2.x and 3.x compatible.
* Ninja Forms 3 compatible.
* Wordpress 4.8.2 compatible.

== Upgrade Notice ==

This release contains fixes related to the affiliate registration and to the currency used for form referrals.
