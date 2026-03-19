<?php

namespace App\Events;
// app/Events/TransactionUpdatedEvent.php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class TransactionUpdatedEvent implements ShouldBroadcast
{
    use SerializesModels;

    public $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction->fresh();
    }

    public function broadcastOn()
    {
        return new Channel('ussd-transactions');
    }

    public function broadcastAs()
    {
        return 'transaction.updated';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->transaction->id,
            'status' => $this->transaction->status,
            'raw_sms' => $this->transaction->raw_sms,
        ];
    }
}
