<?php

namespace App\Constraints\Searchable;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use RuntimeException;
use InvalidArgumentException;


trait SearchableTrait
{
    public function getQueryModeParameterName()
    {
        return 'mode';
    }
    /**
     * Applies filters.
     *
     * @param Builder $builder query builder
     * @param array   $query   query parameters to use for search - Request::all() is used by default
     */
    public function scopeFiltered(Builder $builder, array $query = [])
    {
        $query = ($query ?: Arr::get(Request::only('filters'), 'filters'));
        if (!is_array($query) || empty($query)) {
            return;
        }
        foreach ($query as $key => $q) {
            if (is_array($q) && count($q) === 1) {
                $query[$key] = reset($q);
            } elseif (is_array($q) && (preg_match('/^\((gt|ge|lt|le)\)(.+)$/', reset($q), $match) || preg_match('/^<.+,.+>$/', reset($q), $match))) {
                $query[$key] = '{' . implode('__', $q) . '}';
            } elseif(is_array($q)) {
                $query[$key] = implode(',', $q);
            }
        }
        $query = $this->mapSearchFields($query);
        $this->validateFieldNames($query);
        $mode = $this->getQueryMode($query);
        $query = $this->filterNonSearchableParameters($query);
        $constraints = $this->getConstraints($builder, $query);
        $this->applyConstraints($builder, $constraints, $mode, $query);
    }
    /**
     * Map query parameters based on fields that specified in map array
     *
     * @param array   $query   query parameters
     *
     * @return array
     */
    protected function mapSearchFields($query)
    {
        if (property_exists($this, 'mapSearchFields')) {
            foreach ($this->mapSearchFields as $key => $value) {
                if (Arr::has($query, $key)) {
                    $query[$value] = $query[$key];
                    unset($query[$key]);
                }
            }
        }
        return $query;
    }
    /**
     * Builds search constraints based on model's searchable fields and query parameters.
     *
     * @param Builder $builder query builder
     * @param array   $query   query parameters
     *
     * @return array
     */
    protected function getConstraints(Builder $builder, array $query)
    {
        $constraints = [];
        foreach ($query as $field => $values) {
            if ($this->isFieldSearchable($builder, $field)) {
                $constraints[$field] = $this->buildConstraints($values);
            }
        }
        return $constraints;
    }
    /**
     * Makes sure field names contain only allowed characters
     *
     * @param array $query
     */
    protected function validateFieldNames(array $query) {
        foreach ($query as $field => $values) {
            if (!preg_match('/^!?[a-zA-Z0-9\-_:\.]+$/', $field)) {
                throw new InvalidArgumentException(sprintf('Incorrect field name: %s', $field));
            }
        }
    }
    /**
     * Check if field is searchable for given model.
     *
     * @param Builder $builder query builder
     * @param string  $field   field name
     *
     * @return bool
     */
    protected function isFieldSearchable(Builder $builder, $field)
    {
        $searchable = $this->_getSearchableAttributes($builder);
        $notSearchable =  $this->_getNotSearchableAttributes($builder);
        $field = preg_replace('#^!#', '', $field);
        return !in_array($field, $notSearchable) && !in_array('*', $notSearchable) && (in_array($field, $searchable) || in_array('*', $searchable));
    }
    /**
     * Applies constraints to query, allowing model to overwrite any of them.
     *
     * @param Builder      $builder     query builder
     * @param Constraint[] $constraints constraints
     * @param string       $mode        determines how constraints are applied ("or" or "and")
     */
    protected function applyConstraints(Builder $builder, array $constraints, $mode = Constraint::MODE_OR, array $query)
    {
        foreach ($constraints as $field => $constraint) {
            if (is_array($constraint)) {
                foreach ($constraint as $single_constraint) {
                    $this->applyConstraint($builder, $field, $single_constraint, $mode);
                }
            } else {
                $this->applyConstraint($builder, $field, $constraint, $mode);
            }
        }
    }
    /**
     * Calls constraint interceptor on model.
     *
     * @param Builder    $builder    query builder
     * @param string     $field      field on which constraint is applied
     * @param Constraint $constraint constraint
     *
     * @return bool true if constraint was intercepted by model's method
     */
    protected function callInterceptor(Builder $builder, $field, Constraint $constraint)
    {
        $model = $builder->getModel();
        $interceptor = sprintf('process%sFilter', str_replace(':', '_', Str::studly($field)));
        if (method_exists($model, $interceptor)) {
            if ($model->$interceptor($builder, $constraint)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Build Constraint objects from given filter values
     *
     * @param string []|string
     *
     * @return Constraint[]|Constraint
     */
    protected function buildConstraints($values)
    {
        if (is_array($values)) {
            $constraints = [];
            foreach ($values as $key => $value) {
                if ($key !== $this->getQueryModeParameterName()) {
                    $constraints[] = Constraint::make($value);
                }
            }
            return $constraints;
        } else {
            return Constraint::make($values);
        }
    }
    /**
     * Apply a single constraint - either directly or using model's interceptor
     *
     * @param Builder    $builder    query builder
     * @param string     $field      field name
     * @param Constraint $constraint constraint
     * @param string     $mode       determines how constraint is applied ("or" or "and")
     */
    protected function applyConstraint(Builder $builder, $field, $constraint, $mode = Constraint::MODE_OR)
    {
        // let model handle the constraint if it has the interceptor
        if (!$this->callInterceptor($builder, $field, $constraint)) {
            $constraint->apply($builder, $field, $mode);
        }
    }
    /**
     * Determines how constraints are applied ("or" or "and")
     *
     * @param array $query query parameters
     * @param string $default constraints
     *
     * @return mixed
     */
    protected function getQueryMode(array $query = [], $default = Constraint::MODE_OR)
    {
        return Arr::get($query, $this->getQueryModeParameterName(), $default);
    }
    /**
     * @param Builder $builder
     * @return array list of searchable attributes
     */
    protected function _getSearchableAttributes(Builder $builder) {
        if (method_exists($builder->getModel(), 'getSearchableAttributes')) {
            return $builder->getModel()->getSearchableAttributes();
        }
        if (property_exists($builder->getModel(), 'searchable')) {
            return $builder->getModel()->searchable;
        }
        throw new RuntimeException(sprintf('Model %s must either implement getSearchableAttributes() or have $searchable property set', get_class($builder->getModel())));
    }
    /**
     * Removes parameters that have special meaning to the trait or related sortable/withable traits
     *
     * @param array $query query
     * @return array query without special parameters that model should not be searched on
     */
    protected function filterNonSearchableParameters(array $query) {
        $nonSearchableParameterNames = [$this->getQueryModeParameterName()];
        if (property_exists($this, 'withParameterName')) {
            $nonSearchableParameterNames[] = $this->withParameterName;
        }
        if (property_exists($this, 'sortParameterName')) {
            $nonSearchableParameterNames[] = $this->sortParameterName;
        }
        return Arr::except($query, $nonSearchableParameterNames);
    }
    /**
     * @param Builder $builder
     * @return array|mixed
     */
    protected function _getNotSearchableAttributes(Builder $builder)
    {
        if (method_exists($builder->getModel(), 'getNotSearchableAttributes')) {
            return $builder->getModel()->getNotSearchableAttributes();
        } else if (property_exists($builder->getModel(), 'notSearchable')) {
            return $builder->getModel()->notSearchable;
        }
        return [];
    }
}
