<?php

use Illuminate\Support\Facades\DB;

/**
 * Custom Activity Logger for PMS API
 * This uses the JWT authenticated user and captures IP data.
 */
function logAdminActivity($connection, $username, $action, $details = '', $page = null, $status = null, $entityType = null, $entityId = null)
{

    date_default_timezone_set('Asia/Kolkata');


    $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $now = date('Y-m-d H:i:s');

    // Use the Lumen DB facade to insert into the existing logs table
    try {
        DB::table('admin_activity_logs')->insert([
            'event_time'   => $now,
            'username'     => $username,
            'page'         => $page ?? 'PMS_API',
            'action'       => $action,
            'status'       => $status,
            'entity_type'  => $entityType,
            'entity_id'    => $entityId,
            'details'      => $details,
            'ip_address'   => $ip,
            'source'       => 'pms_api'
        ]);
    } catch (\Exception $e) {
        // Log the error to the main Lumen log if the database insert fails
        error_log("Logging failed: " . $e->getMessage());
    }
}