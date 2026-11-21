<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('triage_records', function (Blueprint $table) {
            // Optional patient context: age, sex, duration, conditions
            $table->json('patient')->nullable()->after('symptoms');
            // Extra AI output: next_step, possible_conditions, red_flags, self_care, confidence
            $table->json('details')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('triage_records', function (Blueprint $table) {
            $table->dropColumn(['patient', 'details']);
        });
    }
};
