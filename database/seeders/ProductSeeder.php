<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Licores principales (22 productos)
            ['id' => 1,  'name' => 'Singani Casa Real Negro',      'category' => 'Licores', 'sale_price' => 240.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 2,  'name' => 'Singani San Pedro',            'category' => 'Licores', 'sale_price' => 230.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 3,  'name' => 'Singani San Pedro Maracuyá',   'category' => 'Licores', 'sale_price' => 240.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 4,  'name' => 'Fernet Branca',                'category' => 'Licores', 'sale_price' => 250.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 5,  'name' => 'Ron Abuelo',                  'category' => 'Licores', 'sale_price' => 280.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 6,  'name' => 'Ron Havana Especial',          'category' => 'Licores', 'sale_price' => 280.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 7,  'name' => 'Ron Havana 7 Años',            'category' => 'Licores', 'sale_price' => 410.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 8,  'name' => 'Ron Havana Reserva',           'category' => 'Licores', 'sale_price' => 300.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 9,  'name' => 'Whisky Double Black',          'category' => 'Licores', 'sale_price' => 1100.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 10, 'name' => 'Whisky Black Label',           'category' => 'Licores', 'sale_price' => 930.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 11, 'name' => 'Whisky Red Label',             'category' => 'Licores', 'sale_price' => 470.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 12, 'name' => 'Whisky Jack Daniels',          'category' => 'Licores', 'sale_price' => 900.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 13, 'name' => 'Tequila Jose Cuervo',          'category' => 'Licores', 'sale_price' => 400.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 14, 'name' => 'Tequila Olmeca Chocolate',     'category' => 'Licores', 'sale_price' => 400.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 15, 'name' => 'Jagermeister 750 ml',          'category' => 'Licores', 'sale_price' => 400.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 16, 'name' => 'Tanqueray 750 ml',             'category' => 'Licores', 'sale_price' => 570.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 17, 'name' => 'Tanqueray Sevilla 750 ml',     'category' => 'Licores', 'sale_price' => 650.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 18, 'name' => 'Tanqueray 1.0L p/Copas',       'category' => 'Licores', 'sale_price' => 680.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 19, 'name' => 'Dharma Gin',                   'category' => 'Licores', 'sale_price' => 420.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 20, 'name' => 'Ganesha Gin',                  'category' => 'Licores', 'sale_price' => 300.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 21, 'name' => 'James Cook',                   'category' => 'Licores', 'sale_price' => 300.00, 'unit' => 'Botella', 'units_per_package' => 6],
            ['id' => 22, 'name' => 'Beefeater Gin',                'category' => 'Licores', 'sale_price' => 650.00, 'unit' => 'Botella', 'units_per_package' => 6],

            // Bebidas Secundarias / Mixers (Extras - 7 productos)
            ['id' => 23, 'name' => 'Ginger Ale 2.0L',              'category' => 'Mixers',  'sale_price' => 25.00,  'unit' => '2.0L',   'units_per_package' => 6],
            ['id' => 24, 'name' => 'Coca Cola 2.0L',               'category' => 'Mixers',  'sale_price' => 25.00,  'unit' => '2.0L',   'units_per_package' => 6],
            ['id' => 25, 'name' => 'Agua Vital 2.0L',              'category' => 'Mixers',  'sale_price' => 15.00,  'unit' => '2.0L',   'units_per_package' => 6],
            ['id' => 26, 'name' => 'Agua Tónica 1.0L',             'category' => 'Mixers',  'sale_price' => 20.00,  'unit' => '1.0L',   'units_per_package' => 6],
            ['id' => 27, 'name' => 'Sprite 2.0L',                  'category' => 'Mixers',  'sale_price' => 25.00,  'unit' => '2.0L',   'units_per_package' => 6],
            ['id' => 28, 'name' => 'Aquarius Pera 2.0L',           'category' => 'Mixers',  'sale_price' => 25.00,  'unit' => '2.0L',   'units_per_package' => 6],
            ['id' => 29, 'name' => 'Agua Vital 600 ml',            'category' => 'Mixers',  'sale_price' => 10.00,  'unit' => '600ml',  'units_per_package' => 12],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['id' => $p['id']], $p);
        }
    }
}
