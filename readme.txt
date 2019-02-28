=== Quick Login ===
Contributors: andreiigna
Tags: login, sign in, twitter, google, facebook, linkedin, oauth, register
Requires at least: 4
Tested up to: 5.1
Requires PHP: 5.6
Stable tag: trunk
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Let your visitors log in with their existing accounts! Supports Twitter, Facebook, Google, WordPress.com and LinkedIn

== Description ==

Quick Login adds extra login methods on your site, letting your visitors quickly log in or register with an existing account. Has support for the most popular logins: Twitter, Google, Facebook, WordPress.com and LinkedIn.

Faster log in or register will increase percent of logged in users on your site, especially helpful on e-commerce sites where the checkout should be as simple as possible.

The plugin is easy to set up, each provider has detailed instructions on how to enable it.

Includes **support for WooCommerce** sections too!

## Features & options

* One-click login with popular providers
  * Twitter
  * Google
  * Facebook
  * WordPress.com
  * LinkedIn
* Button style options
  * Icons
  * Buttons with icon & text
* Logins placement
  * On Login page
  * On Register page
  * On WooCommerce pages: My account, Login, Register and Checkout
  * On page or article comments section
  * Embed in any page with `[quick-login]` shortcode
* Link / Unlink providers from profile page (WordPress and WooCommerce)

> Be aware that Quick Login requires the PHP version to be at least 5.6

== Frequently Asked Questions ==

= How to enable login providers? =

All login providers are available on “WP Admin -> Settings -> Quick Login” page.
Each provider has detailed instructions for set up, including info on how to create or edit specific integrations and adding the cutom codes on your site.

= How to embed login buttons on a page, article or widget? =

Quick Login can add buttons on Login, Register, Comment and WooCommerce pages through plugin options.
Login buttons can be added in pages, articles or widgets with `[quick-login]` shortcode. There are more details on how to embed this shortcode in “WP Admin -> Settings -> Quick Login -> Embed section” on your site.

= How the log in or register process works? =

When a site visitor clicks on a Quick Login button, ex: `Sign in with Google`, the following process takes place:
* User is redirected to Google to authorise your website to read account info
* If authorised, user is sent back to your WordPress site
* If Google account is already linked to WordPress user, the WordPress user is logged in
* If Google account is not linked to a WordPress user AND the email matches a WordPress user, the Google account is linked to WordPress user and the user is logged in
* If Google account is not linked AND email doesn't match a WordPress user, a new WordPress is registered with details (username, email, name) from Google account

The process is similar for the other providers, except in the case when the provider (ex: Pinterest, Instagram) doesn't allow the WordPress site to read user's email. In this case, the user can't be authenticated on website and needs to Log in or Register first, then link the provider account in User Edit page.

= Quick Login doesn't work, with errors on pages or logins not showing up =

Because of newer functionality & security in PHP, this plugin requires the PHP version to be at least 5.6.
If the website server already has PHP >= 5.6 and the plugin still doesn't work, please send a support request either on WordPress support forum or [plugin GitHub page](https://github.com/LayeredStudio/quick-login)

== Screenshots ==

1. Login page example
2. WooCommerce checkout example
3. Main settings page: Set-up and enable providers, control style & placement
4. Link / Unlink providers on User profile page
5. Provider set-up page, with instructions

== Changelog ==

= 1.0 - 28 Feb 2019 =
* Added - Support for Google G Suite domain. [Thanks Jeffrey](https://github.com/LayeredStudio/quick-login/pull/2)
* Updated - Register user with built-in WordPress functions. [Thanks Jeffrey](https://github.com/LayeredStudio/quick-login/pull/1)
* Updated - Register user through WooCommerce functions, when installed

= 0.8 - 3 Feb 2019 =
* Updated - Facebook integration to use latest Facebook Graph API & fields

= 0.7 - 12 January 2019 =
* Added - Filter users in WP Admin by the linked provider

= 0.6 - 10 January 2019 =
* Updated - Security & functionality updates for login providers
* Updated - Do not allow registration for users without email address

= 0.5 - 23 July 2018 =
* Added - LinkedIn provider
* Updated - Show info & scope for each connected provider

= 0.4 - 29 May 2018 =
* Updated - Display connected providers on Users list page
* Fixed - Login form in comments only if user is logged out

= 0.3 - 11 May 2018 =
* Added - Link / Unlink accounts from User edit page & WooCommerce account page
* Added - More WP hooks

= 0.2 - 9 May 2018 =
* Added - Users' conected providers on Users page

= 0.1 =
* Plugin release. Includes Twitter, Google, Facebook and WordPress.com providers
