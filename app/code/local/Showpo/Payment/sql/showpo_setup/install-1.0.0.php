<?php

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

if (!$connection->tableColumnExists($installer->getTable('sales/quote_payment'), 'showpo_ref')) {
    $connection->addColumn($installer->getTable('sales/quote_payment'), 'showpo_ref', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'comment' => 'Showpo ref',
        'length'  => '255'
    ));
}

if (!$connection->tableColumnExists($installer->getTable('sales/order_payment'), 'showpo_ref')) {
    $connection->addColumn($installer->getTable('sales/order_payment'), 'showpo_ref', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'comment' => 'Showpo ref',
        'length'  => '255'
    ));
}

$installer->endSetup();
