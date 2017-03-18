@extends('layouts.app')

@section('content')
    <div class="container">
        @if(!Auth::user())
            <div class="text-center">
                <a href="{{ $login_url }}" class="btn btn-lg btn-info">Connect to my Facebook profile</a>
            </div>
        @else
            <div class="row">
                <div class="col-md-12">
                    <h1 class="text-center text-capitalize page__title">{{ Auth::user()->name }}</h1>
                </div>
            </div>
            <div class="row flex">
                @foreach( Session::get('posts') as $post )
                    <div class="col-md-4 post">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <div class="post__meta">
                                    <div class="post__date">12.12.1223</div>
                                </div>
                            </div>
                            <div class="panel-body">
                                <img src="{{ $post['picture'] or '//placehold.it/400x300' }}" class="post__image">
                                {{ ( !empty($post['description']) ) ? mb_substr($post['description'], 0, 300) . '...' : 'no description' }}
                            </div>
                            <div class="panel-footer  text-center">
                                <span class="pull-left">
                                    <i class="fa fa-thumbs-o-up" aria-hidden="true"></i>
                                    {{ ( isset($post['likes']) ) ? count($post['likes']) : 0 }}
                                </span>
                                <span>
                                    <i class="fa fa-comments-o" aria-hidden="true"></i>
                                    {{ ( isset($post['comments']) ) ? count($post['comments']) : 0 }}
                                </span>
                                <span class="pull-right">
                                    <i class="fa fa-share" aria-hidden="true"></i>
                                    {{ ( isset($post['shares']) ) ? count($post['shares']) : 0 }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
    .flex {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: stretch;
    }
    .post {
        margin-bottom: 30px;
    }
    .panel-default {
        display: flex;
        height: 100%;
        flex-wrap: wrap;
        align-content: space-between;
    }
    .panel-heading,
    .panel-body,
    .panel-footer {
        width: 100%;
        overflow: hidden;
    }
    .page__title {
        margin-bottom: 30px;
    }
    .post__meta {
        text-align: right;
    }
    .post__image {
        float: left;
        margin-right: 15px;
        margin-bottom: 15px;
        width: 130px;
        height: auto;
    }
</style>
@endpush