<?php

namespace OpenAdmin\Admin\RedisManager\DataType;

use Illuminate\Support\Arr;

class Strings extends DataType
{
    /**
     * {@inheritdoc}
     */
    public function fetch(string $key)
    {
        return $this->getConnection()->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $params)
    {
        return $this->store($params);
    }

    /**
     * {@inheritdoc}
     */
    public function store(array $params)
    {
        $key = Arr::get($params, 'key');
        $value = Arr::get($params, 'value');
        $ttl = Arr::get($params, 'ttl');

        $this->getConnection()->set($key, $value);

        if ($ttl > 0) {
            $this->getConnection()->expire($key, $ttl);
        }

        return redirect(route('redis-index', [
            'conn' => request()->conn,
        ]));
    }

    public function form()
    {
        $this->form->hidden('conn')->value($this->conn);
        $this->form->hidden('type')->value('string');
        $this->form->text('key');
        $this->form->text('value');
        $this->form->number('ttl')->default(-1);
    }
}
