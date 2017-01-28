<?php namespace Dreamlands\Spot;

use Spot\Query\Resolver;

class DResolver extends Resolver
{
    public function migrateCreateSchema()
    {
        $schema = parent::migrateCreateSchema();
        foreach ($schema->getTables() as $table) {
//            foreach ($table->getColumns() as $column) {
//                $column->setCustomSchemaOptions([
//                    'charset' => 'utf8mb4',
//                    'collation' => 'utf8mb4_unicode_ci',
//                ]);
//            }

            $table
                ->addOption('charset', 'utf8mb4')
                ->addOption('collate', 'utf8mb4_unicode_ci');
        }

        return $schema;
    }
}
