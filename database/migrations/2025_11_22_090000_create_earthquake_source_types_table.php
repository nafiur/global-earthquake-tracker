<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('earthquake_source_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->timestamps();
        });

        Schema::table('earthquake_sources', function (Blueprint $table) {
            $table->foreignId('type_id')->nullable()->after('url')->constrained('earthquake_source_types');
        });

        $now = now();
        DB::table('earthquake_source_types')->insert([
            ['name' => 'USGS (United States Geological Survey)', 'key' => 'usgs', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'EMSC (European-Mediterranean Seismological Centre)', 'key' => 'emsc', 'created_at' => $now, 'updated_at' => $now],
        ]);

        $types = DB::table('earthquake_source_types')->pluck('id', 'key');

        if (Schema::hasColumn('earthquake_sources', 'type')) {
            DB::table('earthquake_sources')->where('type', 'usgs')->update(['type_id' => $types['usgs'] ?? null]);
            DB::table('earthquake_sources')->where('type', 'emsc')->update(['type_id' => $types['emsc'] ?? null]);
        }

        DB::table('earthquake_sources')->whereNull('type_id')->update(['type_id' => $types['usgs'] ?? null]);

        if (Schema::hasColumn('earthquake_sources', 'type')) {
            Schema::table('earthquake_sources', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }

    public function down(): void
    {
        Schema::table('earthquake_sources', function (Blueprint $table) {
            $table->string('type')->default('usgs');
        });

        DB::table('earthquake_sources')
            ->join('earthquake_source_types', 'earthquake_sources.type_id', '=', 'earthquake_source_types.id')
            ->update(['earthquake_sources.type' => DB::raw('earthquake_source_types.key')]);

        Schema::table('earthquake_sources', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
            $table->dropColumn('type_id');
        });

        Schema::dropIfExists('earthquake_source_types');
    }
};
