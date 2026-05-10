<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('mediums')) {
            Schema::create('mediums', function (Blueprint $table) {
                $table->id();
                $table->string('name', 512);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('mediums')) {
            Schema::dropIfExists('mediums');
        }
    }
};
