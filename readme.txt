=== Tip Jar WP ===
Contributors: tipjarwp
Tags: tip, donation, stripe, patreon, creators, recurring payments, apple pay, google pay, credit card, react
Donate link: https://tipjarwp.com
Requires at least: 4.8
Tested up to: 6.3
Requires PHP: 7.0
Stable tag: trunk
License: GPLv3
License URI: https://opensource.org/licenses/GPL-3.0

== Description ==
Since 2019, Tip Jar WP has helped creators like you earn over $1,000,000 combined! Made for creators, artists, teachers, service providers, and more, it gives you an extremely simple and affordable way to accept tips on your WordPress website. On the cutting edge of payment technologies through the power of Stripe, you can accept Apple Pay, Google Pay, standard credit cards, and more with a beautiful and responsively designed payment form.

If you have supporters that love what you do, many will be happy to send a tip to say "thanks" if you give them the option to do so. With out-of-the-box features including both automatic-recurring-payments, or one-time tips, Tip Jar WP makes that possible.

It's totally free to install. Give it a try today!

= Here's a few of the things included in Tip Jar WP: =

* **Recurring payments**
Users can choose to tip once, or on a recurring basis automatically.

* **Multi-Currency**
Allow your users to pay in their own currency.

* **User dashboard**
Your users can log in to print their receipts, review their plans, or cancel their plans at any time.

* **Apple Pay**
On Apple devices that support Apple Pay, users can tip with a single tap (or "look") with their device.

* **Google Pay**
Users of Google Chrome with saved credit cards can pay with a single tap.

* **3D Secure and Strong Customer Authentication**
You are protected from fraudulent purchases and chargebacks via 3D Secure, and also comply with SCA regulations in the EU.

* **GDPR Compliance Considerations**
- No personal information of any kind is stored, making it both secure and more GDPR compliant right out of the box.

* **No start-up or yearly costs required**
Tip Jar WP makes money through a 1% transaction fee, so you can install it and keep it forever, without paying anything.

* **"Pay what you want for a file" mode**
Allow your users to "pay what they want" for a file download.

* **Gutenberg, Classic Editor, and Page Builder support**
Customize your payment forms using the Gutenberg Block, or the built-in shortcode manager in Classic Editor mode.

== Installation ==
1. In your WordPress dashboard go to "Plugins", "Add New". Search for "Tip Jar WP" and click "Install". Then click "Activate".
2. Find "Tip Jar WP" on the left sidebar in your WordPress dashboard and click on it.
3. Follow the step-by-step "wizard" to make sure everything that needs to be configured, is! We made sure it covers all of the most important things so you don't need to do any guesswork during setup.
4. Once you've completed the set-up wizard, use the [tipjarwp] shortcode on any page/post, or use our Block in the Block Editor by typing /tipjarwp.

Find out more about installation on our guide:
[https://tipjarwp.com/getting-started-with-tip-jar-wp/](https://tipjarwp.com/getting-started-with-tip-jar-wp/)

== Frequently Asked Questions ==
1. Does this include the Stripe integration for free?
Yes. Stripe is the payment gateway that works with Tip Jar WP, and it is included for free.

2. Are there any up-sells or extensions?
No. Everything you will need to accept single and recurring tips is included in Tip Jar WP for free.

3. Do I need an SSL certificate in order to use this?
Yes. Most webhosts are now able to set this up for you at no extra cost. Ask them about "LetsEncrypt".

4. Is this plugin free to use?
Tip Jar WP is totally free to install. There is a 1% transaction fee (plus Stripe fees). So we (Tip Jar WP) don't make money until you do. That means we have your best interests in mind at all times; we aren't successful unless you are.

5. What about GDPR Compliance?
This plugin stores absolutely no personally identifiable information about your users when they make a payment. No names, credit card information, IP addresses, nothing. Only emails are stored in WordPress core's "users" table, and can be erased using WordPress's normal erase-personal-data process. We recommend adding a note to your privacy policy stating that the only information recorded during a payment is the email address, and that all other data is handled by Stripe.com.

== Screenshots ==
1. Put the payment form anywhere on your website with the shortcode [tipjarwp], or customize it with the Block editor.
2. Customize each form to your specific needs.
3. A great way to showcase your podcast and gain supporters at the same time.
4. File Download Mode allows your users to pay what they want for a file download. Great for music artists to sell/give-away their music.
5. Your users can pay in their own currency, making them more comfortable with the payment process.
6. Accept single or recurring payments.
7. Accept Apple Pay on supported Apple Devices, and allow users to pay with one tap!
8. Accept Google Pay on supported Google/Android Devices (like Chrome), and allow users to pay with one tap!
9. Accept credit cards for people without Apple Pay or Google Pay.
10. Beautiful receipts with no page refreshing!
11. Your supporters can leave a note after their payment.
12. User accounts are automatically generated when a payment happens, reducing user friction during purchase.
13. Users can easily log in at any time, confirming their account through their email.
14. Beautiful and simple payment management dashboard for your users.
14. Beautiful and simple payment management dashboard for your users.

== Changelog ==

= 2.1.0 - 2023-08-14 =
* PHP8 Compatibility
* Fixed an issue with new currencies added at Stripe
* Small style improvements for certain themes.

= 2.0.0 - 2021-01-01 =
* Update to use latest react library for stripe: @stripe/react-stripe-js
* Make sure that non zero-decimal currencies always show 2 decimals (like 5.20 instead of 5.2)
* Ensure title of Tip Form is always centered.
* Remove special characters from statement descriptor.
* Apply Form ID to form JSON in DB when created.
* Use mobile card mode based on container width (not screen width, as this works for thin sidebars)

= 1.0.2.8 - 2020-10-14 =
* Ensure Apple Pay domain verification file is always up to date upon creation.

= 1.0.2.7 - 2020-09-20 =
* Improved Apple Pay domain verification.

= 1.0.2.6 - 2020-06-11 =
* Improve handling of upfront card errors, like insufficient funds, and provide a helpful response to the user.

= 1.0.2.5 - 2020-05-04 =
* Ensure trailing slashes are added to endpoints, to fix issue with browser redirects changing POST requests to GET requests with no payload.

= 1.0.2.4 - 2020-03-18 =
* Fixed caching issues for logged-out users.

= 1.0.2.3 - 2019-12-03 =
* Added subscription recovery features, like email notifications when recurring payments fail, and the ability for the user to change/update the card used for a subscription.
* Added a user role and capabilities to do Tip Jar Manager things.
* Added the ability to put different types of media at the top of the payment form, like embedded content (YouTube, etc).
* Added Portuguese translation file.
* Added a custom table for notes with support for threaded replies.
* Zip code is no longer a required field to support countries without zip codes. Stripe already handled this automatically.
* When displaying money amounts, format it using the user's browser's language and location settings.

= 1.0.2.2 - 2019-08-12 =
* Classic Mode shortcode editor: account for shortcodes with no "form" paramater.
* Fix email receipt links if opening in a modal.
* Add better checking for Stripe account name when displaing connected account.

= 1.0.2.1 - 2019-08-05 =
* Ensure customer profile is only generated once.

= 1.0.2.0 - 2019-08-02 =
* Gutenberg Block added.
* Shortcode editor added.
* File Download Mode added, to allow people to pay what they want for a file.

= 1.0.0.0 - 2019-07-28 =
* New: Initial Release
