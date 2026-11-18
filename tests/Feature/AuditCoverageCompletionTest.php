<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\MediaAsset;
use App\Models\NightSession;
use App\Models\Product;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\StoreSale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AuditCoverageCompletionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $standardUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->standardUser = User::factory()->create(['role' => 'bartender']);
    }

    public function test_staff_payment_index_and_store_work_for_admin_and_standard_user(): void
    {
        $session = $this->createOpenSession();
        $staff = $this->createStaff();
        $staffToAdd = $this->createStaff();
        $attendance = StaffAttendance::create([
            'night_session_id' => $session->id,
            'staff_id' => $staff->id,
            'pay_amount' => 100,
            'is_paid' => false,
        ]);

        $this->actingAs($this->admin)
            ->get(route('staffPayments.index', ['session_id' => $session->id]))
            ->assertOk()
            ->assertViewIs('staff.payments')
            ->assertViewHas('attendances', fn ($attendances) => $attendances->contains('id', $attendance->id));

        $this->from(route('staffPayments.index'))
            ->actingAs($this->standardUser)
            ->post(route('staffPayments.store'), [
                'night_session_id' => $session->id,
                'staff_id' => $staffToAdd->id,
                'pay_amount' => 125,
            ])
            ->assertRedirect(route('staffPayments.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('staff_attendances', [
            'night_session_id' => $session->id,
            'staff_id' => $staffToAdd->id,
            'pay_amount' => 125,
        ]);
        $this->assertDatabaseCount('staff_attendances', 2);
    }

    public function test_staff_payment_update_validates_and_destroy_removes_attendance(): void
    {
        $session = $this->createOpenSession();
        $staff = $this->createStaff();
        $attendance = StaffAttendance::create([
            'night_session_id' => $session->id,
            'staff_id' => $staff->id,
            'pay_amount' => 100,
            'is_paid' => false,
        ]);

        $this->actingAs($this->standardUser)
            ->putJson(route('staffPayments.update'), [
                'night_session_id' => $session->id,
                'attendances' => [
                    $attendance->id => ['pay_amount' => -1],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors("attendances.{$attendance->id}.pay_amount");

        $this->actingAs($this->standardUser)
            ->putJson(route('staffPayments.update'), [
                'night_session_id' => $session->id,
                'attendances' => [
                    $attendance->id => ['pay_amount' => 125, 'is_paid' => true],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('totalPlanilla', 125)
            ->assertJsonPath('totalPagado', 125);

        $this->actingAs($this->admin)
            ->delete(route('staffPayments.destroy', $attendance))
            ->assertRedirect();

        $this->assertDatabaseMissing('staff_attendances', ['id' => $attendance->id]);
    }

    public function test_products_index_store_and_store_validation(): void
    {
        $this->actingAs($this->admin)
            ->get(route('products.index'))
            ->assertOk()
            ->assertViewIs('products.index');

        $this->from(route('products.index'))
            ->actingAs($this->standardUser)
            ->post(route('products.store'), [
                'name' => '',
                'category' => 'Licores',
            ])
            ->assertSessionHasErrors(['name', 'sale_price', 'unit']);

        $this->from(route('products.index'))
            ->actingAs($this->standardUser)
            ->post(route('products.store'), [
                'name' => 'Producto de prueba',
                'category' => 'Licores',
                'sale_price' => 120,
                'cost_price' => 80,
                'unit' => 'Botella',
                'units_per_package' => 6,
                'stock_warehouse' => 14,
            ])
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Producto de prueba',
            'stock_warehouse' => 14,
            'stock_packages' => 2,
            'stock_units' => 2,
        ]);
    }

    public function test_product_update_validates_and_destroy_deletes_product(): void
    {
        $product = $this->createProduct();
        $invalidPayload = [
            'name' => 'Producto actualizado',
            'category' => 'Licores',
            'sale_price' => 'invalid',
            'cost_price' => 50,
            'unit' => 'Botella',
            'units_per_package' => 6,
            'stock_warehouse' => 12,
            'is_active' => true,
        ];

        $this->actingAs($this->standardUser)
            ->putJson(route('products.update', $product), $invalidPayload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sale_price');

        $this->actingAs($this->standardUser)
            ->putJson(route('products.update', $product), [
                ...$invalidPayload,
                'sale_price' => 150,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('product.name', 'Producto actualizado');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Producto actualizado',
            'sale_price' => 150,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_invoice_index_store_validation_and_destroy(): void
    {
        $session = $this->createOpenSession();
        $existingInvoice = $this->createInvoice($session);

        $this->actingAs($this->admin)
            ->get(route('invoices.index', ['session_id' => $session->id]))
            ->assertOk()
            ->assertViewIs('invoices.index')
            ->assertViewHas('invoices', fn ($invoices) => $invoices->contains('id', $existingInvoice->id));

        $this->actingAs($this->standardUser)
            ->postJson(route('invoices.store'), [
                'night_session_id' => $session->id,
                'payment_method' => 'transferencia',
                'amount' => 0,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['payment_method', 'amount']);

        $createResponse = $this->actingAs($this->standardUser)
            ->postJson(route('invoices.store'), [
                'night_session_id' => $session->id,
                'payment_method' => 'tarjeta',
                'bar_name' => 'Tienda',
                'amount' => 100,
                'commission_rate' => 0.05,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $invoiceId = $createResponse->json('invoice.id');
        $this->assertDatabaseHas('invoices', [
            'id' => $invoiceId,
            'night_session_id' => $session->id,
            'payment_method' => 'tarjeta',
            'amount' => 100,
            'commission_amount' => 5,
            'net_amount' => 95,
        ]);

        $this->actingAs($this->standardUser)
            ->deleteJson(route('invoices.destroy', $invoiceId))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('id', $invoiceId);

        $this->assertDatabaseMissing('invoices', ['id' => $invoiceId]);
    }

    public function test_store_sale_validates_payment_split_and_records_order(): void
    {
        $session = $this->createOpenSession();
        $product = $this->createProduct();
        $payload = [
            'night_session_id' => $session->id,
            'orders' => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 50],
            ],
            'cash_amount' => 50,
            'qr_amount' => 0,
            'card_amount' => 0,
            'cobrante_name' => 'Cajero de prueba',
        ];

        $this->from(route('sales.index'))
            ->actingAs($this->admin)
            ->post(route('sales.storeSales.store'), $payload)
            ->assertSessionHasErrors('cash_amount');

        $payload['cash_amount'] = 100;
        $this->from(route('sales.index'))
            ->actingAs($this->standardUser)
            ->post(route('sales.storeSales.store'), $payload)
            ->assertRedirect(route('sales.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('store_sales', [
            'night_session_id' => $session->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => 100,
            'cash_amount' => 100,
        ]);
        $this->assertSame(8, $product->fresh()->stock_warehouse);
    }

    public function test_store_sale_destroy_restores_product_stock(): void
    {
        $session = $this->createOpenSession();
        $product = $this->createProduct(['stock_warehouse' => 8]);
        $storeSale = StoreSale::create([
            'night_session_id' => $session->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50,
            'total_price' => 100,
            'cash_amount' => 100,
            'qr_amount' => 0,
            'cobrante_name' => 'Cajero de prueba',
        ]);

        $this->actingAs($this->standardUser)
            ->delete(route('sales.storeSales.destroy', $storeSale))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('store_sales', ['id' => $storeSale->id]);
        $this->assertSame(10, $product->fresh()->stock_warehouse);
    }

    private function createOpenSession(): NightSession
    {
        return NightSession::create([
            'session_date' => now()->toDateString(),
            'day_name' => 'Viernes',
            'status' => 'open',
            'pos_commission_rate' => 0.05,
        ]);
    }

    private function createStaff(): Staff
    {
        return Staff::create([
            'name' => 'Personal de prueba',
            'role' => 'MESERO',
            'default_pay' => 100,
            'is_active' => true,
        ]);
    }

    private function createProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Producto de prueba',
            'category' => 'Licores',
            'sale_price' => 100,
            'cost_price' => 60,
            'unit' => 'Botella',
            'units_per_package' => 6,
            'stock_warehouse' => 10,
            'stock_packages' => 1,
            'stock_units' => 4,
            'is_active' => true,
        ], $overrides));
    }

    private function createInvoice(NightSession $session): Invoice
    {
        return Invoice::create([
            'night_session_id' => $session->id,
            'correlative_num' => 1,
            'payment_method' => 'efectivo',
            'bar_name' => 'Principal',
            'amount' => 50,
            'commission_rate' => 0,
            'commission_amount' => 0,
            'net_amount' => 50,
        ]);
    }

    public function test_media_index_renders_gallery_for_authenticated_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('media.index'))
            ->assertOk()
            ->assertViewIs('media.index')
            ->assertViewHas('mediaAssets')
            ->assertViewHas('counts');
    }

    public function test_media_store_and_destroy_manage_uploaded_gallery_image(): void
    {
        $mediaPath = null;

        try {
            $this->actingAs($this->standardUser)
                ->post(route('media.store'), [
                    'title' => 'Audit Coverage Banner',
                    'category' => 'general',
                    'image' => UploadedFile::fake()->createWithContent(
                        'banner.png',
                        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==')
                    ),
                ])
                ->assertRedirect(route('media.index', ['category' => 'general']))
                ->assertSessionHas('status');

            $media = MediaAsset::where('title', 'Audit Coverage Banner')->firstOrFail();
            $mediaPath = public_path($media->path);
            $this->assertFileExists($mediaPath);
            $this->assertDatabaseHas('media_assets', [
                'id' => $media->id,
                'category' => 'general',
                'is_system' => false,
            ]);

            $this->actingAs($this->admin)
                ->delete(route('media.destroy', $media))
                ->assertRedirect(route('media.index'))
                ->assertSessionHas('status');

            $this->assertDatabaseMissing('media_assets', ['id' => $media->id]);
            $this->assertFileDoesNotExist($mediaPath);
        } finally {
            if ($mediaPath && File::exists($mediaPath)) {
                File::delete($mediaPath);
            }
        }
    }

    public function test_product_model_helpers_cover_stock_catalog_mapping_and_image_url(): void
    {
        $product = $this->createProduct([
            'name' => 'Ganesha Gin',
            'image_path' => null,
        ]);

        $this->assertSame('Gins', $product->drink_type);
        $this->assertContains('Gins', Product::getDrinkSubcategories());
        $this->assertSame(
            ['mixer_id' => 26, 'ratio' => 2],
            Product::getMixerMapping()[20]
        );
        $this->assertStringEndsWith('/images/drinks/CasaRealNegro.png', $product->image_url);

        $product->deductWarehouseStock(12);
        $this->assertSame(0, $product->fresh()->stock_warehouse);
        $this->assertSame(0, $product->fresh()->stock_packages);
        $this->assertSame(0, $product->fresh()->stock_units);

        $product->restoreWarehouseStock(8);
        $this->assertSame(8, $product->fresh()->stock_warehouse);
        $this->assertSame(1, $product->fresh()->stock_packages);
        $this->assertSame(2, $product->fresh()->stock_units);
    }
}
