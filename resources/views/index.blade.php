@extends('open-admin-redis-manager::layout')

@section('page')

<div class="card card-primary">
    <div class="card-header with-border">
        <h3 class="card-title">Connection: {{ $conn }}</h3> <small></small>

        <div class="card-tools pull-right">
            <button type="button" class="btn btn-card-tool" data-bs-toggle="collapse" data-bs-target="#redis-page" >
                <i class="icon-minus"></i>
                <i class="icon-plus"></i>
            </button>
        </div>
    </div>

    <form class="form-horizontal" method="get" action="{{ route('redis-index') }}" pjax-container>
        <div class="card-body collapse show" id="redis-page">
            <div class="row">
                <label for="preview" class="col-sm-2 control-label">Using prefix</label>
                <div class="col-sm-9">
                    <div class="form-control disabled" style="background:#F2F2F2">{{$prefix}}</div>
                </div>
            </div>
            <div class="row">
                <label for="inputPattern" class="col-sm-2 control-label">Pattern</label>

                <div class="col-sm-9">
                    <input class="form-control input-sm" name="pattern" id="inputPattern" value="{{ request('pattern', '*')}}">
                </div>
            </div>

            <div class="row">
                <div class="offset-sm-2 col-sm-9 d-flex">
                    <input type="hidden" name="conn" value="{{ $conn }}">

                    <button type="submit" class="btn btn-primary btn-sm me-auto"><i class="icon-search"></i>&nbsp;&nbsp;Search</button>

                    <div class="pull-right">
                        <a class="btn btn-danger btn-sm key-delete-multi"><i class="icon-trash"></i>&nbsp;&nbsp;Delete</a>

                        <a class="btn btn-warning btn-sm" href="{{ route('redis-console', ['conn' => $conn]) }}"><i class="icon-terminal"></i>&nbsp;&nbsp;Console</a>

                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-success"><i class="icon-plus"></i>&nbsp;&nbsp;Create</button>
                            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a class="dropdown-item" href="{{ route('redis-create-key', ['conn' => $conn, 'type' => 'string']) }}">string</a></li>
                                <li><a class="dropdown-item" href="{{ route('redis-create-key', ['conn' => $conn, 'type' => 'list']) }}">list</a></li>
                                <li><a class="dropdown-item" href="{{ route('redis-create-key', ['conn' => $conn, 'type' => 'hash']) }}">hash</a></li>
                                <li><a class="dropdown-item" href="{{ route('redis-create-key', ['conn' => $conn, 'type' => 'set']) }}">set</a></li>
                                <li><a class="dropdown-item" href="{{ route('redis-create-key', ['conn' => $conn, 'type' => 'zset']) }}">zset</a></li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- /.card-footer -->
    </form>

    <hr class="no-margin">

    <!-- /.card-header -->
    <div class="card-body table-responsive">

        <table class="table table-hover select-table">
            <thead>
            <tr>
                <th><input type="checkbox" id="grid-select-all" class="key-select-all form-check-input" onchange="admin.grid.select_all(event,this)"></th>
                <th>Key</th>
                <th>Type</th>
                <th>TTL(s)</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($keys as $index => $key)
                <tr data-key="{{$key['key']}}" class="row-{{$key['key']}}">
                    <td><input type="checkbox" class="key-select form-check-input row-selector grid-row-checkbox" data-key="{{ $key['key'] }}" data-id="{{ $key['key'] }}"></td>
                    <td><code>{{ $key['key'] }}</code></td>
                    <td><span class="badge bg-{{ \OpenAdmin\Admin\RedisManager\RedisManager::typeColor($key['type']) }}">{{ $key['type'] }}</span></td>
                    <td>{{ $key['ttl'] }}</td>
                    <td>
                        <a href="{{ route('redis-edit-key', ['key' => $key['key'], 'conn' => $conn]) }}"><i class="icon-edit"></i></a>
                        &nbsp;
                        <a class="key-delete" data-key="{{ $key['key'] }}"><i class="icon-trash"></i></a>
                    </td>
                </tr>
            @endforeach

            </tbody>
        </table>

        @if (empty($keys))
            <div class="text-center" style="padding: 20px;">
                Empty list or set.
            </div>
        @endif

    </div>
    <!-- /.card-body -->
</div>

<script>
    (function () {

        document.querySelectorAll('a.key-delete').forEach(el=>{

            el.addEventListener('click', function (e) {

                e.preventDefault();

                var key = e.currentTarget.dataset.key;

                Swal.fire({
                    title: "Delete key ["+key+"] ?",
                    type: "error",
                    showCancelButton: true
                }).then(function (result) {
                    if (result.value) {

                        let url = '{{ route('redis-key-delete') }}';
                        var data = {
                            _method:'delete',
                            conn: "{{ $conn }}",
                            key: key,
                        };
                        admin.ajax.post(url,data,function(data){
                            console.log(data);
                            admin.toastr.success('Key ' + key + ' deleted');
                            admin.ajax.reload();
                        });
                    }
                });
            });
        });

        document.querySelector('a.key-delete-multi').addEventListener('click', function (e) {

            var keys = selectedRows();
            if (keys.length == 0) {
                return;
            }

            Swal.fire({
                title: "Delete selected keys ?",
                type: "error",
                showCancelButton: true
            }).then(function (result) {
                if (result.value) {

                    let url = '{{ route('redis-key-delete') }}';
                    var data = {
                        _method:'delete',
                        conn: "{{ $conn }}",
                        key: keys,
                    };
                    admin.ajax.post(url,data,function(result){
                        admin.toastr.success(data.key.length + ' keys deleted');
                        admin.ajax.reload();
                    });
                }
            });
        });

        function selectedRows() {

            var selected = [];
            document.querySelectorAll('.key-select:checked').forEach(el => {
                selected.push(el.dataset.key);
            });

            return selected;
        };
    })();

</script>

@endsection