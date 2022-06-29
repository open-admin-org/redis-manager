<?php

namespace OpenAdmin\Admin\RedisManager\DataType;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Arr;
use OpenAdmin\Admin\Widgets\Form;

abstract class DataType
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var array
     */
    public $data;

    /**
     * DataType constructor.
     *
     * @param $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->conn = $connection->getName();
        $this->form = new Form();
        $this->form->action(route('redis-store-key'));
    }

    public function getForm()
    {
        $this->form();
        if (!empty($this->data)) {
            //$this->form->attribute("method", "put");
            foreach ($this->form->fields() as $field) {
                if ($field->getId() == 'key') {
                    $field->readonly();
                }
            }
        }

        return $this->form->render();
    }

    /**
     * Get redis connection.
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    abstract public function fetch(string $key);

    /**
     * @param array $params
     *
     * @return mixed
     */
    abstract public function store(array $params);

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function update(array $params)
    {
        // ok a bit dirty but it works :-)
        $key = Arr::get($params, 'key');
        $this->getConnection()->del($key);

        return $this->store($params);
    }

    /**
     * @param none
     *
     * @return form
     */
    public function form()
    {
    }

    /**
     * Returns the remaining time to live of a key that has a timeout.
     *
     * @param string $key
     *
     * @return int
     */
    public function ttl($key)
    {
        return $this->getConnection()->ttl($key);
    }

    /**
     * Set a timeout on key.
     *
     * @param string $key
     * @param int    $expire
     *
     * @return void
     */
    public function setTtl($key, $expire)
    {
        if (is_null($expire)) {
            return;
        }

        $expire = (int) $expire;

        if ($expire > 0) {
            $this->getConnection()->expire($key, $expire);
        } else {
            $this->getConnection()->persist($key);
        }
    }

    public function prepareData($data)
    {
        return $data;
    }

    /**
     * Set form data.
     *
     * @param array $data
     *
     * @return void
     */
    public function setData($data = false)
    {
        if ($data) {
            $this->data = $this->prepareData($data);
            $this->form->fill($this->data);
        }

        $this->form->action(route('redis-update-key'));
        $this->form->attribute('method', 'post');

        return $this;
    }
}
