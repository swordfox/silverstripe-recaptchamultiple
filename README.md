# silverstripe-recaptchamultiple
A spam protector and form field using the Google's reCAPTCHA 3 that supports multiple forms

Based on https://github.com/UndefinedOffset/silverstripe-nocaptcha - The main changes are the way it injects CustomJs, most of it has been moved to the JS file.

## Requirements
* SilverStripe 4.x
* [SilverStripe Spam Protection 3.x](https://github.com/silverstripe/silverstripe-spamprotection/)
* PHP CURL

## Installation
```
composer require swordfox/silverstripe-recaptchamultiple
```

After installing the module via composer or manual install you must set the spam protector to RecaptchaMultipleProtector, this needs to be set a config file e.g. mysite/\_config/recaptchamultiple.yml.
```yml
SilverStripe\SpamProtection\Extension\FormSpamProtectionExtension:
  default_spam_protector: Swordfox\RecaptchaMultiple\Forms\RecaptchaMultipleProtector
```

Add the "spam protection" field to your form fields.

```php
$form->enableSpamProtection()
	->fields()->fieldByName('Captcha')
	->setTitle("Spam protection")
	->setDescription("Please tick the box to prove you're a human and help us stop spam.");
```

Then finally, add this to your Page.ss template after your jQuery script.

```php
<% if $RecaptchaMultipleFields %>
$RecaptchaMultipleFields
<% end_if %>
```

## Configuration
There are multiple configuration options for the field, you must set the site_key and the secret_key which you can get from the [reCAPTCHA page](https://www.google.com/recaptcha). These configuration options must be added to a config filee.g. mysite/\_config/recaptchamultiple.yml.
```yml
Swordfox\RecaptchaMultiple\Forms\RecaptchaMultipleField:
    site_key: "YOUR_SITE_KEY" #Your site key (required)
    secret_key: "YOUR_SECRET_KEY" #Your secret key (required)
    verify_ssl: true #Allows you to disable php-curl's SSL peer verification by setting this to false (optional, defaults to true)
    default_theme: "light" #Default theme color (optional, light or dark, defaults to light)
    default_type: "image" #Default captcha type (optional, image or audio, defaults to image)
    default_size: "normal" #Default size (optional, normal, compact or invisible, defaults to normal)
    default_badge: "bottomright" #Default badge position (bottomright, bottomleft or inline, defaults to bottomright)
    proxy_server: "" #Your proxy server address (optional)
    proxy_auth: "" #Your proxy server authentication information (optional)
```
