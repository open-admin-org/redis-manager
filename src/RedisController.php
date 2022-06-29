<?php

namespace OpenAdmin\Admin\RedisManager;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;
use OpenAdmin\Admin\Facades\Admin;
use OpenAdmin\Admin\Layout\Content;

class RedisController extends BaseController
{
    /**
     * Index page.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $manager = $this->manager();
            $vars = $manager->vars();

            $vars['keys'] = $manager->scan(
                request('pattern', '*'),
                request('count', 50)
            );

            $content->header('Redis manager');
            $content->description('Connections');
            $content->breadcrumb(['text' => 'Redis manager']);
            $content->body(view('open-admin-redis-manager::index', $vars));
        });
    }

    /**
     * Edit page.
     *
     * @param Request $request
     *
     * @return Content
     */
    public function edit(Request $request)
    {
        return Admin::content(function (Content $content) use ($request) {
            $manager = $this->manager();

            $this->data = $manager->fetch($request->get('key'));

            $vars = $manager->vars();
            $vars['form'] = $this->form($request);
            $vars['form_title'] = __('Edit');

            if (empty($this->data)) {
                $view = 'open-admin-redis-manager::edit.nil';
            } else {
                $view = 'open-admin-redis-manager::form';
            }

            $content->header('Redis manager');
            $content->description('Connections');
            $content->breadcrumb(
                ['text' => 'Redis manager', 'url' => route('redis-index', ['conn' => $vars['conn']])],
                ['text' => 'Edit']
            );
            $content->body(view($view, $vars));
        });
    }

    /**
     * Create page.
     *
     * @param Request $request
     *
     * @return Content
     */
    public function create(Request $request)
    {
        return Admin::content(function (Content $content) use ($request) {
            $manager = $this->manager();
            $vars = $manager->vars();
            $vars['form'] = $this->form($request);
            $vars['form_title'] = __('Create');

            $content->header('Redis manager');
            $content->description('Connections');
            $content->breadcrumb(
                ['text' => 'Redis manager', 'url' => route('redis-index', ['conn' => $vars['conn']])],
                ['text' => 'Create']
            );
            //$content->body(view($view, $vars));
            $content->body(view('open-admin-redis-manager::form', $vars));
        });
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function form(Request $request)
    {
        if (!empty($this->data)) {
            $type = $this->data['type'];
        } else {
            $type = $request->get('type');
        }

        if (!empty($type)) {
            $this->dataType = $this->manager()->{$type}();

            if (!empty($this->data)) {
                $this->dataType->setData($this->data);
            }

            return $this->dataType->getForm();
        }
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $type = $request->type;
        admin_toastr('Saved', 'success');

        return $this->manager()->{$type}()->store($request->all());
    }

    /**
     * @param Request $request
     *
     * @return int
     */
    public function destroy(Request $request)
    {
        return $this->manager()->del($request->get('key'));
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function fetch(Request $request)
    {
        return $this->manager()->fetch($request->get('key'));
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function remove(Request $request)
    {
        $type = $request->get('type');

        return $this->manager()->{$type}()->remove($request->all());
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request)
    {
        admin_toastr('Saved', 'success');

        return $this->manager()->update($request);
    }

    /**
     * Redis console interface.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function console(Request $request)
    {
        return Admin::content(function (Content $content) use ($request) {
            $connection = $request->get('conn', 'default');

            $manager = $this->manager();

            $vars = [
                'conn'        => $connection,
                'info'        => $manager->getInformation(),
                'connections' => $manager->getConnections(),
            ];

            $view = 'open-admin-redis-manager::console';

            $content->header('Redis manager');
            $content->description('Connections');
            $content->breadcrumb(
                ['text' => 'Redis manager', 'url' => route('redis-index', ['conn' => $connection])],
                ['text' => 'Console']
            );
            $content->body(view($view, $vars));
        });
    }

    /**
     * Execute a redis command.
     *
     * @param Request $request
     *
     * @return bool|string
     */
    public function execute(Request $request)
    {
        $command = $request->get('command');

        try {
            $result = $this->manager()->execute($command);
        } catch (\Exception $exception) {
            return $this->renderException($exception);
        }

        if (is_string($result) && Str::startsWith($result, ['ERR ', 'WRONGTYPE '])) {
            return $this->renderException(new \Exception($result));
        }

        return $this->getDumpedHtml($result);
    }

    /**
     * Render exception.
     *
     * @param \Exception $exception
     *
     * @return string
     */
    protected function renderException(\Exception $exception)
    {
        return sprintf(
            "<div class='callout callout-warning'><i class='icon-exclamation-triangle'></i>&nbsp;&nbsp;&nbsp;%s</div>",
            str_replace("\n", '<br />', $exception->getMessage())
        );
    }

    /**
     * Get html of dumped variable.
     *
     * @param mixed $var
     *
     * @return bool|string
     */
    protected function getDumpedHtml($var)
    {
        ob_start();

        dump($var);

        $content = ob_get_contents();

        ob_get_clean();

        return substr($content, strpos($content, '<pre '));
    }

    /**
     * Get the redis manager instance.
     *
     * @return RedisManager
     */
    protected function manager()
    {
        $conn = \request()->get('conn');

        return RedisManager::instance($conn);
    }
}
