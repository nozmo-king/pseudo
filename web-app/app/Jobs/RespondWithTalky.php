<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RespondWithTalky implements ShouldQueue
{
    use Queueable;

    public $chatroomId;

    /**
     * Create a new job instance.
     */
    public function __construct($chatroomId)
    {
        $this->chatroomId = $chatroomId;
        $this->delay(now()->addSeconds(rand(10, 30)));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get recent messages (last 5 minutes)
        $recentMessages = Message::where('chatroom_id', $this->chatroomId)
            ->where('created_at', '>', now()->subMinutes(5))
            ->where('anonymous_name', '!=', 'talky')
            ->whereNull('user_id')
            ->orWhere(function($q) {
                $q->where('chatroom_id', $this->chatroomId)
                  ->where('created_at', '>', now()->subMinutes(5))
                  ->whereNotNull('user_id');
            })
            ->count();

        // Only respond if chat is still quiet (less than 3 messages in last 5 mins)
        if ($recentMessages <= 2) {
            $responses = [
                "hey there! pretty quiet in here",
                "anyone around?",
                "what's everyone up to?",
                "slow day huh",
                "just checking in",
                "how's it going?",
                "quiet night",
                "anyone want to chat?",
                "hello hello",
                "hmm pretty dead in here",
            ];

            Message::create([
                'user_id' => null,
                'chatroom_id' => $this->chatroomId,
                'body' => $responses[array_rand($responses)],
                'anonymous_name' => 'talky',
            ]);
        }
    }
}
