<?php

namespace App\Events;

use App\Models\Targets\TT_TriggerEventoModel;
use App\Models\TT_ServizioModel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TargetEvent {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @property TT_ServizioModel $servizio
     */
    public $servizio;

    /**
     * @property TT_TriggerEventoModel $trigger
     */
    public $triggerEvento;

    /**
     * Create a new event instance.
     *
     * @param TT_ServizioModel $servizio
     * @param TT_TriggerEventoModel $trigger
     * @return void
     */
    public function __construct(TT_ServizioModel $servizio, TT_TriggerEventoModel $triggerEvento) {
        $this->servizio = $servizio;
        $this->triggerEvento = $triggerEvento;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {
        return new PrivateChannel('channel-name');
    }
}
