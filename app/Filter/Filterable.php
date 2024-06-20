<?php
/**
 * Created by PhpStorm.
 * User: manu
 * Date: 11/03/20
 * Time: 12:14 PM
 */
namespace App\Filter;
use Illuminate\Database\Eloquent\Builder;
trait Filterable
{
    /**
     * Filter a result set.
     *
     * @param  Builder      $query
     * @param  QueryFilters $filters
     * @return Builder
     */
    public function scopeFilter($query, QueryFilters $filters)
    {
        return $filters->apply($query);
    }


}