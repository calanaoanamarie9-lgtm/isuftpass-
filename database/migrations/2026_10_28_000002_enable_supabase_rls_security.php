<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fixes Supabase Security Advisor errors by enabling Row Level Security (RLS)
     * on all application tables in PostgreSQL, protecting them from unauthorized
     * access via Supabase's automatic PostgREST API.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // 1. Enable RLS on every table in the public schema
        DB::statement("
            DO \$\$
            DECLARE
                r RECORD;
            BEGIN
                FOR r IN (
                    SELECT tablename 
                    FROM pg_tables 
                    WHERE schemaname = 'public'
                ) LOOP
                    EXECUTE 'ALTER TABLE public.' || quote_ident(r.tablename) || ' ENABLE ROW LEVEL SECURITY;';
                END LOOP;
            END \$\$;
        ");

        // 2. Revoke direct anon / authenticated access from sensitive application tables
        $tables = [
            'users',
            'student_profiles',
            'password_reset_tokens',
            'sessions',
            'settings',
            'audit_logs',
            'gate_logs',
            'appointments',
            'document_requests',
            'slot_availabilities',
            'feedbacks',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("REVOKE ALL ON TABLE public.{$table} FROM anon, authenticated;");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("
            DO \$\$
            DECLARE
                r RECORD;
            BEGIN
                FOR r IN (
                    SELECT tablename 
                    FROM pg_tables 
                    WHERE schemaname = 'public'
                ) LOOP
                    EXECUTE 'ALTER TABLE public.' || quote_ident(r.tablename) || ' DISABLE ROW LEVEL SECURITY;';
                END LOOP;
            END \$\$;
        ");
    }
};
