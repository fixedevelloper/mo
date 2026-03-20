<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use Pusher\Pusher;

class SimulateTransaction extends Command
{
    protected $signature = 'simulate:transaction {phone=657285050} {amount=100} {operator=orange}';
    protected $description = 'Simule l\'envoi d\'une transaction directement sur Pusher';

    public function handle()
    {
        $phone = $this->argument('phone');
        $amount = $this->argument('amount');
        $operator = $this->argument('operator');

        // Crée la transaction en base
        $transaction = Transaction::create([
            'phone' => $phone,
            'operator' => $operator,
            'amount' => $amount,
            'status' => 'PENDING',
            'sms_status' => 'PENDING',
            'type' => 'WITHDRAW',
            'device_id' => null,
        ]);

        // Configure Pusher
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ]
        );

        // Payload envoyé
        $data = [
            'id' => $transaction->id,
            'phone' => $transaction->phone,
            'operator' => $transaction->operator,
            'amount' => $transaction->amount,
            'status' => $transaction->status,
            'sms_status' => $transaction->sms_status,
            'created_at' => $transaction->created_at,
            'reference' => $transaction->reference,
            'type' => $transaction->type,
            'device_id' => $transaction->device_id,
        ];

        // Envoi direct à Pusher
        $pusher->trigger('ussd-transactions', 'new.transaction', $data);

        $this->info("Transaction simulée et envoyée sur Pusher : {$transaction->id}");
    }
}
