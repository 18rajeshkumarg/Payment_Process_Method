# PayMongo Payment Process Method (PHP)

A simple PayMongo payment integration built with vanilla PHP. Customers enter an amount, a PayMongo payment link is created on the server, and the customer is redirected to the PayMongo hosted checkout page. A webhook (`callback.php`) records the payment outcome.

## Files

| File                 | Purpose                                                                  |
|----------------------|--------------------------------------------------------------------------|
| `index.php`          | Landing page with a payment amount form.                                 |
| `create_payment.php` | Validates the amount and creates a PayMongo payment link via the API.    |
| `callback.php`       | Webhook endpoint that logs payment success/failure to `payments.log`.    |
| `config.example.php` | Credentials template. Copy to `config.php` and fill in your secret key.  |
| `payments.log`       | Written at runtime by the webhook (auto-generated, gitignored).          |

## Requirements

- PHP 8.1+ with the `curl` extension (built-in server is enough for local testing).

## Setup

1. Get your [PayMongo](https://paymongo.com) secret key from the dashboard.
2. Copy `config.example.php` to `config.php` and replace the placeholder:

   ```php
   return [
       'paymongo_secret_key' => 'sk_test_xxxxxxxxxxxxxxxxxxxx',
   ];
   ```

   `config.php` is gitignored and never committed. Alternatively, set the `PAYMONGO_SECRET_KEY` environment variable.

3. Start the built-in PHP server from this directory:

   ```bash
   php -S localhost:8000
   ```

4. Open http://localhost:8000 in your browser, enter an amount (min. PHP 100), and click **Pay Now**.

## Webhook (callback.php)

Point your PayMongo dashboard webhook to `https://<your-domain>/callback.php` with the `payment.paid` event. Payments are appended to `payments.log`.

> **Important:** PayMongo APIs/PayMongo Cards is a paid service provided by PayMongo. Always keep your secret key server-side only.

## Notes

- GitHub Pages serves only static files and cannot execute PHP. To make this live you need a PHP-capable host (e.g. a VPS, shared hosting, or a PaaS that supports PHP).