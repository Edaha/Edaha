<?php

/*
 * This file is part of kusaba.
 *
 * kusaba is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * kusaba is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * kusaba; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
/*
 * Functions for validating form inputs
 * Last Updated: $Date: $
 * @author    $Author: $
 * @package   kusaba
 * @version   $Revision: $
 *
 */
class kxForm
{
    /**
     * Array to hold field names.
     *
     * @var array
     */
    public static $values = [];

    /**
     * Array to hold rulesets.
     *
     * @var array
     */
    public static $rules = [];
    private static $instance;

    /**
     * Sets the class instance and form values.
     *
     * @param array    Form data
     * @param mixed $data
     *
     * @return object kxForm
     */
    public static function validate($data)
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
     * @param mixed $key
     * @param mixed $rule
     * @param mixed $expected
     * @param mixed $compare
     *
     * @return object kxForm
     */
    public static function addRule($key, $rule, $expected = true, $compare = '')
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
    public static function check()
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
     * @param string    Input field
     * @param array     Ruleset array
     * @param mixed $input
     * @param mixed $rules
     */
    private static function _checkRules($input, $rules)
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
     *
     * @param string    Input field
     * @param mixed $value
     */
    private static function _checkRequired($value)
    {
        if (array_key_exists($value, self::$values) && !empty(self::$values[$value])) {
            return true;
        }

        return false;
    }
}
