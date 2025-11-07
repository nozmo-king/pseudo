@extends('layout')

@section('title', 'Chatrooms')

@section('content')
<h1>Chatrooms</h1>
<p class="info">Real-time chat powered by proof-of-work</p>

<div class="board-list">
    @foreach($chatrooms as $chatroom)
        <a href="{{ route('chat.show', $chatroom->slug) }}" style="text-decoration: none; color: inherit;">
            <div class="board-card">
                <h2>{{ $chatroom->name }}</h2>
                <p>{{ $chatroom->description }}</p>
                <p class="meta">{{ $chatroom->messages_count }} messages</p>
            </div>
        </a>
    @endforeach
</div>
@endsection
