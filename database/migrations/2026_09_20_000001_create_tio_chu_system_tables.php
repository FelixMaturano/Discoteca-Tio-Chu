<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Productos / Bebidas (Inventario base)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('Licores'); // Licores, Cervezas, Mixers, Cigarros, Otros
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->decimal('cost_price', 10, 2)->nullable()->default(0);
            $table->string('unit')->default('Botella'); // Botella, 2.0L, 1.0L, 600ml, Lata, Cajetilla
            $table->unsignedInteger('units_per_package')->default(6); // Cajas de ~6 o configurable
            $table->integer('stock_warehouse')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Personal
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role')->default('Staff'); // Bartender, Staff/Seguridad, Mozo, Limpieza, DJ, Administrador
            $table->decimal('default_pay', 10, 2)->default(100.00);
            $table->string('assigned_bar')->nullable(); // Principal, Subte, General
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Noches de Evento / Sesiones de Apertura
        Schema::create('night_sessions', function (Blueprint $table) {
            $table->id();
            $table->date('session_date')->unique();
            $table->string('day_name'); // Viernes, Sábado, Domingo, etc.
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->decimal('pos_commission_rate', 6, 4)->default(0.0130); // 1.3% editable por Don Ludo
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Asistencia y Pago de Personal por Noche
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('night_session_id')->constrained('night_sessions')->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->decimal('pay_amount', 10, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        // 5. Ventas e Inventario por Barra
        Schema::create('bar_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('night_session_id')->constrained('night_sessions')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('bar_name'); // Barra Kelly (Principal), Barra Ariel (Subte), Tienda
            $table->integer('packages')->default(0); // Paquetes/cajas al inicio
            $table->integer('units')->default(0); // Unidades sueltas al inicio
            $table->integer('total_initial')->default(0); // Total botellas inicio
            $table->integer('saldo')->default(0); // Botellas sobrantes al cierre
            $table->integer('vendido')->default(0); // Total vendido en la noche
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0); // vendido * unit_price
            $table->timestamps();
        });

        // 6. Facturas Emitidas (Tarjetas y Efectivo)
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('night_session_id')->constrained('night_sessions')->onDelete('cascade');
            $table->unsignedInteger('correlative_num');
            $table->enum('payment_method', ['tarjeta', 'efectivo'])->default('efectivo');
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('commission_rate', 6, 4)->default(0.0130);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        // 7. Pagos QR (Yasta / Banco Unión & Yape / Banco BCP)
        Schema::create('qr_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('night_session_id')->constrained('night_sessions')->onDelete('cascade');
            $table->string('operator_name'); // Nombre del mozo/operador
            $table->enum('bank_app', ['YASTA', 'YAPE'])->default('YASTA');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('reference_code')->nullable();
            $table->boolean('is_confirmed')->default(true);
            $table->timestamps();
        });

        // 8. Gastos (Internos y Externos)
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('night_session_id')->constrained('night_sessions')->onDelete('cascade');
            $table->enum('category', ['interno', 'externo'])->default('interno');
            $table->string('description');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('receipt_no')->nullable();
            $table->timestamps();
        });

        // 9. Resumen de Cierre de Caja
        Schema::create('cash_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('night_session_id')->unique()->constrained('night_sessions')->onDelete('cascade');
            $table->decimal('total_card', 10, 2)->default(0);
            $table->decimal('total_card_commission', 10, 2)->default(0);
            $table->decimal('total_card_net', 10, 2)->default(0);
            $table->decimal('total_cash_invoices', 10, 2)->default(0);
            $table->decimal('total_qr_yasta', 10, 2)->default(0);
            $table->decimal('total_qr_yape', 10, 2)->default(0);
            $table->decimal('total_bar_sales', 10, 2)->default(0);
            $table->decimal('total_staff_paid', 10, 2)->default(0);
            $table->decimal('total_expenses', 10, 2)->default(0);
            $table->decimal('net_cash_closing', 10, 2)->default(0);
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_closings');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('qr_payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('bar_sales');
        Schema::dropIfExists('staff_attendances');
        Schema::dropIfExists('night_sessions');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('products');
    }
};
