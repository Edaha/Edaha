<?php
namespace Edaha\Entities;

interface EntityInterface {
    public static function loadFromDb(array $identifiers, object &$db);
    public static function loadFromAssoc(array $assoc);
}
