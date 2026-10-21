<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogsTable extends Migration {
    public function up()
    {
        if(!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function(Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->nullable();
                $table->string('user_name')->nullable();
                $table->string('method', 20);
                $table->string('route_name')->nullable();
                $table->string('uri');
                $table->string('ip_address', 60)->nullable();
                $table->string('user_agent')->nullable();
                $table->text('payload')->nullable();
                $table->integer('status_code')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
}
