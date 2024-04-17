<?php

namespace Triton\Entities\Variable;

use Triton\Entities\Recipient\RecipientField;

class Reserved {

    /**
     * Returns all reserved vars flat list
     *
     * @return array
     */
    public static function all()
    {
        return array_merge(
            self::phpKeywords(),
            self::tritonSysVars(),
            RecipientField::all()
        );
    }

    /**
     * @return array
     */
    public static function phpKeywords()
    {
        return [
            '__halt_compiler',
            'abstract',
            'and',
            'array',
            'as',
            'break',
            'callable',
            'case',
            'catch',
            'class',
            'clone',
            'const',
            'continue',
            'declare',
            'default',
            'die',
            'do',
            'echo',
            'else',
            'elseif',
            'empty',
            'enddeclare',
            'endfor',
            'endforeach',
            'endif',
            'endswitch',
            'endwhile',
            'eval',
            'exit',
            'extends',
            'final',
            'finally',
            'for',
            'foreach',
            'function',
            'global',
            'goto',
            'if',
            'implements',
            'include',
            'include_once',
            'instanceof',
            'insteadof',
            'interface',
            'isset',
            'list',
            'namespace',
            'new',
            'or',
            'print',
            'private',
            'protected',
            'public',
            'require',
            'require_once',
            'return',
            'self',
            'static',
            'switch',
            'this',
            'throw',
            'trait',
            'try',
            'unset',
            'use',
            'var',
            'while',
        ];
    }

    /**
     * @return array
     */
    public static function tritonSysVars()
    {
        return [
            'tracking_category_id',
            'tracking_category_name',
            'dc_name'
        ];
    }
}