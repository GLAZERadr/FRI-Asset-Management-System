<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\MaintenanceAsset;

class NotificationService
{
    /**
     * Send approval request notification
     */
    public function sendApprovalRequest(MaintenanceAsset $maintenanceAsset, User $toUser, string $fromRole)
    {
        $title = 'Permintaan Persetujuan Perbaikan Aset';
        $message = "Anda memiliki permintaan persetujuan perbaikan untuk aset {$maintenanceAsset->asset->nama_asset} dari {$fromRole}";
        
        return Notification::create([
            'type' => 'approval_request',
            'title' => $title,
            'message' => $message,
            'notifiable_type' => User::class,
            'notifiable_id' => $toUser->id,
            'related_model' => MaintenanceAsset::class,
            'related_id' => $maintenanceAsset->id,
            'action_url' => route('pengajuan.show', $maintenanceAsset->id)
        ]);
    }

    /**
     * Send bulk approval request notification
     */
    public function sendBulkApprovalRequest($count, User $toUser, string $fromRole)
    {
        $title = 'Permintaan Persetujuan Perbaikan Aset';
        $message = "Anda memiliki {$count} permintaan persetujuan perbaikan aset dari {$fromRole}";
        
        return Notification::create([
            'type' => 'approval_request',
            'title' => $title,
            'message' => $message,
            'notifiable_type' => User::class,
            'notifiable_id' => $toUser->id,
            'action_url' => route('pengajuan.index')
        ]);
    }
    public function sendApprovalResult(MaintenanceAsset $maintenanceAsset, User $toUser, string $status, string $approverRole)
    {
        $statusText = $status === 'Diterima' ? 'disetujui' : 'ditolak';
        $title = "Pengajuan Perbaikan {$statusText}";
        $message = "Pengajuan perbaikan untuk aset {$maintenanceAsset->asset->nama_asset} telah {$statusText} oleh {$approverRole}";
        
        return Notification::create([
            'type' => 'approval_result',
            'title' => $title,
            'message' => $message,
            'notifiable_type' => User::class,
            'notifiable_id' => $toUser->id,
            'related_model' => MaintenanceAsset::class,
            'related_id' => $maintenanceAsset->id,
            'action_url' => route('pengajuan.show', $maintenanceAsset->id)
        ]);
    }

    /**
     * Get unread notifications for a user
     */
    public function getUnreadNotifications(User $user)
    {
        return Notification::where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all notifications for a user
     */
    public function getAllNotifications(User $user, $limit = 10)
    {
        return Notification::where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
        return $notification;
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user)
    {
        return Notification::where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function sendMaintenanceUpdate(MaintenanceAsset $maintenanceAsset, string $updateType = 'catatan')
    {
        // Get kaur users who should be notified
        $kaurLab = User::role('kaur_laboratorium')->first();
        $kaurKeuangan = User::role('kaur_keuangan_logistik_sdm')->first();
        
        $title = 'Laporan Perbaikan Diperbarui';
        $message = "Laporan perbaikan untuk aset {$maintenanceAsset->asset->nama_asset} telah diperbarui dengan catatan baru";
        
        $notifications = [];
        
        // Send to kaur laboratorium
        if ($kaurLab) {
            $notifications[] = Notification::create([
                'type' => 'maintenance_update',
                'title' => $title,
                'message' => $message,
                'notifiable_type' => User::class,
                'notifiable_id' => $kaurLab->id,
                'related_model' => MaintenanceAsset::class,
                'related_id' => $maintenanceAsset->id,
                'action_url' => route('perbaikan.status.done', $maintenanceAsset->maintenance_id)
            ]);
        }
        
        // Send to kaur keuangan
        if ($kaurKeuangan) {
            $notifications[] = Notification::create([
                'type' => 'maintenance_update',
                'title' => $title,
                'message' => $message,
                'notifiable_type' => User::class,
                'notifiable_id' => $kaurKeuangan->id,
                'related_model' => MaintenanceAsset::class,
                'related_id' => $maintenanceAsset->id,
                'action_url' => route('perbaikan.status.done', $maintenanceAsset->maintenance_id)
            ]);
        }
        
        return $notifications;
    }

    /**
     * Send maintenance completion notification
     */
    public function sendMaintenanceCompletion(MaintenanceAsset $maintenanceAsset)
    {
        // Get all kaur users and the original requester
        $kaurLab = User::role('kaur_laboratorium')->first();
        $kaurKeuangan = User::role('kaur_keuangan_logistik_sdm')->first();
        $requester = User::find($maintenanceAsset->requested_by);
        
        $title = 'Perbaikan Aset Selesai';
        $message = "Perbaikan aset {$maintenanceAsset->asset->nama_asset} telah selesai dilaksanakan";
        
        $notifications = [];
        $recipients = collect([$kaurLab, $kaurKeuangan, $requester])->filter();
        
        foreach ($recipients as $user) {
            $notifications[] = Notification::create([
                'type' => 'maintenance_completed',
                'title' => $title,
                'message' => $message,
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'related_model' => MaintenanceAsset::class,
                'related_id' => $maintenanceAsset->id,
                'action_url' => route('perbaikan.status.view', $maintenanceAsset->maintenance_id)
            ]);
        }
        
        return $notifications;
    }
}