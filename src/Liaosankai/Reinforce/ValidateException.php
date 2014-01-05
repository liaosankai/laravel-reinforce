<?php

namespace Liaosankai\Reinforce;

/**
 * Validate exception
 *
 * @author Liao San-Kai <liaosankai@gmail.com>
 * @copyright (c) 2013, Liao San-Kai
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class ValidateException extends \Exception
{

    /**
     * Validator
     *
     * @var \Illuminate\Validation\Validator
     */
    private $validator;

    /**
     * Construct the exception
     *
     * @link http://php.net/manual/en/exception.construct.php
     */
    public function __construct($model, \Illuminate\Validation\Validator $validator, $message = null, $code = null, $previous = null)
    {
        $this->model = $model;
        $this->validator = $validator;
        $this->message = $model . ' failed to validate';
        parent::__construct($this->message, $code, $previous);
    }

    /**
     * For get validator
     *
     * @return \Illuminate\Validation\Validator
     */
    public function __get($name)
    {
        return $this->validator;
    }

}
