@extends('layout')

@section('title', $chatroom->name)

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('chat.index') }}">← Back to Chatrooms</a>
</div>

<h1>{{ $chatroom->name }}</h1>
<p class="info">{{ $chatroom->description }}</p>

<div id="messages" style="border: 2px solid #00ff00; padding: 20px; margin: 20px 0; background: #000; min-height: 400px; max-height: 500px; overflow-y: auto;">
    @foreach($messages as $message)
        <div class="post" style="margin: 10px 0;">
            <strong>{{ $message->user->name ?? 'Anonymous' }}</strong>
            <span class="meta">({{ $message->created_at->format('H:i:s') }})</span>
            <span class="pow-indicator">{{ $message->points }} pts</span>
            <p style="margin-top: 5px;">{{ $message->message }}</p>
        </div>
    @endforeach
</div>

<form id="chat-form" data-pow data-pow-difficulty="21e8">
    @csrf
    <input type="text" id="message-input" name="message" placeholder="Type your message..." required maxlength="1000">
    <p class="info">Difficulty: 21e8 (5 points) - Your browser will mine proof-of-work when you send</p>
    <button type="submit">Send Message</button>
</form>
@endsection

@section('scripts')
<script>
    // Auto-scroll to bottom
    const messagesDiv = document.getElementById('messages');
    messagesDiv.scrollTop = messagesDiv.scrollHeight;

    // Handle chat form submission
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message-input');
    
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const message = messageInput.value;
        if (!message) return;
        
        const submitBtn = chatForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Mining...';
        
        try {
            const { nonce, hash } = await mineProofOfWork(message, '21e8');
            
            const response = await fetch('{{ route('chat.store', $chatroom->slug) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    message: message,
                    pow_nonce: nonce,
                    pow_difficulty: '21e8'
                })
            });
            
            if (response.ok) {
                messageInput.value = '';
                location.reload(); // Reload to show new message
            } else {
                alert('Failed to send message');
            }
        } catch (error) {
            alert('Error: ' + error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
</script>
@endsection
