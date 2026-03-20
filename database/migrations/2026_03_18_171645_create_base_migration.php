<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->string('phone')->comment('Numéro du client');
            $table->string('operator')->comment('Opérateur: orange, mtn, etc.');
            $table->decimal('amount', 12, 2)->comment('Montant de la transaction');

            $table->enum('type', ['DEPOSIT', 'WITHDRAW'])->comment('Type de transaction');
            $table->enum('status', ['PENDING', 'PROCESSING', 'SUCCESS', 'FAILED'])
                ->default('PENDING')
                ->comment('Statut de la transaction');
            $table->enum('sms_status', ['PENDING', 'PROCESSING', 'SUCCESS', 'FAILED'])
                ->default('PENDING')
                ->comment('Statut du sms de la transaction');

            $table->string('device_id')->nullable()->comment('SIM ou device utilisé pour la transaction');
            $table->text('raw_sms')->nullable()->comment('SMS reçu pour valider la transaction');

            $table->string('operator_code')->nullable()->comment('Code USSD ou raccourci spécifique à l’opérateur');
            $table->timestamp('processed_at')->nullable()->comment('Date/heure de traitement de la transaction');

            $table->timestamps();

            $table->index('status');
            $table->index('operator');
            $table->index(['status', 'operator']); // pour filtrer rapidement par statut et opérateur
        });
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nom du device / SIM, ex: SIM_1, SIM_2');
            $table->string('phone_number')->nullable()->comment('Numéro associé à la SIM, si applicable');
            $table->string('operator')->nullable()->comment('Opérateur associé, ex: Orange, MTN');
            $table->string('status')->default('ACTIVE')->comment('Statut du device: ACTIVE / INACTIVE');
            $table->timestamps();

            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('base_migration');
    }
};
