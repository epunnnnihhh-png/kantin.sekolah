<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('username')->unique()->nullable();
            $table->string('password');
            $table->string('role')->default('student');
            $table->string('school')->nullable();
            $table->integer('canteen_id')->nullable();
            $table->string('nisn')->nullable();
            $table->boolean('is_present')->default(false);
            $table->timestamp('attendance_date')->nullable();
            $table->string('class_name')->nullable(); // For students
            $table->integer('balance')->default(0); // For student wallet
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
