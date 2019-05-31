<?php

namespace App\Events;

use App\Models\DungeonRoute;
use App\Models\KillZone;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KillZoneDeletedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var DungeonRoute $_dungeonroute */
    private $_dungeonroute;

    /** @var int $_id */
    private $_id;

    /**
     * Create a new event instance.
     *
     * @param $dungeonroute DungeonRoute
     * @param $killZone KillZone
     * @return void
     */
    public function __construct(DungeonRoute $dungeonroute, KillZone $killZone)
    {
        $this->_dungeonroute = $dungeonroute;
        $this->_id = $killZone->id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel(sprintf('route-edit.%s', $this->_dungeonroute->public_key));
    }

    public function broadcastAs()
    {
        return 'killzone-deleted';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->_id
        ];
    }
}
