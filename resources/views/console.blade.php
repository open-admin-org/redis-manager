@extends('open-admin-redis-manager::layout')

@section('page')


<div class="card card-primary">
    <div class="card-header with-border">
        <h3 class="card-title">Redis Console</h3> <small></small>
    </div>
    <div class="card-body chat" id="console-card" style="height:calc(100vh - 280px);"></div>
    <div class="card-footer with-border">
        <div class="input-group">
            <input class="form-control input-lg" id="console-query" placeholder="Type command">
            <div class="btn-group">
                <button type="button" class="btn btn-primary btn-lg" id="console-send"><i class="icon-paper-plane"></i></button>
                <button type="button" class="btn btn-warning btn-lg" id="console-clear"><i class="icon-trash"></i></button>
            </div>
        </div>
    </div>
</div>

<script>
     (function () {

        var storageKey = function () {
            return 'redis-history'
        };

        function History () {
            this.index = this.count() - 1;
        }

        History.prototype.store = function () {
            var history = localStorage.getItem(storageKey());
            if (!history) {
                history = [];
            } else {
                history = JSON.parse(history);
            }
            return history;
        };

        History.prototype.push = function (record) {
            var history = this.store();
            history.push(record);
            localStorage.setItem(storageKey(), JSON.stringify(history));

            this.index = this.count() - 1;
        };

        History.prototype.count = function () {
            return this.store().length;
        };

        History.prototype.up = function () {
            if (this.index > 0) {
                this.index--;
            }

            return this.store()[this.index];
        };

        History.prototype.down = function () {
            if (this.index < this.count() - 1) {
                this.index++;
            }

            return this.store()[this.index];
        };

        History.prototype.clear = function () {
            localStorage.removeItem(storageKey());
        };

        var history = new History;
        var input = document.querySelector('#console-query');

        var send = function () {

            var url = '{{ route('redis-execute', ['conn' => $conn]) }}';
            var data = {
                conn: '{{ $conn }}',
                command: input.value,
            };
            admin.ajax.post(url,data,function(response){

                var data = response.data;
                history.push(input.value);
                var card = document.querySelector('#console-card');
                //var connection = document.querySelector('#connections').value;
                var connection = 'unknown';
                card.innerHTML += '<div class="item"><small class="badge bg-secondary">'+connection+' - '+input.value+"<\/small><\/div>";
                card.innerHTML += '<div class="item">'+data+"<\/div>";
                input.value = '';
            });
        };

        input.addEventListener('keyup', function (e) {
            if (e.keyCode == 13 && input.value) {
                send();
            }
            if (e.keyCode == 38) {
                input.value = history.up();
            }
            if (e.keyCode == 40) {
                input.value = history.down();
            }
        });

        document.querySelector('#console-send').addEventListener("click",function () {
            send();
        });

        document.querySelector('#console-clear').addEventListener("click",function () {
            document.querySelector('#console-card').innerHTML = '';
        });

    })();

</script>

@endsection