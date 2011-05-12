<?php

class Twig_Extensions_Extension_PHP extends Twig_Extension {
    public function __construct() {}

    public function getFilters() {
        return array(
            'is_array'  => new Twig_Filter_Function('_is_array'),
            'is_empty'  => new Twig_Filter_Function('_is_empty'),
            'empty'     => new Twig_Filter_Function('_is_empty'),
            'not_array' => new Twig_Filter_Function('_not_array'),
            'not_empty' => new Twig_Filter_Function('_not_empty')
        );
    }

    // interface required
    public function getName() {
        return 'phpFunctions';
    }

}

function _is_array($array = array()) {
    if (is_array($array)) {
        return TRUE;
    } else {
        return FALSE;
    }

}

function _is_empty($mixed = NULL) {
    if (isset($mixed) && !empty($mixed)) {
        return TRUE;
    } else {
        return FALSE;
    }

}

function _not_array($array = array()) {
    if (! (is_array($array) && count($array) > 0) ) {
        return TRUE;
    } else {
        return FALSE;
    }

}

function _not_empty($mixed = NULL) {
    if (! (isset($mixed) && !empty($mixed) > 0) ) {
        return TRUE;
    } else {
        return FALSE;
    }

} 