<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'sale_price',
        'cost_price',
        'unit',
        'image_path',
        'units_per_package',
        'stock_warehouse',
        'stock_packages',
        'stock_units',
        'is_active',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'units_per_package' => 'integer',
        'stock_warehouse' => 'integer',
        'stock_packages' => 'integer',
        'stock_units' => 'integer',
        'is_active' => 'boolean',
    ];

    public function barSales(): HasMany
    {
        return $this->hasMany(BarSale::class);
    }

    /**
     * Descuenta stock directamente de Bodega Central (nunca baja de 0)
     */
    public function deductWarehouseStock(int $quantity): void
    {
        $this->stock_warehouse = max(0, (int)$this->stock_warehouse - $quantity);
        $unitsPerPackage = max(1, (int) $this->units_per_package);
        $stock = max(0, (int) $this->stock_warehouse);
        $this->stock_packages = intdiv($stock, $unitsPerPackage);
        $this->stock_units = $stock % $unitsPerPackage;
        $this->save();
    }

    /**
     * Restaura stock a Bodega Central al anular una venta
     */
    public function restoreWarehouseStock(int $quantity): void
    {
        $this->stock_warehouse += $quantity;
        $unitsPerPackage = max(1, (int) $this->units_per_package);
        $stock = max(0, (int) $this->stock_warehouse);
        $this->stock_packages = intdiv($stock, $unitsPerPackage);
        $this->stock_units = $stock % $unitsPerPackage;
        $this->save();
    }

    public static function getDrinkSubcategories(): array
    {
        return [
            'Singanis',
            'Rones',
            'Fernet',
            'Whiskys',
            'Gins',
            'Tequilas',
            'Otros',
        ];
    }

    public function getDrinkTypeAttribute(): string
    {
        $name = strtolower($this->name);
        if (str_contains($name, 'singani')) return 'Singanis';
        if (str_contains($name, 'ron')) return 'Rones';
        if (str_contains($name, 'fernet')) return 'Fernet';
        if (str_contains($name, 'whisky')) return 'Whiskys';
        if (str_contains($name, 'gin') || str_contains($name, 'tanqueray') || str_contains($name, 'beefeater') || str_contains($name, 'dharma') || str_contains($name, 'ganesha') || str_contains($name, 'james cook')) return 'Gins';
        if (str_contains($name, 'tequila') || str_contains($name, 'jagermeister')) return 'Tequilas';
        return 'Otros';
    }

    /**
     * Mapeo de Licores a su Mixer/Soda incluido en el Combo según el flujo de Tío Chu:
     * - Singanis => Ginger Ale 2.0L (id 23)
     * - Fernet y Rones => Coca Cola 2.0L (id 24)
     * - Whiskys => Agua Vital 2.0L (id 25)
     * - Gins => Agua Tónica 1.0L (id 26)
     * - Tequilas / Jagermeister => Solos (sin mixer)
     */
    public static function getMixerMapping(): array
    {
        return [
            // Singanis => 1 Ginger Ale 2.0L (id 23)
            1 => ['mixer_id' => 23, 'ratio' => 1],
            2 => ['mixer_id' => 23, 'ratio' => 1],
            3 => ['mixer_id' => 23, 'ratio' => 1],
            // Fernet y Rones => 1 Coca Cola 2.0L (id 24)
            4 => ['mixer_id' => 24, 'ratio' => 1],
            5 => ['mixer_id' => 24, 'ratio' => 1],
            6 => ['mixer_id' => 24, 'ratio' => 1],
            7 => ['mixer_id' => 24, 'ratio' => 1],
            8 => ['mixer_id' => 24, 'ratio' => 1],
            // Whiskys => 1 Agua Vital 2.0L (id 25)
            9 => ['mixer_id' => 25, 'ratio' => 1],
            10 => ['mixer_id' => 25, 'ratio' => 1],
            11 => ['mixer_id' => 25, 'ratio' => 1],
            12 => ['mixer_id' => 25, 'ratio' => 1],
            // Gins => 2 Aguas Tónicas 1.0L (id 26) - ¡2 botellas por combo!
            16 => ['mixer_id' => 26, 'ratio' => 2],
            17 => ['mixer_id' => 26, 'ratio' => 2],
            18 => ['mixer_id' => 26, 'ratio' => 2],
            19 => ['mixer_id' => 26, 'ratio' => 2],
            20 => ['mixer_id' => 26, 'ratio' => 2],
            21 => ['mixer_id' => 26, 'ratio' => 2],
            22 => ['mixer_id' => 26, 'ratio' => 2],
        ];
    }

    /**
     * URL de la imagen del producto (prioriza image_path personalizado y luego el catálogo de /images/drinks/)
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!empty($this->image_path)) {
            if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
                return $this->image_path;
            }
            return asset(ltrim($this->image_path, '/'));
        }

        // Mapeo directo por ID según el seed oficial y los archivos en public/images/drinks/
        $map = [
            1  => 'CasaRealNegro.png',
            2  => 'SanPedro.png',
            3  => 'SanPedroMaracuya.png',
            4  => 'FernetBranca.png',
            5  => 'RonAbuelo.png',
            6  => 'HavanaEspecial.png',
            7  => 'Havana7Años.png',
            8  => 'HavanaReserva.png',
            9  => 'DobleBlack.png',
            10 => 'BlackLabel.png',
            11 => 'RedLabel.png',
            12 => 'JackDaniels.png',
            13 => 'JoseCuervo.png',
            14 => 'OlmecaChocolate.png',
            15 => 'Jagermeister.png',
            16 => 'Tanqueray.png',
            17 => 'TanqueraySevilla.png',
            18 => 'Tanqueray1Litro.png',
            19 => 'DharmaGin.png',
            20 => 'GaneshaGin.png',
            21 => 'JamesCook.png',
            22 => 'BeefeaterGin.png',
            23 => 'GingerAle.png',
            24 => 'CocaCola2L.png',
            25 => 'AguaVital2L.png',
            26 => 'AguaTonica1.0L.png',
            27 => 'Sprite2L.png',
            28 => 'AquiariusPera2L.png',
            29 => 'AguaVital600ml.png',
        ];

        if (isset($map[$this->id])) {
            return asset('images/drinks/' . $map[$this->id]);
        }

        // Fallback por coincidencia de nombre
        $n = mb_strtolower($this->name);
        if (str_contains($n, 'casa real')) return asset('images/drinks/CasaRealNegro.png');
        if (str_contains($n, 'maracuy')) return asset('images/drinks/SanPedroMaracuya.png');
        if (str_contains($n, 'san pedro')) return asset('images/drinks/SanPedro.png');
        if (str_contains($n, 'fernet')) return asset('images/drinks/FernetBranca.png');
        if (str_contains($n, 'abuelo')) return asset('images/drinks/RonAbuelo.png');
        if (str_contains($n, 'havana') && str_contains($n, '7')) return asset('images/drinks/Havana7Años.png');
        if (str_contains($n, 'havana') && str_contains($n, 'especial')) return asset('images/drinks/HavanaEspecial.png');
        if (str_contains($n, 'havana') && str_contains($n, 'reserva')) return asset('images/drinks/HavanaReserva.png');
        if (str_contains($n, 'double black') || str_contains($n, 'doble black')) return asset('images/drinks/DobleBlack.png');
        if (str_contains($n, 'black label')) return asset('images/drinks/BlackLabel.png');
        if (str_contains($n, 'red label')) return asset('images/drinks/RedLabel.png');
        if (str_contains($n, 'jack daniel')) return asset('images/drinks/JackDaniels.png');
        if (str_contains($n, 'cuervo')) return asset('images/drinks/JoseCuervo.png');
        if (str_contains($n, 'olmeca')) return asset('images/drinks/OlmecaChocolate.png');
        if (str_contains($n, 'jager')) return asset('images/drinks/Jagermeister.png');
        if (str_contains($n, 'sevilla')) return asset('images/drinks/TanqueraySevilla.png');
        if (str_contains($n, 'tanqueray') && (str_contains($n, '1.0') || str_contains($n, 'copas') || str_contains($n, 'litro'))) return asset('images/drinks/Tanqueray1Litro.png');
        if (str_contains($n, 'tanqueray')) return asset('images/drinks/Tanqueray.png');
        if (str_contains($n, 'dharma')) return asset('images/drinks/DharmaGin.png');
        if (str_contains($n, 'ganesha')) return asset('images/drinks/GaneshaGin.png');
        if (str_contains($n, 'james cook')) return asset('images/drinks/JamesCook.png');
        if (str_contains($n, 'beefeater')) return asset('images/drinks/BeefeaterGin.png');
        if (str_contains($n, 'ginger')) return asset('images/drinks/GingerAle.png');
        if (str_contains($n, 'coca')) return asset('images/drinks/CocaCola2L.png');
        if (str_contains($n, 'tónica') || str_contains($n, 'tonica')) return asset('images/drinks/AguaTonica1.0L.png');
        if (str_contains($n, 'sprite')) return asset('images/drinks/Sprite2L.png');
        if (str_contains($n, 'aquarius') || str_contains($n, 'aquiarius')) return asset('images/drinks/AquiariusPera2L.png');
        if (str_contains($n, '600')) return asset('images/drinks/AguaVital600ml.png');
        if (str_contains($n, 'vital')) return asset('images/drinks/AguaVital2L.png');

        return null;
    }
}

