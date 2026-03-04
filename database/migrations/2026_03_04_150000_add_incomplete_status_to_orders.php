<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE orders
            MODIFY status ENUM('created','processing','assigned','transportation','incomplete','completed')
            NOT NULL DEFAULT 'created'
        ");

        DB::statement("
            ALTER TABLE order_status_histories
            MODIFY from_status ENUM('created','processing','assigned','transportation','incomplete','completed') NULL
        ");
        DB::statement("
            ALTER TABLE order_status_histories
            MODIFY to_status ENUM('created','processing','assigned','transportation','incomplete','completed') NOT NULL
        ");
    }

    public function down(): void
    {
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
};
