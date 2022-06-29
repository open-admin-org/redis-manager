<?php

namespace OpenAdmin\Admin\RedisManager;

use OpenAdmin\Admin\Facades\Admin;

trait BootExtension
{
    public static function boot()
    {
        static::registerRoutes();

        Admin::extend('redis-manager', __CLASS__);
    }

    /**
     * Register routes for open-admin.
     *
     * @return void
     */
    protected static function registerRoutes()
    {
        parent::routes(function ($router) {
            /* @var \Illuminate\Routing\Router $router */
            $router->get('redis', 'OpenAdmin\Admin\RedisManager\RedisController@index')->name('redis-index');
            $router->delete('redis/key', 'OpenAdmin\Admin\RedisManager\RedisController@destroy')->name('redis-key-delete');
            $router->get('redis/fetch', 'OpenAdmin\Admin\RedisManager\RedisController@fetch')->name('redis-fetch-key');
            $router->get('redis/create', 'OpenAdmin\Admin\RedisManager\RedisController@create')->name('redis-create-key');
            $router->post('redis/store', 'OpenAdmin\Admin\RedisManager\RedisController@store')->name('redis-store-key');
            $router->get('redis/edit', 'OpenAdmin\Admin\RedisManager\RedisController@edit')->name('redis-edit-key');
            $router->post('redis/key', 'OpenAdmin\Admin\RedisManager\RedisController@update')->name('redis-update-key');
            $router->delete('redis/item', 'OpenAdmin\Admin\RedisManager\RedisController@remove')->name('redis-remove-item');

            $router->get('redis/console', 'OpenAdmin\Admin\RedisManager\RedisController@console')->name('redis-console');
            $router->post('redis/console', 'OpenAdmin\Admin\RedisManager\RedisController@execute')->name('redis-execute');
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function import()
    {
        parent::createMenu('Redis manager', 'redis', 'icon-database');

        parent::createPermission('Redis Manager', 'ext.redis-manager', 'redis*');
    }
}
