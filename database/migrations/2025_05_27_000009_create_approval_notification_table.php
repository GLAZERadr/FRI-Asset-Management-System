<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // approval_request, approval_result
            $table->string('title');
            $table->text('message');
            $table->morphs('notifiable'); // user who will receive notification
            $table->string('related_model')->nullable(); // MaintenanceAsset
            $table->unsignedBigInteger('related_id')->nullable(); // maintenance asset id
            $table->string('action_url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Create approval_logs table for tracking
        Schema::create('approval_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maintenance_asset_id');
            $table->string('action'); // submitted, approved, rejected
            $table->string('performed_by'); // username or user_id
            $table->string('role'); // role of the person
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('maintenance_asset_id')->references('id')->on('maintenance_assets')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('approval_logs');
    }
};