<?php

namespace Liaosankai\Reinforce;

use Illuminate\Support\ServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Validator as Validator;

class ReinforceServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('liaosankai/reinforce');

        //include custom helpers
        foreach (glob($this->guessPackagePath() . '/helpers/*.php') as $helper) {
            require_once($helper);
        }
        //use custom validator
        Validator::resolver(function($translator, $data, $rules, $messages) {
            return new Checker($translator, $data, $rules, $messages);
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('reinforce');
    }

}
