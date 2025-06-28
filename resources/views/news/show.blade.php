@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-2">{{ $post->title }}</h1>
        <p class="text-muted">{{ $post->publish_date ? $post->publish_date->format('H:i d/m/Y') : '' }}</p>
        <p class="lead">{{ $post->description }}</p>
        <div>{!! $post->content !!}</div>
    </div>
@endsection