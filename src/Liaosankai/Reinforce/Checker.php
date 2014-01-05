<?php

namespace Liaosankai\Reinforce;

use Illuminate\Validation\Validator as Validator;

/**
 * Custom Validator for extends origin validator
 *
 * @author Liao San-Kai <liaosankai@gmail.com>
 * @copyright (c) 2013, Liao San-Kai
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class Checker extends Validator
{

    /**
     * The field under validation must be included in the given list of case-insensitive values.
     *
     * @param string $attribute
     * @param mixed $value
     * @reutnr bool
     */
    public function validateIni($attribute, $value, $parameters)
    {
        return in_arrayi($value, $parameters);
    }

}
