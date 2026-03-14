<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {

            $table->id();

            $table->string('disk')->default('dropbox'); // futuro: s3, local ecc
            $table->string('path'); // /books/file.pdf
            $table->string('name'); // file.pdf

            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();

            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('visibility', [
                'public',
                'private',
                'teacher',
                'admin'
            ])->default('private');

            $table->string('type')->nullable();
            // invoice, lesson, book, contract ecc

            $table->timestamps();

            $table->index('owner_id');
            $table->index('visibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
