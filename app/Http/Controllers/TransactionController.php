<?php

namespace App\Http\Controllers;

use App\Events\NewTransactionEvent;
use App\Models\Transaction;
use App\Models\Device;
use App\Rules\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Liste toutes les transactions
     */
    public function index()
    {
        $transactions = Transaction::with('device')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    public function transactionPending()
    {
        $transactions = Transaction::with('device')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Liste les transactions en attente
     */
    public function pending()
    {
        $transactions = Transaction::with('device')
            ->where('status', 'PENDING')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Création d'une nouvelle transaction
     * { amount: 1000, operator: "orange", phone: "6570000000", type: "DEPOSIT" }
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        // Validation des champs
        $validator = Validator::make($request->all(), [
            'phone' => ['required', new PhoneNumber],
            'operator' => 'required|in:orange,mtn',
            'amount' => 'required|numeric',
            'type' => 'required|in:DEPOSIT,WITHDRAW',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }
        DB::beginTransaction();
        try {
            // Choisir automatiquement le device selon l'opérateur
            $device = Device::where('operator', strtolower($request->operator))
                ->where('status', 'ACTIVE')
                ->first();

            // Création de la transaction
            $transaction = Transaction::create([
                'phone' => $request->phone,
                'operator' => $request->operator,
                'amount' => $request->amount,
                'type' => $request->type,
                'status' => 'PENDING',
                'sms_status' => 'PENDING',
                'device_id' => $device ?->id,
       ]);
        // 🚀 Broadcast en temps réel via Pusher
       // broadcast(new NewTransactionEvent($transaction))->toOthers();
        broadcast(new NewTransactionEvent($transaction));
        DB::commit();
        return response()->json([
            'success' => true,
            'data' => $transaction
        ], Response::HTTP_CREATED);
        } catch (\Exception $exception) {

            return response()->json([
                'success' => false,
                'errors' => $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }

    /**
     * Marquer une transaction comme PROCESSING
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function markProcessing($id, Request $request)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->status = 'PROCESSING';
        $transaction->device_id = $request->device_id ?? $transaction->device_id;
        $transaction->save();

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }
    public function updateStatus($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:SUCCESS,FAILED,PROCESSING',
            'sms_status' => 'required|in:SUCCESS,FAILED,PROCESSING,PENDING',
            'raw_sms' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }
        $transaction = Transaction::findOrFail($id);
        $transaction->status = $request->status;
        $transaction->sms_status = $request->sms_status;
        $transaction->save();

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }
    /**
     * Marquer une transaction comme COMPLETE (SUCCESS / FAILED)
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function completeTransaction($id, Request $request)
    {
        $transaction = Transaction::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:SUCCESS,FAILED',
            'raw_sms' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        $transaction->status = $request->status;
        $transaction->raw_sms = $request->raw_sms ?? $transaction->raw_sms;
        $transaction->processed_at = now();
        $transaction->save();

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }
}
