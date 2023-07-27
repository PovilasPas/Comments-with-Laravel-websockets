<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserVoted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $commentId;

    private $decrementsThis;

    private $decrementsOther;

    private $voteUp;

    private $senderId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($id, $dThis, $dOther, $voteUp, $senderId)
    {
        $this->commentId = $id;
        $this->decrementsThis = $dThis;
        $this->decrementsOther = $dOther;
        $this->voteUp = $voteUp;
        $this->senderId = $senderId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('public.comments');
    }

    public function broadcastAs()
    {
        return "UserVoted";
    }

    public function broadcastWith()
    {
        return [
            "commentId" => $this->commentId,
            "decrementsThis" => $this->decrementsThis,
            "decrementsOther" => $this->decrementsOther,
            "voteUp" => $this->voteUp,
            "senderId" => $this->senderId
        ];
    }
}
