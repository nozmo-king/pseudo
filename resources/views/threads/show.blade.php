@extends('layout')

@section('title', $thread->subject . ' - /' . $board->slug . '/')

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('boards.show', $board->slug) }}">← Back to /{{ $board->slug }}/</a>
</div>

<div class="thread" style="background: #002200; border: 2px solid #00ff00;">
    <h1>{{ $thread->subject }}</h1>
    <div style="margin: 15px 0;">
        {{ $thread->content }}
    </div>
    <p class="meta">
        Posted by: {{ $thread->user->name ?? 'Anonymous' }} | 
        {{ $thread->created_at->format('Y-m-d H:i:s') }} | 
        <span class="pow-indicator">{{ $thread->points }} points</span>
    </p>
</div>

<h2 style="margin-top: 30px;">Replies</h2>

@foreach($thread->posts as $post)
    <div class="post">
        <p>{{ $post->content }}</p>
        <p class="meta">
            By: {{ $post->user->name ?? 'Anonymous' }} | 
            {{ $post->created_at->diffForHumans() }} | 
            <span class="pow-indicator">{{ $post->points }} points</span>
        </p>
    </div>
@endforeach

@if(!$thread->is_locked)
    <form action="{{ route('posts.store', $thread->id) }}" method="POST" data-pow data-pow-difficulty="21e8" style="margin-top: 30px;">
        @csrf
        <h3>Reply to Thread</h3>
        <textarea name="content" placeholder="Your reply..." rows="6" required></textarea>
        <p class="info">Difficulty: 21e8 (5 points) - Your browser will mine proof-of-work when you submit</p>
        <button type="submit">Post Reply</button>
    </form>
@else
    <div class="error" style="margin-top: 30px;">
        This thread is locked. No more replies allowed.
    </div>
@endif
@endsection
