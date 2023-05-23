<?php

namespace Olekjs\Elasticsearch\Providers;

use Illuminate\Support\ServiceProvider;
use Olekjs\Elasticsearch\Client;

class ElasticsearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Client::class, Client::class);
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/services.php', 'services');
    }
}
