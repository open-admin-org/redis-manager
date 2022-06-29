<?php

namespace OpenAdmin\Admin\RedisManager\DataType;

use Illuminate\Support\Arr;
use OpenAdmin\Admin\Widgets\Table;

class Lists extends DataType
{
    /**
     * {@inheritdoc}
     */
    public function fetch(string $key)
    {
        return $this->getConnection()->lrange($key, 0, -1);
    }

    /**
     * {@inheritdoc}
     */

    /*
    public function update(array $params)
    {
        $key = Arr::get($params, 'key');

        if (Arr::has($params, 'push')) {
            $item = Arr::get($params, 'item');
            $command = $params['push'] == 'left' ? 'lpush' : 'rpush';

            $this->getConnection()->{$command}($key, $item);
        }

        if (Arr::has($params, '_editable')) {
            $value = Arr::get($params, 'value');
            $index = Arr::get($params, 'pk');

            $this->getConnection()->lset($key, $index, $value);
        }

        return redirect(route('redis-edit-key', [
            'conn' => request('conn'),
            'key'  => $key,
        ]));
    }
    */

    /**
     * {@inheritdoc}
     */
    public function update(array $params)
    {
        // ok a bit dirty but it works
        $key = Arr::get($params, 'key');
        $this->getConnection()->del($key);

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

        $this->getConnection()->rpush($key, $value);

        if ($ttl > 0) {
            $this->getConnection()->expire($key, $ttl);
        }

        return redirect(route('redis-edit-key', [
            'conn' => request('conn'),
            'key'  => $key,
        ]));
    }

    /**
     * Remove a member from list by index.
     *
     * @param array $params
     *
     * @return mixed
     */
    public function remove(array $params)
    {
        $key = Arr::get($params, 'key');
        $index = Arr::get($params, 'index');

        $lua = <<<'LUA'
redis.call('lset', KEYS[1], ARGV[1], '__DELETED__');
redis.call('lrem', KEYS[1], 1, '__DELETED__');
LUA;

        return $this->getConnection()->eval($lua, 1, $key, $index);
    }

    public function form()
    {
        $this->form->hidden('conn')->value($this->conn);
        $this->form->hidden('type')->value('list');
        $this->form->text('key');
        $this->form->number('ttl')->default(-1);
        $this->form->list('value')->sortable();

        /*
        $this->form->text('add_left');
        $this->form->text('add_right');
        $this->form->html(function ($form) {
            $list = $form->data['value'];
            $keys = array_keys($list);
            $data = array_map(function ($key, $list) {
                return ["key"=>$key,"value"=>$list];
            }, $keys, $list);

            $table = new Table(["key","value"], $data);
            return $table->render();
        });
        */
    }
}
