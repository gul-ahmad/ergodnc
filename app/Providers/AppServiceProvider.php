<?php

namespace App\Providers;

use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //By Gul
        //To disable mass assignment which is bydefualt ON in laravel 
        //As Muhammad said said disable mass assignment becuase we should always do authentication
        //we never pass raw data to DB so we we are disableling mass assignment here On ALL Models
        Model::unguard();



        //Gul here
        //using custom polymorphic type here to aliase 
        //we aliase the name of Office Model to office here

        Relation::enforceMorphMap([
           'office' =>Office::class,
           'user'  =>User::class
        ]);
    }
}
