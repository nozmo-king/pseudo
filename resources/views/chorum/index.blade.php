@extends('layout')

@section('title', $user->name . "'s Chorum")

@section('content')
<h1>{{ $user->name }}'s Chorum</h1>
<p class="info">Personal blog powered by proof-of-work</p>
<p class="meta">Total Points: {{ $user->total_points }}</p>

<div style="margin-top: 30px;">
    @forelse($posts as $post)
        <div class="thread">
            <h2>
                <a href="{{ route('chorum.show', [$user->name, $post->id]) }}">
                    {{ $post->title }}
                </a>
                <span class="pow-indicator">{{ $post->points }} pts</span>
            </h2>
            <p>{{ Str::limit($post->content, 300) }}</p>
            <p class="meta">{{ $post->created_at->diffForHumans() }}</p>
        </div>
    @empty
        <p class="info">No blog posts yet.</p>
    @endforelse

    <div class="pagination">
        {{ $posts->links() }}
    </div>
</div>
@endsection
