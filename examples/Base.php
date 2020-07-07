<?php
declare(strict_types=1);

final class BaseClass {}
final class ConstVarClass {
    public const CONST_VAR = 1;
}
final class StaticCallClass {
    public static function staticMethod() {}
}
final class StaticVarClass {
    public static $VAR1 = 'abc';
}

interface BaseInterface {}
trait BaseTrait {}

const BASE_CONST = 1;

function base_function() {}
function BaseClass() {}

define('DEFINED_VAR', 1);