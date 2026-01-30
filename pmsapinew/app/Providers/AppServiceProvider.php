<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory as ValidatorFactory;
use Illuminate\Database\Connection;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */

     public function boot()
    {
        $this->app['validator']->extend('unique', function ($attribute, $value, $parameters, $validator) {
            $query = (new \Illuminate\Database\Query\Builder($this->app['db']->connection()))->from($parameters[0]);            
            return $query->where($attribute, $value)->count() === 0;
        });
    }
    
    public function register()
    {
        //
    }
}
