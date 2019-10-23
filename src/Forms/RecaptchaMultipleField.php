<?php

namespace Swordfox\RecaptchaMultiple\Forms;

use SilverStripe\View\Requirements;
use SilverStripe\i18n\i18n;
use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Forms\FormField;
use SilverStripe\Control\Controller;

class RecaptchaMultipleField extends FormField {
    /**
     * Recaptcha Site Key
     * @config RecaptchaMultipleField.site_key
     */
    private static $site_key;

    /**
     * Recaptcha Secret Key
     * @config RecaptchaMultipleField.secret_key
     */
    private static $secret_key;

    /**
     * CURL Proxy Server location
     * @config RecaptchaMultipleField.proxy_server
     */
    private static $proxy_server;

    /**
     * CURL Proxy authentication
     * @config RecaptchaMultipleField.proxy_auth
     */
    private static $proxy_auth;

    /**
     * Verify SSL Certificates
     * @config RecaptchaMultipleField.verify_ssl
     * @default true
     */
    private static $verify_ssl=true;

    /**
     * Captcha theme, currently options are light and dark
     * @var string
     * @default light
     */
    private static $default_theme='light';

    /**
     * Captcha type, currently options are audio and image
     * @var string
     * @default image
     */
    private static $default_type='image';

    /**
     * Captcha size, currently options are normal, compact and invisible
     * @var string
     * @default normal
     */
    private static $default_size='normal';

    /**
     * Captcha theme, currently options are light and dark
     * @var string
     */
    private $_captchaTheme;

    /**
     * Captcha type, currently options are audio and image
     * @var string
     */
    private $_captchaType;

    /**
     * Captcha size, currently options are normal and compact
     * @var string
     */
    private $_captchaSize;

    /**
     * Captcha badge, currently options are bottomright, bottomleft and inline
     * @var string
     */
    private $_captchaBadge;

    /**
     * Captcha callback, user defined function
     * @var string
     */
    private $_captchaCallback;

    /**
     * Creates a new Recaptcha 2 field.
     * @param string $name The internal field name, passed to forms.
     * @param string $title The human-readable field label.
     * @param mixed $value The value of the field (unused)
     */
    public function __construct($name, $title=null, $value=null) {
        parent::__construct($name, $title, $value);

        $this->title = $title;

        $this->_captchaTheme=self::config()->default_theme;
        $this->_captchaType=self::config()->default_type;
        $this->_captchaSize=self::config()->default_size;
        $this->_captchaBadge=self::config()->default_badge;
    }

    /**
     * Adds in the requirements for the field
     * @param array $properties Array of properties for the form element (not used)
     * @return string Rendered field template
     */
    public function Field($properties=array()) {
        $siteKey=self::config()->site_key;
        $secretKey=self::config()->secret_key;

        if(empty($siteKey) || empty($secretKey)) {
            user_error('You must configure RecaptchaMultiple.site_key and RecaptchaMultiple.secret_key, you can retrieve these at https://google.com/recaptcha', E_USER_ERROR);
        }

        Requirements::javascript('swordfox/silverstripe-recaptchamultiple:javascript/RecaptchaMultiple.js', ["defer" => true]);

        return parent::Field($properties);
    }

    /**
     * Validates the captcha against the Recaptcha2 API
     * @param Validator $validator Validator to send errors to
     * @return bool Returns boolean true if valid false if not
     */
    public function validate($validator) {
        if(!isset($_REQUEST['g-recaptcha-response'])) {
            $validator->validationError($this->name, '_Please answer the captcha, if you do not see the captcha you must enable JavaScript', 'validation');
            return false;
        }

        if(!function_exists('curl_init')) {
            user_error('You must enable php-curl to use this field', E_USER_ERROR);
            return false;
        }

        $url='https://www.google.com/recaptcha/api/siteverify?secret='.self::config()->secret_key.'&response='.rawurlencode($_REQUEST['g-recaptcha-response']).'&remoteip='.rawurlencode($_SERVER['REMOTE_ADDR']);
        $ch=curl_init($url);
        $proxy_server=self::config()->proxy_server;
        if(!empty($proxy_server)){
            curl_setopt($ch, CURLOPT_PROXY, $proxy_server);

            $proxy_auth=self::config()->proxy_auth;
            if(!empty($proxy_auth)){
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_auth);
            }
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, self::config()->verify_ssl);

        $lnm=singleton(LeftAndMain::class);
        curl_setopt($ch, CURLOPT_USERAGENT, str_replace(',', '/', 'SilverStripe '.$lnm->CMSVersion()));
        $response=json_decode(curl_exec($ch), true);

        if(is_array($response)) {
            if(array_key_exists('success', $response) && $response['success']==false) {
                $validator->validationError($this->name, '_Please answer the captcha, if you do not see the captcha you must enable JavaScript', 'validation');
                return false;
            }
        }else {
            $validator->validationError($this->name, '_Captcha could not be validated', 'validation');
            return false;
        }


        return true;
    }

    /**
     * Sets the theme for this captcha
     * @param string $value Theme to set it to, currently the api supports light and dark
     * @return RecaptchaMultipleField
     */
    public function setTheme($value) {
        $this->_captchaTheme=$value;

        return $this;
    }

    /**
     * Gets the theme for this captcha
     * @return string
     */
    public function getCaptchaTheme() {
        return $this->_captchaTheme;
    }

    /**
     * Sets the type for this captcha
     * @param string $value Type to set it to, currently the api supports audio and image
     * @return RecaptchaMultipleField
     */
    public function setCaptchaType($value) {
        $this->_captchaType=$value;

        return $this;
    }

    /**
     * Gets the type for this captcha
     * @return string
     */
    public function getCaptchaType() {
        return $this->_captchaType;
    }


    /**
     * Sets the size for this captcha
     * @param string $value Size to set it to, currently the api supports normal, compact and invisible
     * @return RecaptchaMultipleField
     */
    public function setCaptchaSize($value) {
        $this->_captchaSize=$value;

        return $this;
    }

    /**
     * Gets the size for this captcha
     * @return string
     */
    public function getCaptchaSize() {
        return $this->_captchaSize;
    }

    /**
     * Sets the badge position for this captcha
     * @param string $value Badge to set it to, currently the api supports bottomright, bottomleft or inline
     * @return RecaptchaMultipleField
     */
    public function setCaptchaBadge($value) {
        $this->_captchaBadge=$value;

        return $this;
    }

    /**
     * Gets the Badge position for this captcha
     * @return string
     */
    public function getCaptchaBadge() {
        return $this->_captchaBadge;
    }

    /**
     * Sets the callback function
     * @param string $value
     * @return RecaptchaMultipleField
     */
    public function setCallback($value) {
        $this->_captchaCallback=$value;

        return $this;
    }

    /**
     * Gets the Badge position for this captcha
     * @return string
     */
    public function getCallback() {
        return $this->_captchaCallback;
    }

    /**
     * Gets the site key configured via RecaptchaMultipleField.site_key this is used in the template
     * @return string
     */
    public function getSiteKey() {
        return self::config()->site_key;
    }

    /**
     * Gets the form's id
     * @return string
     */
    public function getFormID() {
        return ($this->form ? $this->getTemplateHelper()->generateFormID($this->form):null);
    }
}
?>
