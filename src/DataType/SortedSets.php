<?php

namespace OpenAdmin\Admin\RedisManager\DataType;

use Illuminate\Support\Arr;

class SortedSets extends DataType
{
    /**
     * {@inheritdoc}
     */
    public function fetch(string $key)
    {
        return $this->getConnection()->zrange($key, 0, -1, ['WITHSCORES' => true]);
    }

    /*
    public function update(array $params)
    {
        $key = Arr::get($params, 'key');


        if (Arr::has($params, 'member')) {
            $member = Arr::get($params, 'member');
            $score = Arr::get($params, 'score');
            $this->getConnection()->zadd($key, [$member => $score]);
        }

        if (Arr::has($params, '_editable')) {
            $score = Arr::get($params, 'value');
            $member = Arr::get($params, 'pk');

            $this->getConnection()->zadd($key, [$member => $score]);
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
        $values = Arr::get($params, 'value');

        foreach ($values as $i => $value) {
            $this->getConnection()->zadd($key, [$value => $i]);
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
     * Remove a member from a sorted set.
     *
     * @param array $params
     *
     * @return int
     */
    public function remove(array $params)
    {
        $key = Arr::get($params, 'key');
        $member = Arr::get($params, 'member');

        return $this->getConnection()->zrem($key, $member);
    }

    public function prepareData($data)
    {
        $data['value'] = array_flip($data['value']);

        return $data;
    }

    public function form()
    {
        $this->form->hidden('conn')->value($this->conn);
        $this->form->hidden('type')->value('zset');
        $this->form->text('key');
        $this->form->number('ttl')->default(-1);
        $this->form->list('value')->sortable();
    }
}
