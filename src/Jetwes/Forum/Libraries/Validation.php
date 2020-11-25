<?php namespace Jemy09\Forum\Libraries;

use Request;
use Jemy09\Forum\Libraries\Alerts;
use Validator;

class Validation {

    public static function processValidationMessages($messages)
    {
        foreach ($messages as $message)
        {
            Alerts::add('danger', $message);
        }
    }

    public static function check($type = 'thread')
    {
        $rules = config('forum.preferences.validation_rules');
        $validator = Validator::make(Request::all(), $rules[$type]);

        if (!$validator->passes())
        {
            self::processValidationMessages($validator->messages()->all());
            return false;
        }

        return true;
    }

}
