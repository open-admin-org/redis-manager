@extends('open-admin-redis-manager::layout')

@section('page')
<style>
    .form-no-bottom-padding .form{
        padding-bottom:0;
    }
</style>
<div class="card card-primary form-no-bottom-padding">
    <div class="card-header with-border">
        <h3 class="card-title">{{$form_title}}</h3><small style="padding:10px;line-height:2.3rem;"> Note: Please don't use this tool to update frequently using lists / sets </small>
    </div>

    {!!$form!!}

</div>
<!-- /.card-body -->

@endsection