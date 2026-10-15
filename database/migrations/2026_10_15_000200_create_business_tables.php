<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pesanan / request pembuatan website, invoice & pembayaran.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('client_name');
            $table->string('client_email');
            $table->string('client_phone', 30);
            $table->string('company_name')->nullable();
            $table->string('website_type', 50);
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->json('features')->nullable();
            $table->string('pages_estimate', 30)->nullable();
            $table->string('budget_range', 50)->nullable();
            $table->date('deadline')->nullable();
            $table->text('reference_urls')->nullable();
            $table->text('description');
            $table->string('color_preference', 100)->nullable();
            $table->boolean('has_domain')->default(false);
            $table->boolean('has_hosting')->default(false);
            $table->string('status', 30)->default('new')->index();
            $table->string('priority', 20)->default('normal');
            $table->unsignedBigInteger('quoted_price')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('referral_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source', 20)->default('web');
            $table->text('internal_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20)->default('comment'); // status | comment | progress | system
            $table->text('message');
            $table->boolean('is_public')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number', 40)->unique();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone', 30)->nullable();
            $table->text('client_address')->nullable();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->json('items');
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->string('status', 20)->default('unpaid')->index(); // draft|unpaid|partial|paid|cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->date('paid_at');
            $table->string('method', 50)->default('transfer');
            $table->string('reference')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['payments', 'invoices', 'order_activities', 'orders'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
