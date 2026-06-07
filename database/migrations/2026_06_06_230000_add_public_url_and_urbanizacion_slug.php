<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'public_base_url'],
            ['value' => null, 'created_at' => now(), 'updated_at' => now()]
        );

        Schema::table('urbanizaciones', function (Blueprint $table): void {
            if (! Schema::hasColumn('urbanizaciones', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('nombre');
            }
        });

        DB::table('urbanizaciones')
            ->select('id', 'nombre', 'slug')
            ->orderBy('id')
            ->get()
            ->each(function (object $urbanizacion): void {
                if ($urbanizacion->slug) {
                    return;
                }

                $base = Str::slug($urbanizacion->nombre) ?: 'urbanizacion-'.$urbanizacion->id;
                $slug = $base;
                $suffix = 2;

                while (DB::table('urbanizaciones')->where('slug', $slug)->where('id', '!=', $urbanizacion->id)->exists()) {
                    $slug = $base.'-'.$suffix++;
                }

                DB::table('urbanizaciones')->where('id', $urbanizacion->id)->update(['slug' => $slug]);
            });
    }

    public function down(): void
    {
        Schema::table('urbanizaciones', function (Blueprint $table): void {
            if (Schema::hasColumn('urbanizaciones', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
        });

        DB::table('system_settings')->where('key', 'public_base_url')->delete();
    }
};
