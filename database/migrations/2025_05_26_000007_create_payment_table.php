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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // Payment details based on the form
            $table->string('no_invoice')->unique()->comment('Invoice number');
            $table->date('jatuh_tempo')->comment('Due date');
            $table->string('vendor')->comment('Vendor name');
            $table->decimal('total_tagihan', 15, 2)->comment('Total bill amount');
            
            // File upload for invoice
            $table->string('file_invoice')->nullable()->comment('Invoice file path');
            
            // Payment type dropdown
            $table->enum('tipe_pembayaran', ['transfer', 'cash', 'check'])
                  ->default('transfer')
                  ->comment('Payment type');

            $table->enum('jenis_pembayaran', ['setelah_perbaikan', 'sebelum_perbaikan'])
                  ->default('setelah_perbaikan');
            
            // Payment status
            $table->enum('status', ['belum_dibayar', 'sudah_dibayar', 'terlambat', 'dibatalkan'])
                  ->default('belum_dibayar')
                  ->comment('Payment status');
            
            // Additional tracking fields
            $table->datetime('tanggal_pembayaran')->nullable()->comment('Payment date');
            
            // User tracking
            $table->unsignedBigInteger('updated_by')->nullable();
            
            // Timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys for user tracking
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for better performance
            $table->index(['status', 'jatuh_tempo']);
            $table->index(['vendor']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};