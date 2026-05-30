<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $defaults = [
            'system_name' => 'IMPACTO URBANIZACIONES',
            'system_subtitle' => 'Sistema Integral de Terrenos',
            'company_name' => 'IMPACTO URBANIZACIONES',
            'razon_social' => 'IMPACTO URBANIZACIONES',
            'nit' => '',
            'direccion' => '',
            'ciudad' => 'Santa Cruz',
            'departamento' => 'Santa Cruz',
            'telefono' => '',
            'celular' => '',
            'whatsapp' => '',
            'email' => '',
            'website' => '',
            'footer_text' => 'Version piloto - MVP funcional.',
            'primary_color' => '#0f766e',
            'secondary_color' => '#0f2530',
            'logo_main' => '',
            'logo_login' => '',
            'logo_pdf' => '',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('system_settings')->insert([
                'key' => $key,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
