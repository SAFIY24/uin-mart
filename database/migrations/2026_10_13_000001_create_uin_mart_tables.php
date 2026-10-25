<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  // Laravel skeleton already provides users table; add username / role below.
  Schema::table('users',function(Blueprint $table){$table->string('username',100)->unique();$table->string('role',20)->default('customer');});
  Schema::create('categories',function(Blueprint $t){$t->id();$t->string('name');$t->timestamps();});
  Schema::create('products',function(Blueprint $t){$t->id();$t->foreignId('category_id')->constrained();$t->string('name');$t->text('description')->nullable();$t->decimal('price',14,2);$t->unsignedInteger('stock')->default(0);$t->string('image')->nullable();$t->string('status',20)->default('active');$t->timestamps();});
  Schema::create('orders',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained();$t->string('order_number')->unique();$t->string('customer_name');$t->string('phone',30);$t->text('address');$t->text('notes')->nullable();$t->decimal('total',14,2);$t->string('payment_method',30);$t->string('payment_status',30)->default('pending');$t->string('order_status',30)->default('pending');$t->timestamps();});
  Schema::create('order_items',function(Blueprint $t){$t->id();$t->foreignId('order_id')->constrained()->cascadeOnDelete();$t->foreignId('product_id')->nullable()->constrained()->nullOnDelete();$t->string('product_name');$t->decimal('price',14,2);$t->unsignedInteger('quantity');$t->decimal('subtotal',14,2);$t->timestamps();});
 }
 public function down():void {Schema::dropIfExists('order_items');Schema::dropIfExists('orders');Schema::dropIfExists('products');Schema::dropIfExists('categories');Schema::table('users',function(Blueprint $t){$t->dropColumn(['username','role']);});}
};
