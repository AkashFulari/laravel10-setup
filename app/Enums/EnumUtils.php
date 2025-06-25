<?php

namespace App\Enums;

class EnumUtils
{
    public static function parseValues($any)
    {
        $values = [];
        foreach ($any as $enum) {
            $values[] = $enum->value;
        }
        return $values;
    }

    public static function parseNames($any)
    {
        $names = [];
        foreach ($any as $enum) {
            $names[] = $enum->name;
        }
        return $names;
    }
    
    public static function valuesToValidateRule($any)
    {
        $values = "";
        foreach ($any as $enum) {
            $values .= $enum->value . ",";
        }
        $len = strlen($values);
        if ($len > 0) {
            $values = "|in:" . substr($values, 0, $len - 1);
        }
        return $values;
    }
    
    public static function namesToValidateRule($any)
    {
        $names = "";
        foreach ($any as $enum) {
            $names .= $enum->name . ",";
        }
        $len = strlen($names);
        if ($len > 0) {
            $names = "|in:" . substr($names, 0, $len - 1);
        }
        return $names;
    }
}