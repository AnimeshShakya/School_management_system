<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('class_sections')) {
            Schema::create('class_sections', function (Blueprint $table) {
                $table->id();
                $table->integer('class_id');
                $table->integer('section_id');
                $table->integer('class_teacher_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('class_sections')) {
            Schema::dropIfExists('class_sections');
        }
    }
};
