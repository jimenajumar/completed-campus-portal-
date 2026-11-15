
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_advisor_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('club_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('advisor_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('assigned_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->dateTime('assigned_at');
            $table->dateTime('ended_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_advisor_histories');
    }
};
