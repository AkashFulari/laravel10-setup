<?php

namespace App\Console\Commands;

use App\Helpers\NotificationHelper;
use App\Models\NotifiedUser;
use Illuminate\Console\Command;

class AdminNotifyCRON extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:admin-push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $getAllNotifications = NotifiedUser::with('notification')
            ->where("has_send", false)
            ->get();
        if (count($getAllNotifications) > 0) {
            foreach ($getAllNotifications as $notificationInfo) {
                $params = [
                    "user_id" => $notificationInfo->user_id,
                    "user_type" => $notificationInfo->user_type,
                    'title' => $notificationInfo->notification->title,
                    'body' => $notificationInfo->notification->message,
                    'image' => $notificationInfo->notification->image,
                    'action' => $notificationInfo->notification->action,
                    'action_id' => $notificationInfo->notification->action_id,
                    'type' => $notificationInfo->notification->type,
                ];
                NotificationHelper::SendPush($params);

                $notifiedUser = NotifiedUser::find($notificationInfo->id);
                $notifiedUser->has_send = true;
                // $notifiedUser->sent_at = date("Y-m-d H:i:s");
                $notifiedUser->save();
            }
        }
    }
}
