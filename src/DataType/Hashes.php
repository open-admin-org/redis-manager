<?php

namespace OpenAdmin\Admin\RedisManager\DataType;

use Illuminate\Support\Arr;

class Hashes extends DataType
{
    /**
     * {@inheritdoc}
     */
    public function fetch(string $key)
    {
        return $this->getConnection()->hgetall($key);
    }

    /**
     * {@inheritdoc}
     */
    /*
    public function update(array $params)
    {
        $key = Arr::get($params, 'key');

        if (Arr::has($params, 'field')) {
            $field = Arr::get($params, 'field');
            $value = Arr::get($params, 'value');

            $this->getConnection()->hset($key, $field, $value);
        }

        if (Arr::has($params, '_editable')) {
            $value = Arr::get($params, 'value');
            $field = Arr::get($params, 'pk');

            $this->getConnection()->hset($key, $field, $value);
        }
    }
    */

    /**
     * {@inheritdoc}
     */
    public function store(array $params)
    {
        $key = Arr::get($params, 'key');
        $ttl = Arr::get($params, 'ttl');
        //$field = Arr::get($params, 'field');
        $value = Arr::get($params, 'value');

        $i = 0;
        foreach ($value['keys'] as $field_key) {
            $field_value = $value[$i];
            $this->getConnection()->hset($key, $field_key, $field_value);
            $i++;
        }

        if ($ttl > 0) {
            $this->getConnection()->expire($key, $ttl);
        }

        return redirect(route('redis-edit-key', [
            'conn' => request('conn'),
            'key'  => $key,
        ]));
    }

    /**
     * Remove a field from a hash.
     *
     * @param array $params
     *
     * @return int
     */
    public function remove(array $params)
    {
        $key = Arr::get($params, 'key');
        $field = Arr::get($params, 'field');

        return $this->getConnection()->hdel($key, [$field]);
    }

    public function form()
    {
        $this->form->hidden('conn')->value($this->conn);
        $this->form->hidden('type')->value('hash');
        $this->form->text('key');
        $this->form->number('ttl')->default(-1);
        $this->form->keyValue('value');
    }
}
