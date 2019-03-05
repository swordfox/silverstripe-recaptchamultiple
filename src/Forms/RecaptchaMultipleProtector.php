<?php

namespace Swordfox\RecaptchaMultiple\Forms;

use SilverStripe\SpamProtection\SpamProtector;


class RecaptchaMultipleProtector implements SpamProtector {
    /**
     * Return the Field that we will use in this protector
     * @return string
     */
    public function getFormField($name="Recaptcha2Field", $title='Captcha', $value=null) {
        return RecaptchaMultipleField::create($name, $title);
    }

    /**
     * Not used by RecaptchaMultiple
     */
    public function setFieldMapping($fieldMapping) {}
}
?>
