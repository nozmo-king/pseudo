@extends('layout')

@section('title', '/'. $board->slug . '/ - ' . $board->name)

@section('content')
<h1>/{{ $board->slug }}/ - {{ $board->name }}</h1>
<p class="info">{{ $board->description }}</p>

<form action="{{ route('threads.store', $board->slug) }}" method="POST" data-pow data-pow-difficulty="21e8" style="margin-top: 20px;">
    @csrf
    <h3>Create New Thread</h3>
    <input type="text" name="subject" placeholder="Subject" required maxlength="255">
    <textarea name="content" placeholder="Content" rows="6" required></textarea>
    <p class="info">Difficulty: 21e8 (5 points) - Your browser will mine proof-of-work when you submit</p>
    <button type="submit">Create Thread</button>
</form>

<div style="margin-top: 40px;">
    <h2>Threads</h2>
    @forelse($threads as $thread)
        <div class="thread">
            <h3>
                <a href="{{ route('threads.show', [$board->slug, $thread->id]) }}">
                    {{ $thread->subject }}
                </a>
                <span class="pow-indicator" title="Points earned from proof-of-work">{{ $thread->points }} pts</span>
                @if($thread->is_pinned)
                    <span style="color: #ffaa00;">[PINNED]</span>
                @endif
                @if($thread->is_locked)
                    <span style="color: #ff0000;">[LOCKED]</span>
                @endif
            </h3>
            <p>{{ Str::limit($thread->content, 200) }}</p>
            <p class="meta">
                By: {{ $thread->user->name ?? 'Anonymous' }} | 
                {{ $thread->created_at->diffForHumans() }} | 
                {{ $thread->posts_count }} replies
            </p>
        </div>
    @empty
        <p class="info">No threads yet. Be the first to create one!</p>
    @endforelse

    <div class="pagination">
        {{ $threads->links() }}
    </div>
</div>
@endsection
