<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Starter codes and asset types for the categories the seeder creates,
     * so existing installs can generate asset codes straight away.
     */
    private const DEFAULTS = [
        'Computer & IT Equipment' => ['C', ['Laptop' => 'LAP', 'Desktop Computer' => 'DES', 'Monitor' => 'MON', 'Printer' => 'PRI', 'Projector' => 'PRO', 'Network Equipment' => 'NET']],
        'Office Equipment' => ['OE', ['Photocopier' => 'COP', 'Scanner' => 'SCN', 'Telephone' => 'TEL', 'Shredder' => 'SHR']],
        'Furniture' => ['F', ['Office Chair' => 'CHR', 'Office Table' => 'TBL', 'Cabinet' => 'CAB', 'Sofa' => 'SOF', 'Bookshelf' => 'BKS']],
        'Vehicle' => ['V', ['Car' => 'CAR', 'Van' => 'VAN', 'Motorcycle' => 'MOT', 'Lorry' => 'LOR']],
    ];

    public function up(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            // Short code used as the first part of generated asset codes (e.g. "C").
            $table->string('short_code', 5)->nullable()->after('code');
        });

        Schema::create('asset_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_category_id')->constrained('asset_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 5);
            $table->timestamps();
        });

        foreach (DB::table('asset_categories')->get() as $category) {
            [$short, $types] = self::DEFAULTS[$category->name] ?? [$this->initials($category->name), []];

            DB::table('asset_categories')->where('id', $category->id)->update(['short_code' => $short]);

            foreach ($types as $name => $code) {
                DB::table('asset_types')->insert([
                    'asset_category_id' => $category->id,
                    'name' => $name,
                    'code' => $code,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_types');

        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropColumn('short_code');
        });
    }

    private function initials(string $name): string
    {
        preg_match_all('/\b[A-Za-z]/', $name, $matches);

        return strtoupper(substr(implode('', $matches[0]), 0, 5)) ?: 'A';
    }
};
