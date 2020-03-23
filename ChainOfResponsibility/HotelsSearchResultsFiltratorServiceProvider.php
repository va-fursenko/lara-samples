<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HotelsSearchResultsFiltratorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(\HotelsSearchResultsFiltrator::class, function ($app, $params) {
            if (count($params) < 2) {
                throw new \Exception(\HotelsSearchResultsFiltrator::class . " app::make() needs 2 parameters: (HotelSearchRQ)'request', (bool)'isLite'");
            }
            if (empty($params[0]) || !$params[0] instanceof \HotelSearchRQ) {
                throw new \Exception(\HotelsSearchResultsFiltrator::class . " app::make() needs " . \HotelSearchRQ::class . ' parameter');
            }
            return new \HotelsSearchResultsFiltrator($params[0], (bool)$params[1]);
        });
    }
}
