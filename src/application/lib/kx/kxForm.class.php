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
class kxForm {
    private static $instance = null;
    /**
     * Array to hold field names
     *
     * @access public
     * @var    array
     */
    public static $values = array();
    /**
     * Array to hold rulesets
     *
     * @access public
     * @var    array
     */
    public static $rules = array();
    
    /**
     * Sets the class instance and form values
     *
     * @access public
     * @param array    Form data
     * @return object  kxForm
     */
    public static function validate($data) {
        if (empty(self::$instance)) {
            self::$instance = new self;
        }
        foreach ($data as $key => $value) {
            self::$values[$key] = $value;
        }
        
        return self::$instance;
    }
    
    /**
     * Adds a ruleset to $rules
     *
     * @access public
     * @param string    Input field
     * @param string    Rule name
     * @param boolean   Expected result
     * @param string    Comparison value
     * @return object   kxForm
     */
    public static function addRule($key, $rule, $expected = true, $compare = '') {
        if (empty(self::$instance)) {
            self::validate(kxEnv::$request);
        }
        self::$rules[$key][$rule] = array('expects' => $expected, 'compare' => $compare);
        return self::$instance;
    }
    
    /**
     * Calls checkRules for each ruleset
     *
     * @access public
     */
    public static function check() {
        try {
            foreach (self::$rules as $key => $value) {
                self::_checkRules($key, $value);
            }
        } catch (kxException $kxE) {
            kxFunc::showError($kxE->getMessage());
        }
        self::$values = array();
        self::$rules = array();
    }
    
    /**
     * Applies the rulesets to the data,
     * errors out if they don't match.
     *
     * @access private
     * @param string    Input field
     * @param array     Ruleset array
     */
    private static function _checkRules($input, $rules) {
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
                    throw new kxException(sprintf(_gettext('Invalid rule for %s'),$input));
                break;
            }
            
            if (($check == 'required' && !$result) || (!empty(self::$values[$input]) && $result != $ruleset['expects'])) {
                throw new kxException(sprintf(_gettext('Invalid form entry - %s - %s'), $input, $check));
            }
        }
    }
    
    /**
     * Determines if a required value exists
     *
     * @access private
     * @param string    Input field
     */
    private static function _checkRequired($value) {
        if (array_key_exists($value, self::$values) && !empty(self::$values[$value])) {
            return true;
        } else {
            return false;
        }
    }
    
}

?>