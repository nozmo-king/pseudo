@extends('layout')

@section('title', 'Pseudo - Boards')

@section('content')
<div class="board-list">
    @foreach($boards as $board)
        <a href="{{ route('boards.show', $board->slug) }}" style="text-decoration: none; color: inherit;">
            <div class="board-card">
                <h2>/{{ $board->slug }}/</h2>
                <p>{{ $board->description }}</p>
                <p class="meta">{{ $board->threads_count }} threads</p>
            </div>
        </a>
    @endforeach
</div>

<div style="margin-top: 40px; padding: 20px; border: 1px solid #00ff00;">
    <h2>What is Pseudo?</h2>
    <p style="margin: 10px 0;">Pseudo is a proof-of-work based imageboard. Every post and thread requires solving cryptographic puzzles to submit.</p>
    
    <h3 style="margin-top: 20px;">Point System:</h3>
    <ul style="margin-left: 20px; margin-top: 10px;">
        <li>21e8 = 5 points</li>
        <li>21e80 = 15 points</li>
        <li>21e800 = 45 points</li>
        <li>21e8000 = 100 points</li>
        <li>21e80000 = 675 points</li>
        <li>21e800000 = 1,000 points</li>
        <li>21e8000000 = 5,000 points</li>
        <li>21e800000000 = 25,000 points</li>
    </ul>
    
    <p style="margin-top: 20px;">The more computational effort you invest, the more points your content earns!</p>
</div>
@endsection
