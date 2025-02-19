<?php
/**
 * Created by PhpStorm.
 * User: manu
 * Date: 11/03/20
 * Time: 12:14 PM
 */

namespace App\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
abstract class QueryFilters
{
    /**
     * The request object.
     *
     * @var Request
     */
    protected $request;
    /**
     * The builder instance.
     *
     * @var Builder
     */
    protected $builder;
    /**
     * Create a new QueryFilters instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /**
     * Apply the filters to the builder.
     *
     * @param  Builder $builder
     * @return Builder
     */
    public function apply(Builder $builder)
    {
        $this->builder = $builder;
        foreach ($this->filters() as $name => $value) {
            if (! method_exists($this, $name)) {
                continue;
            }
            if (!empty($value)) {
                $this->$name($value);
            } else {
                //this->$name();
            }
        }
        return $this->builder;
    }
    /**
     * Get all request filters data.
     *
     * @return array
     */
    public function filters()
    {
        return $this->request->all();
    }
}