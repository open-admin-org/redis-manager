<div class="row redis-manager">
    <style>
        .redis-manager .btn-card-tool .icon-minus,
        .redis-manager .btn-card-tool.collapsed .icon-plus{
            display:inline-block;
        }
        .redis-manager .btn-card-tool.collapsed .icon-minus,
        .redis-manager .btn-card-tool .icon-plus{
            display:none;
        }
        .redis-manager .nav-link{
            border-left:3px solid transparent;
        }
        .redis-manager .nav-link.active{
            border-left:3px solid var(--bs-primary);
            background: rgba(0,0,0,0.02);
        }
        .redis-manager .card-footer {
            margin-bottom:0;
        }
    </style>

    <div class="col-md-3">
        <div class="card with-border">
            <div class="card-header with-border">
                <h3 class="card-title">Connections</h3>

                <div class="card-tools">
                    <button type="button" class="btn btn-card-tool" data-bs-toggle="collapse" data-bs-target="#connections-body" >
                        <i class="icon-minus"></i>
                        <i class="icon-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body no-padding collapse show p-0" id="connections-body">
                <ul class="nav flex-column">
                    @foreach($connections as $name => $connection)
						@if(!empty($connection['host']))
                        <li class="nav-item">
                            <a class="nav-link @if($name == $conn)active @endif" href=" {{ route('redis-index', ['conn' => $name]) }}">
                                <i class="icon-database"></i> {{ $name }}  &nbsp;&nbsp;<small>[{{ $connection['host'].':'.$connection['port'] }}]</small>
                            </a>
                        </li>
						@endif
                    @endforeach
                </ul>
            </div>
            <!-- /.card-body -->
        </div>

        <div class="card card-default collapsed-card mt-4">
            <div class="card-header with-border">
                <h3 class="card-title">Connection <small><code>{{ $conn }}</code></small></h3>

                <div class="card-tools pull-right">
                    <button type="button" class="btn btn-card-tool" data-bs-toggle="collapse" data-bs-target="#connection-details" >
                        <i class="icon-minus"></i>
                        <i class="icon-plus"></i>
                    </button>
                </div>
            </div>

            <!-- /.card-header -->
            <div class="card-body collapse show p-0" id="connection-details">
                <div class="table-responsive">
                    <table class="table table-striped">
                        @foreach($connections[$conn] as $name => $value)
                            <tr>
                                <td width="160px">{{ $name }}</td>
                                <td><span class="label label-primary">{{ is_array($value) ? json_encode($value) : $value }}</span></td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <!-- /.table-responsive -->
            </div>
            <!-- /.card-body -->
        </div>

        <div class="card with-border mt-4">
            <div class="card-header with-border">
                <h3 class="card-title">Information</h3>

                <div class="card-tools pull-right">
                    <button type="button" class="btn btn-card-tool" data-bs-toggle="collapse" data-bs-target="#connection-info" >
                        <i class="icon-minus"></i>
                        <i class="icon-plus"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body collapse show p-0" id="connection-info">
                <div class="accordion accordion-flush" id="accordion">

                    @foreach($info as $part => $detail)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $part }}" aria-expanded="true" aria-controls="collapseOne">
                            {{ $part }}
                        </button>
                        </h2>
                        <div id="collapse{{ $part }}" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordion">
                            <div class="accordion-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped no-margin">
                                        @foreach($detail as $key => $value)
                                            <tr>
                                                <td>{{ $key }}</td>
                                                <td>
                                                    @if(is_array($value))
                                                        <pre><code>{{ json_encode($value, JSON_PRETTY_PRINT) }}</code></pre>
                                                    @else
                                                        <span class="label label-primary">{{ $value }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <!-- /.card-body -->
        </div>

    </div>

    <div class="col-md-9">

        @yield('page')

    </div>

</div>

