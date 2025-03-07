<?php

namespace OCA\DienstzeitenApp\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000000Date20250307000000 extends SimpleMigrationStep {

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('dienstzeiten_entries')) {
            $table = $schema->createTable('dienstzeiten_entries');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('user_id', 'string', [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('first_name', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('last_name', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('email', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('service_date', 'date', [
                'notnull' => true,
            ]);
            $table->addColumn('start_time', 'time', [
                'notnull' => true,
            ]);
            $table->addColumn('end_time', 'time', [
                'notnull' => true,
            ]);
            $table->addColumn('station', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('other_details', 'text', [
                'notnull' => false,
                'default' => null,
            ]);
            $table->addColumn('overtime_due_to_emergency', 'boolean', [
                'notnull' => true,
                'default' => false,
            ]);
            $table->addColumn('emergency_number', 'string', [
                'notnull' => false,
                'length' => 255,
                'default' => null,
            ]);
            $table->addColumn('signature', 'text', [
                'notnull' => true,
            ]);
            $table->addColumn('created_at', 'datetime', [
                'notnull' => true,
            ]);
            $table->addColumn('status', 'string', [
                'notnull' => true,
                'length' => 32,
                'default' => 'pending',
            ]);
            $table->addColumn('rejection_reason', 'text', [
                'notnull' => false,
                'default' => null,
            ]);
            $table->addColumn('approved_by', 'string', [
                'notnull' => false,
                'length' => 64,
                'default' => null,
            ]);
            $table->addColumn('approved_at', 'datetime', [
                'notnull' => false,
                'default' => null,
            ]);
            $table->addColumn('token', 'string', [
                'notnull' => true,
                'length' => 128,
            ]);
            
            $table->setPrimaryKey(['id']);
            $table->addIndex(['user_id'], 'dienstzeit_user_id_idx');
            $table->addIndex(['status'], 'dienstzeit_status_idx');
        }

        if (!$schema->hasTable('dienstzeiten_settings')) {
            $table = $schema->createTable('dienstzeiten_settings');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('key', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('value', 'text', [
                'notnull' => true,
            ]);
            
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['key'], 'dienstzeit_setting_key_idx');
        }

        return $schema;
    }
}
