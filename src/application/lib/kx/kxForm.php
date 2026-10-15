<?php

namespace kx;

use kx\Exceptions\kxException;

// Functions for validating form inputs
class kxForm
{
    /**
     * Array to hold field names.
     */
    public static array $values = [];

    /**
     * Array to hold rulesets.
     */
    public static array $rules = [];
    private static kxForm $instance;

    /**
     * Sets the class instance and form values.
     *
     * @param array    Form data
     * @param mixed $data
     *
     * @return object kxForm
     */
    public static function validate($data): kxForm
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        foreach ($data as $key => $value) {
            self::$values[$key] = $value;
        }

        return self::$instance;
    }

    /**
     * Adds a ruleset to $rules.
     *
     * @param string    Input field
     * @param string    Rule name
     * @param bool   Expected result
     * @param string    Comparison value
     * @param string $key      The name of the request field
     * @param string $rule     The type of rule to enforce (required, numeric, value)
     * @param bool   $expected huh
     * @param string $compare  For value checks, what it's compared to
     *
     * @return object kxForm
     */
    public static function addRule(string $key, string $rule, bool $expected = true, string $compare = ''): kxForm
    {
        if (empty(self::$instance)) {
            self::validate(kxEnv::$request);
        }
        self::$rules[$key][$rule] = ['expects' => $expected, 'compare' => $compare];

        return self::$instance;
    }

    /**
     * Calls checkRules for each ruleset.
     */
    public static function check(): void
    {
        try {
            foreach (self::$rules as $key => $value) {
                self::_checkRules($key, $value);
            }
        } catch (kxException $kxE) {
            kxFunc::showError($kxE->getMessage());
        }
        self::$values = [];
        self::$rules = [];
    }

    /**
     * Applies the rulesets to the data,
     * errors out if they don't match.
     *
     * @param mixed $input
     * @param mixed $rules
     */
    private static function _checkRules($input, $rules): void
    {
        foreach ($rules as $check => $ruleset) {
            switch ($check) {
                case 'required':
                    $result = self::_checkRequired($input);

                    break;

                case 'numeric':
                    $result = is_numeric(self::$values[$input]);

                    break;

                case 'value':
                    $result = (self::$values[$input] == $ruleset['compare']);

                    break;

                default:
                    throw new kxException(sprintf(_('Invalid rule for %s'), $input));

                    break;
            }

            if (('required' == $check && !$result) || (!empty(self::$values[$input]) && $result != $ruleset['expects'])) {
                throw new kxException(sprintf(_('Invalid form entry - %s - %s'), $input, $check));
            }
        }
    }

    /**
     * Determines if a required value exists.
     */
    private static function _checkRequired(string $value): bool
    {
        if (array_key_exists($value, self::$values) && !empty(self::$values[$value])) {
            return true;
        }

        return false;
    }
}
