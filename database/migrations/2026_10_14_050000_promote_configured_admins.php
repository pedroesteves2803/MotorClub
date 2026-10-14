<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Promote only the accounts explicitly configured by the deployment owner.
     *
     * The value is read once during migration and persisted in admin_role. The
     * panel continues to authorize from the database role, so this is not an
     * email-only access check and it does not create accounts.
     */
    public function up(): void
    {
        $emails = preg_split('/[\s,;]+/', (string) env('ADMIN_EMAILS', ''), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($emails as $email) {
            DB::table('users')
                ->whereRaw('LOWER(email) = ?', [strtolower(trim($email))])
                ->update([
                    'admin_role' => 'super_admin',
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * This data migration is intentionally irreversible. Revocation is done
     * explicitly by changing the user's admin_role in the protected panel.
     */
    public function down(): void
    {
        // Do not demote accounts if a deployment is rolled back.
    }
};
