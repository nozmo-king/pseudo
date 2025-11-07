@extends('layout')

@section('title', $post->title . ' - ' . $user->name . "'s Chorum")

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('chorum.index', $user->name) }}">← Back to {{ $user->name }}'s Chorum</a>
</div>

<div class="thread" style="background: #002200; border: 2px solid #00ff00;">
    <h1>{{ $post->title }}</h1>
    <div style="margin: 15px 0; white-space: pre-wrap;">{{ $post->content }}</div>
    <p class="meta">
        Posted by: {{ $user->name }} | 
        {{ $post->created_at->format('Y-m-d H:i:s') }} | 
        <span class="pow-indicator">{{ $post->points }} points</span>
    </p>
</div>
@endsection
