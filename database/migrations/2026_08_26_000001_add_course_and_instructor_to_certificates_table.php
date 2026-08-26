<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->string('course_name')
                ->default('Manual Handling and People Handling')
                ->after('company_id');
            $table->string('instructor_name')
                ->default('Santhosh Jacob')
                ->after('course_name');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['course_name', 'instructor_name']);
        });
    }
};
