<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pseudo - Proof of Work Imageboard')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Courier New', monospace; 
            background: #0a0a0a; 
            color: #00ff00; 
            padding: 20px;
            line-height: 1.6;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        header { 
            border: 2px solid #00ff00; 
            padding: 20px; 
            margin-bottom: 20px;
            background: #000;
        }
        header h1 { 
            color: #00ff00; 
            font-size: 2em;
            text-shadow: 0 0 10px #00ff00;
        }
        nav { margin-top: 15px; }
        nav a { 
            color: #00ff00; 
            text-decoration: none; 
            margin-right: 20px;
            padding: 5px 10px;
            border: 1px solid #00ff00;
            transition: all 0.3s;
        }
        nav a:hover { 
            background: #00ff00; 
            color: #000;
            box-shadow: 0 0 10px #00ff00;
        }
        .board-list { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .board-card {
            border: 2px solid #00ff00;
            padding: 20px;
            background: #000;
            transition: all 0.3s;
            cursor: pointer;
        }
        .board-card:hover {
            background: #001a00;
            box-shadow: 0 0 20px #00ff00;
            transform: translateY(-5px);
        }
        .board-card h2 { 
            color: #00ff00; 
            margin-bottom: 10px;
        }
        .thread {
            border: 1px solid #00ff00;
            padding: 15px;
            margin: 10px 0;
            background: #001100;
        }
        .thread:hover {
            background: #002200;
            box-shadow: 0 0 10px #00ff00;
        }
        .post {
            border-left: 3px solid #00ff00;
            padding: 10px;
            margin: 10px 0;
            background: #001100;
        }
        .pow-indicator {
            display: inline-block;
            padding: 2px 8px;
            background: #003300;
            border: 1px solid #00ff00;
            font-size: 0.8em;
            margin-left: 10px;
            cursor: help;
        }
        .pow-indicator:hover {
            background: #00ff00;
            color: #000;
        }
        form {
            border: 2px solid #00ff00;
            padding: 20px;
            margin: 20px 0;
            background: #000;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            background: #000;
            border: 1px solid #00ff00;
            color: #00ff00;
            font-family: 'Courier New', monospace;
        }
        button {
            padding: 10px 20px;
            background: #000;
            border: 2px solid #00ff00;
            color: #00ff00;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            transition: all 0.3s;
        }
        button:hover {
            background: #00ff00;
            color: #000;
            box-shadow: 0 0 10px #00ff00;
        }
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .pow-mining {
            padding: 10px;
            margin: 10px 0;
            background: #001100;
            border: 1px solid #00ff00;
            font-size: 0.9em;
        }
        a { color: #00ff00; }
        a:hover { text-shadow: 0 0 5px #00ff00; }
        .info { color: #00aa00; font-size: 0.9em; }
        .error { color: #ff0000; border: 1px solid #ff0000; padding: 10px; margin: 10px 0; }
        .success { color: #00ff00; border: 1px solid #00ff00; padding: 10px; margin: 10px 0; }
        .meta { color: #008800; font-size: 0.85em; }
        .pagination { margin: 20px 0; }
        .pagination a { margin: 0 5px; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>☢ PSEUDO ☢</h1>
            <p class="info">Proof of Work Imageboard - All posts require solving cryptographic puzzles</p>
            <nav>
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('boards.show', 'gen') }}">/gen/</a>
                <a href="{{ route('boards.show', 'tech') }}">/tech/</a>
                <a href="{{ route('boards.show', 'doodle') }}">/doodle/</a>
                <a href="{{ route('boards.show', 'meta') }}">/meta/</a>
                <a href="{{ route('chat.index') }}">Chat</a>
            </nav>
        </header>

        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        @if(session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        <main>
            @yield('content')
        </main>
    </div>

    <script>
        // Proof of Work Mining Function
        async function mineProofOfWork(data, difficulty) {
            let nonce = 0;
            while (true) {
                const hash = await sha256(data + nonce);
                if (hash.startsWith(difficulty)) {
                    return { nonce, hash };
                }
                nonce++;
                
                // Update UI every 1000 iterations
                if (nonce % 1000 === 0) {
                    const event = new CustomEvent('pow-progress', { detail: { nonce, hash } });
                    document.dispatchEvent(event);
                }
            }
        }

        // SHA-256 hash function
        async function sha256(message) {
            const msgBuffer = new TextEncoder().encode(message);
            const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            return hashHex;
        }

        // Auto-mine on form submission
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form[data-pow]');
            
            forms.forEach(form => {
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const difficulty = form.dataset.powDifficulty || '21e8';
                    
                    // Build data string based on form fields
                    let data = '';
                    const subjectField = form.querySelector('[name="subject"]');
                    const contentField = form.querySelector('[name="content"]');
                    const messageField = form.querySelector('[name="message"]');
                    
                    if (subjectField && contentField) {
                        // For threads: subject + delimiter + content
                        data = subjectField.value + '||' + contentField.value;
                    } else if (contentField) {
                        // For posts: content only
                        data = contentField.value;
                    } else if (messageField) {
                        // For chat: message only
                        data = messageField.value;
                    }
                    
                    if (!data) {
                        alert('Please enter content first');
                        return;
                    }
                    
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Mining...';
                    
                    // Add progress indicator
                    const progress = document.createElement('div');
                    progress.className = 'pow-mining';
                    progress.textContent = 'Mining proof of work...';
                    form.appendChild(progress);
                    
                    // Listen for progress
                    document.addEventListener('pow-progress', function(e) {
                        progress.textContent = `Mining... Nonce: ${e.detail.nonce} | Hash: ${e.detail.hash.substring(0, 16)}...`;
                    });
                    
                    try {
                        const { nonce, hash } = await mineProofOfWork(data, difficulty);
                        
                        // Add hidden fields with proof of work
                        const nonceInput = document.createElement('input');
                        nonceInput.type = 'hidden';
                        nonceInput.name = 'pow_nonce';
                        nonceInput.value = nonce;
                        form.appendChild(nonceInput);
                        
                        const difficultyInput = document.createElement('input');
                        difficultyInput.type = 'hidden';
                        difficultyInput.name = 'pow_difficulty';
                        difficultyInput.value = difficulty;
                        form.appendChild(difficultyInput);
                        
                        progress.textContent = `✓ Proof of work completed! Hash: ${hash}`;
                        
                        // Submit the form
                        setTimeout(() => form.submit(), 500);
                    } catch (error) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                        progress.textContent = 'Error: ' + error.message;
                    }
                });
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>
