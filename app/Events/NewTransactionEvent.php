<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;


use Illuminate\Support\Facades\Log;

class NewTransactionEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction->fresh();

        // 🔥 LOG création événement
        Log::info('NewTransactionEvent CREATED', [
            'transaction_id' => $this->transaction->id,
            'device_id' => $this->transaction->device_id,
            'operator' => $this->transaction->operator,
            'amount' => $this->transaction->amount,
        ]);
    }

    public function broadcastOn()
    {
        $channel = 'ussd-transactions';

        // 🔥 LOG canal utilisé
        Log::info('Broadcasting on channel', [
            'channel' => $channel,
            'transaction_id' => $this->transaction->id
        ]);

        return new Channel($channel);

        // Exemple canal par device
        // return new Channel('device-'.$this->transaction->device_id);
    }

    public function broadcastAs()
    {
        $eventName = 'new.transaction';

        // 🔥 LOG nom événement
        Log::info('Broadcast event name', [
            'event' => $eventName,
            'transaction_id' => $this->transaction->id
        ]);

        return $eventName;
    }

    public function broadcastWith()
    {
        $data = [
            'id' => $this->transaction->id,
            'phone' => $this->transaction->phone,
            'operator' => $this->transaction->operator,
            'amount' => $this->transaction->amount,
            'status' => $this->transaction->status,
            'type' => $this->transaction->type,
            'device_id' => $this->transaction->device_id,
            'sms_status' => $this->transaction->sms_status,
            'created_at' => $this->transaction->created_at,
            'reference' => $this->transaction->reference,
        ];

        // 🔥 LOG payload envoyé à Pusher
        Log::info('Broadcast payload', $data);

        return $data;
    }
}
