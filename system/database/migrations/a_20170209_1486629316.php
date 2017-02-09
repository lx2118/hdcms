<?php namespace system\database\migrations;
use houdunwang\database\build\Migration;
use houdunwang\database\build\Blueprint;

class a extends Migration {
    //执行
	public function up() {
		Schema::create( 'a', function ( Blueprint $table ) {
			$table->increments( 'id' );
            $table->timestamps();
        });
    }

    //回滚
    public function down() {
        Schema::drop( 'a' );
    }
}