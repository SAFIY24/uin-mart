
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_order_fulfillments', function (Blueprint $table) {
            $table->dateTime('scheduled_at')->nullable();

            $table->enum('schedule_status', [
                'not_proposed',
                'proposed',
                'confirmed',
                'change_requested',
            ])->default('not_proposed');

            $table->text('schedule_note')->nullable();
            $table->text('schedule_change_reason')->nullable();
            $table->timestamp('schedule_confirmed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('seller_order_fulfillments', function (Blueprint $table) {
            $table->dropColumn([
                'scheduled_at',
                'schedule_status',
                'schedule_note',
                'schedule_change_reason',
                'schedule_confirmed_at',
            ]);
        });
    }
};
