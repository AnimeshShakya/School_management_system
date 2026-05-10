<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('classes')) {
            Schema::create('classes', function (Blueprint $table) {
                $table->id();
                $table->string('name', 512);
                $table->integer('medium_id');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('classes')) {
            Schema::dropIfExists('classes');
        }
    }
};
