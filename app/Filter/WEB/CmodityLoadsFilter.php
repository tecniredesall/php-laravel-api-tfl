<?php
namespace App\Filter\WEB;
use Illuminate\Support\Facades\DB;
use App\Filter\QueryFilters;

class CmodityLoadsFilter extends QueryFilters
{
    public function processed($value)
    {
        return $this->builder
            ->where("was_processed",intval($value));
    }
    public function error($value)
    {
        return $this->builder
            ->where("had_error",intval($value));
    }
    public function code($value)
    {
        return $this->builder
            ->where("error_code",intval($value));
    }
}