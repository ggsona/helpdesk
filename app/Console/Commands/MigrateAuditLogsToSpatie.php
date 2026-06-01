<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateAuditLogsToSpatie extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:migrate-to-spatie';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra los registros antiguos de la tabla audit_logs a la nueva tabla activity_log de Spatie';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando migración de registros de auditoría antiguos...');

        $oldLogs = DB::table('audit_logs')->get();
        $count = 0;

        foreach ($oldLogs as $log) {
            $properties = [];
            
            if ($log->old_values) {
                $properties['old'] = json_decode($log->old_values, true);
            }
            if ($log->new_values) {
                $properties['attributes'] = json_decode($log->new_values, true);
            }
            if ($log->ip_address) {
                $properties['ip'] = $log->ip_address;
            }
            if ($log->user_agent) {
                $properties['user_agent'] = $log->user_agent;
            }

            // Map action to event
            $event = $log->action;
            if ($event === 'create') $event = 'created';
            if ($event === 'update') $event = 'updated';
            if ($event === 'delete') $event = 'deleted';

            DB::table('activity_log')->insert([
                'log_name' => 'default',
                'description' => 'Migrado desde audit_logs',
                'subject_type' => $log->auditable_type,
                'event' => $event,
                'subject_id' => $log->auditable_id == 0 ? null : $log->auditable_id,
                'causer_type' => $log->user_id ? 'App\Models\User' : null,
                'causer_id' => $log->user_id,
                'properties' => json_encode($properties),
                'batch_uuid' => null,
                'created_at' => $log->created_at,
                'updated_at' => $log->created_at, // audit_logs doesn't have updated_at
            ]);
            $count++;
        }

        $this->info("Migración completada. Se migraron $count registros a Spatie Activitylog.");
    }
}
