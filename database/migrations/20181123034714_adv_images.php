<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AdvImages extends Migrator
{
    public function up()
    {
        $table = $this->table('adv_images');
        $table->setId('id');
        $table->addColumn(Column::integer('pid')->setNullable()->setComment('归属id'));
        $table->addColumn(Column::string('location')->setComment('图片路径'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('创建时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('adv_images');
    }
}
