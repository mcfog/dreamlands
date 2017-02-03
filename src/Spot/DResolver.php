<?php namespace Dreamlands\Spot;

use Spot\Query\Resolver;

class DResolver extends Resolver
{
    public function migrateCreateSchema()
    {
        $schema = parent::migrateCreateSchema();
        foreach ($schema->getTables() as $table) {
            $entityName = $this->mapper->entity();
            /** @noinspection PhpUndefinedMethodInspection */
            $entityName::alterTableSchema($table);

            $table
                ->addOption('charset', 'utf8mb4')
                ->addOption('collate', 'utf8mb4_unicode_ci');
        }

        return $schema;
    }
}
