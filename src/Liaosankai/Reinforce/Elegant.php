<?php

namespace Liaosankai\Reinforce;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Validator as Validator;

/**
 * Eloquent strengthening
 *
 * @author Liao San-Kai <liaosankai@gmail.com>
 * @copyright (c) 2013, Liao San-Kai
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
abstract class Elegant extends Eloquent
{

    const HAS_ONE = 'hasOne';
    const HAS_MANY = 'hasMany';
    const BELONGS_TO = 'belongsTo';
    const BELONGS_TO_MANY = 'belongsToMany';
    const MORPH_TO = 'morphTo';
    const MORPH_ONE = 'morphOne';

    /**
     * Validate rules
     *
     * @see http://laravel.com/docs/validation
     * @var array
     */
    protected $rules = array();

    /**
     * Relationships table
     *
     * @example
     *
     * If you have a relation method as follow:
     *
     *   public function user() {
     *       return $this->belongsTo('User', 'custom_key');
     *   }
     *
     * Now you can use variable to set them like:
     *
     *   array(
     *       'user' => array(self::BELONGS_TO, array("User', 'custom_key'))
     *   )
     *
     * Here is all relaitons params example:
     *
     *   array(
     *      'relation' => array(self::HAS_ONE, array($related, $foreignKey)),
     *      'relation' => array(self::HAS_MANY, array($related, $foreignKey)),
     *      'relation' => array(self::BELONGS_TO, array($related, $foreignKey)),
     *      'relation' => array(self::BELONGS_TO_MANY, array($related, $table, $foreignKey, $otherKey)),
     *      'relation' => array(self::MORPH_TO, array($name, $type, $id)),
     *      'relation' => array(self::MORPH_ONE, array($related, $name, $type, $id)),
     *      'relation' => array(self:::MORPH_MANY, array($related, $name, $type, $id))
     *   );
     *
     * @see http://laravel.com/docs/eloquent#relationships
     * @var array
     */
    protected $relationships = array();

    /**
     * Create a new Elegant model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        //remove empty rules
        $this->rules = array_filter($this->rules);
    }

    /**
     * Determine if a given attribute is dirty with type insensitive
     *
     * @param  string  $attribute
     * @param  bool    $insensitive type insensitive
     * @return bool
     */
    public function isDirty($attribute, $insensitive = false)
    {
        return array_key_exists($attribute, $this->getDirty($insensitive));
    }

    /**
     * Get the attributes that have been changed since last sync with type insensitive
     *
     * @param  bool $insensitive type insensitive
     * @return array
     */
    public function getDirty($insensitive = false)
    {
        $dirty = array();

        foreach ($this->attributes as $key => $value) {
            if ($insensitive) {
                if (!array_key_exists($key, $this->original) or $value != $this->original[$key]) {
                    $dirty[$key] = $value;
                }
            } else {
                if (!array_key_exists($key, $this->original) or $value !== $this->original[$key]) {
                    $dirty[$key] = $value;
                }
            }
        }

        return $dirty;
    }

    /**
     * Save the model to the database with validate.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array())
    {
        $dirty = $this->getDirty(true);
        foreach ($this->rules as $column => $norm) {
            //rules from string to array
            $rules = is_array($norm) ? $norm : explode('|', $norm);
            //ignore 'unique' rule when attribute is not dirty
            foreach ($rules as $key => $rule) {
                if (!array_key_exists($column, $dirty) and str_contains($rule, 'unique:') === true) {
                    unset($rules[$key]);
                    $this->rules[$column] = $rules;
                }
            }
        }

        // make a new validator object
        $checker = Validator::make($this->getAttributes(), $this->rules);
        // check for failure
        if ($checker->fails()) {
            throw new ValidateException(get_class($this), $checker);
        }

        return parent::save($options);
    }

    /**
     * Call the relation from relationships table
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (array_key_exists($method, $this->relationships)) {
            $relation = array_get($this->relationships[$method], 0, null);
            $args = array_get($this->relationships[$method], 1, array());
            if (!$relation || !in_array($relation, array(
                        self::HAS_ONE,
                        self::HAS_MANY,
                        self::BELONGS_TO,
                        self::BELONGS_TO_MANY,
                        self::MORPH_TO,
                        self::MORPH_ONE,
                    ))) {
                throw new \InvalidArgumentException('Invaild relation constants');
            }
            return call_user_func_array(array($this, $relation), $args);
        }
        return parent::__call($method, $parameters);
    }

    /**
     * Get Relations Key
     *
     * @return array
     */
    public function getRelationsKey()
    {
        return array_keys($this->relationships);
    }

    /**
     * Set where between with string like 'YYYY-MM-DD ~ YYYY-MM-DD' or '1 ~ 99'
     *
     * @example
     *
     *   $model = new ModelExtendElegant;
     *   $model->range('price', '0 ~ 99')
     *         ->range('created_at', '2013-01-01 ~ 2013-12-27')
     *         ->get();
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param string $range
     * @param string $delimiter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRange($query, $column = null, $range = null, $delimiter = '~')
    {
        $between = explode($delimiter, $range);

        if (count($between) == 2) {
            $start = trim(array_get($between, 0, ''));
            $end = trim(array_get($between, 1, ''));

            if (($start == '' or $start == '*') and $end != '') {
                //range: "* ~ 10"
                $query->where($column, '<=', $end);
            } else if ($start != '' and ($end == '' or $end == '*')) {
                //range: "1 ~ *"
                $query->where($column, '>=', $start);
            } else {
                //range: "1 ~ 10"
                $query->whereBetween($column, array($start, $end));
            }
        }
        return $query;
    }

    /**
     * Set order by with string like 'column:desc'
     *
     *   $model = new ModelExtendElegant;
     *         ->sortBy('updated_at:desc')
     *         ->get();
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $sort_by
     * @param string $delimiter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortBy($query, $sort_by = null, $delimiter = ':')
    {
        $order_by = explode($delimiter, $sort_by);
        $column = array_get($order_by, 0, 'id');
        $direction = strtolower(array_get($order_by, 1, 'asc'));
        $direction = in_array($direction, array('desc', 'asc')) ? $direction : 'asc';
        return $query->orderBy($column, $direction);
    }

}
