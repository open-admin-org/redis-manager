<?php

namespace OpenAdmin\Admin\RedisManager;

use Illuminate\Http\Request;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use OpenAdmin\Admin\Extension;
use OpenAdmin\Admin\RedisManager\DataType\DataType;
use OpenAdmin\Admin\RedisManager\DataType\Hashes;
use OpenAdmin\Admin\RedisManager\DataType\Lists;
use OpenAdmin\Admin\RedisManager\DataType\Sets;
use OpenAdmin\Admin\RedisManager\DataType\SortedSets;
use OpenAdmin\Admin\RedisManager\DataType\Strings;
use Predis\Collection\Iterator\Keyspace;

/**
 * Class RedisManager.
 */
class RedisManager extends Extension
{
    use BootExtension;

    /**
     * @var array
     */
    public static $typeColor = [
        'string' => 'primary',
        'list'   => 'info',
        'zset'   => 'danger',
        'hash'   => 'warning',
        'set'    => 'success',
    ];

    /**
     * @var array
     */
    protected $dataTyps = [
        'string' => Strings::class,
        'hash'   => Hashes::class,
        'set'    => Sets::class,
        'zset'   => SortedSets::class,
        'list'   => Lists::class,
    ];

    /**
     * @var RedisManager
     */
    protected static $instance;

    /**
     * @var string
     */
    protected $connection;

    /**
     * @var string
     */
    protected $connection_name;

    /**
     * Get instance of redis manager.
     *
     * @param string $connection
     *
     * @return RedisManager
     */
    public static function instance($connection = 'default')
    {
        if (!static::$instance instanceof self) {
            static::$instance = new static($connection);
        }

        return static::$instance;
    }

    /**
     * RedisManager constructor.
     *
     * @param string $connection
     */
    public function __construct()
    {
        $this->connection = request()->get('conn', 'default');
        $this->prefix = config('database.redis.options.prefix');
    }

    public function vars()
    {
        return [
            'conn'        => $this->connection,
            'prefix'      => $this->prefix,
            'info'        => $this->getInformation(),
            'connections' => $this->getConnections(),
        ];
    }

    /**
     * @return Lists
     */
    public function list()
    {
        return new Lists($this->getConnection());
    }

    /**
     * @return Strings
     */
    public function string()
    {
        return new Strings($this->getConnection());
    }

    /**
     * @return Hashes
     */
    public function hash()
    {
        return new Hashes($this->getConnection());
    }

    /**
     * @return Sets
     */
    public function set()
    {
        return new Sets($this->getConnection());
    }

    /**
     * @return SortedSets
     */
    public function zset()
    {
        return new SortedSets($this->getConnection());
    }

    /**
     * Get connection collections.
     *
     * @return Collection
     */
    public function getConnections()
    {
        return collect(config('database.redis'))->filter(function ($conn) {
            return is_array($conn);
        });
    }

    /**
     * Get a registered connection instance.
     *
     * @param string $connection
     *
     * @return Connection
     */
    public function getConnection($connection = null)
    {
        if ($connection) {
            $this->connection = $connection;
        }

        return Redis::connection($this->connection);
    }

    /**
     * Get information of redis instance.
     *
     * @return array
     */
    public function getInformation()
    {
        return $this->getConnection()->info();
    }

    /**
     * Get key without pref.
     *
     * @return string
     */
    public function keyNoPrefix($key)
    {
        return Str::replace($this->prefix, '', $key);
    }

    /**
     * Scan keys in redis by giving pattern.
     *
     * @param string $pattern
     * @param int    $count
     *
     * @return array|\Predis\Pipeline\Pipeline
     */
    public function scan($pattern = '*', $count = 100)
    {
        $client = $this->getConnection();
        $keys = [];
        $pattern = $this->prefix.$pattern;
        foreach (new Keyspace($client->client(), $pattern) as $key) {
            $key = $this->keyNoPrefix($key);
            $type = (string) $client->type($key);
            $ttl = $client->ttl($key);
            $keys[] = compact('key', 'type', 'ttl');
        }

        return $keys;
    }

    /**
     * Fetch value of a giving key.
     *
     * @param string $key
     *
     * @return array
     */
    public function fetch($key)
    {
        if (!$this->getConnection()->exists($key)) {
            return [];
        }

        $type = $this->getConnection()->type($key)->__toString();

        /** @var DataType $class */
        $class = $this->{$type}();
        $value = $class->fetch($key);
        $ttl = $class->ttl($key);

        return compact('key', 'value', 'ttl', 'type');
    }

    /**
     * Update a specified key.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function update(Request $request)
    {
        $key = $request->get('key');
        $type = $request->get('type');

        /** @var DataType $class */
        $class = $this->{$type}();

        $res = $class->update($request->all());

        $class->setTtl($key, $request->get('ttl'));

        return $res;
    }

    /**
     * Remove the specified key.
     *
     * @param string $key
     *
     * @return int
     */
    public function del($key)
    {
        if (is_string($key)) {
            $key = [$key];
        }

        return $this->getConnection()->del($key);
    }

    /**
     * 运行redis命令.
     *
     * @param string $command
     *
     * @return mixed
     */
    public function execute($command)
    {
        $command = explode(' ', $command);

        return $this->getConnection()->executeRaw($command);
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    public static function typeColor($type)
    {
        return Arr::get(static::$typeColor, $type, 'secondary');
    }
}
