<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('sent_to');
            $table->string('code_hash');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('last_sent_at')->nullable();
            // Use datetime for wider MySQL compatibility (no default required)
            $table->dateTime('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_otps');
    }
};
