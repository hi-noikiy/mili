<?php

use think\migration\Migrator;
use think\migration\db\Column;

class RepairNote extends Migrator
{
    public function up()
    {
        $table = $this->table('repair_note');
        $table->setId('id');
        $table->addColumn(Column::integer('uid')->setComment('会员id'));
        $table->addColumn(Column::integer('repair_id')->setComment('工单id'));
        $table->addColumn(Column::text('content')->setComment('内容'));
        $table->addColumn(Column::char('status', 2)->setDefault(10)->setComment('状态'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('下单时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('repair');
    }
}