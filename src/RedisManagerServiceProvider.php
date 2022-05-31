<?php

namespace OpenAdmin\Admin\RedisManager;

use Illuminate\Support\ServiceProvider;

class RedisManagerServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'open-admin-redis-manager');

        RedisManager::boot();
    }
}
