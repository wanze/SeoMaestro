<?php

namespace SeoMaestro;

use ProcessWire\Field;
use ProcessWire\Wire;

class DatabaseMigrations extends Wire
{
    const SCHEMA_VERSION = 2;
    const PDO_CODE_DUPLICATED_COLUMN = '42S21';

    public function run()
    {
        $fields = $this->getSeoMaestroFields();

        foreach ($fields as $field) {
            $this->runForField($field);
        }
    }

    private function runForField(Field $field)
    {
        $schemaVersion = (int) $field->get('schemaVersion') ?: 1;

        while ($schemaVersion < self::SCHEMA_VERSION) {
            $this->execute($field, $schemaVersion);
            $schemaVersion++;
            $field->set('schemaVersion', $schemaVersion);
            $field->save();
        }
    }

    private function getSeoMaestroFields()
    {
        return $this->wire('fields')->find('type=FieldtypeSeoMaestro');
    }

    private function execute(Field $field, $version)
    {
        if ($version === 1) {
            $sql = sprintf('ALTER TABLE field_%s ADD structuredData_inherit tinyint UNSIGNED NOT NULL DEFAULT 1 AFTER robots_inherit', $field->name);
            try {
                $this->wire('database')->exec($sql);
            } catch (\PDOException $e) {
                // Ignore exceptions for duplicated columns.
                if ($e->getCode() !== self::PDO_CODE_DUPLICATED_COLUMN) {
                    throw $e;
                }
            }
        }
    }
}
