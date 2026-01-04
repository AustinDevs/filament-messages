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
        Schema::create('fm_auto_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->boolean('is_active')->default(true);
            $table->string('trigger_type')->default('all'); // 'all', 'first_message', 'keywords'
            $table->json('keywords')->nullable(); // For keyword-based triggers
            $table->timestamp('start_at')->nullable(); // Schedule start time
            $table->timestamp('end_at')->nullable(); // Schedule end time
            $table->integer('reply_delay_seconds')->default(0); // Delay before sending auto-reply
            $table->boolean('reply_once_per_conversation')->default(false); // Only reply once per conversation
            $table->json('replied_conversations')->nullable(); // Track conversations already replied to
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_auto_replies');
    }
};
