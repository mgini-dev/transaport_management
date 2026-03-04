<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('completion_document_path')->nullable()->after('completed_at');
            $table->text('completion_comment')->nullable()->after('completion_document_path');
            $table->foreignId('completed_by')->nullable()->after('completion_comment')->constrained('users')->nullOnDelete();
        });

        // Add transportation stage to enum statuses.
        DB::statement("
            ALTER TABLE orders
            MODIFY status ENUM('created','processing','assigned','transportation','completed')
            NOT NULL DEFAULT 'created'
        ");

        DB::statement("
            ALTER TABLE order_status_histories
            MODIFY from_status ENUM('created','processing','assigned','transportation','completed') NULL
        ");
        DB::statement("
            ALTER TABLE order_status_histories
            MODIFY to_status ENUM('created','processing','assigned','transportation','completed') NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE orders
            MODIFY status ENUM('created','processing','assigned','completed')
            NOT NULL DEFAULT 'created'
        ");

        DB::statement("
            ALTER TABLE order_status_histories
            MODIFY from_status ENUM('created','processing','assigned','completed') NULL
        ");
        DB::statement("
            ALTER TABLE order_status_histories
            MODIFY to_status ENUM('created','processing','assigned','completed') NOT NULL
        ");

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('completed_by');
            $table->dropColumn(['completion_document_path', 'completion_comment']);
        });
    }
};

