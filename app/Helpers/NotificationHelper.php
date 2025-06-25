<?php

namespace App\Helpers;

use App\Models\UserDevice;
use Minishlink\WebPush\WebPush;
use Google\Client;
use Illuminate\Support\Facades\Log;

class NotificationHelper
{
    private static function getAccessToken()
    {
        // Path to your service account JSON key file
        $serviceAccountPath = public_path('firebase-public-account.json');

        $client = new Client();
        $client->setAuthConfig($serviceAccountPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->useApplicationDefaultCredentials();
        $token = $client->fetchAccessTokenWithAssertion();
        Log::info("FCM Access Token : ", $token);
        return $token['access_token'];
    }

    private static function SendFCMNotificationV1($params = [])
    {
        $url = "https://fcm.googleapis.com/fcm/send";
        // $serverKey = 'AAAArSlgdw4:APA91bEYZPJqGYmnMn_0gM5IHARmuIXd56Ds2BlYAQ5JWsnC94RNuLfSsnjKST3TZfmtSeih5Rc9jLUfIkDdzB6yEItDEtk40Pg8wQThZWxIZTZe9krCg17yXgV_Jr3XqomFbZMCrfh3';
        $serverKey = config("services.fcm.server_key");

        $notification = [
            "title" => $params['title'],
            "body" => $params['body'],
            'sound' => 'default',
            'badge' => '1',
            'action_id' => $params['action_id'] ?? null,
            'action' => $params['action'] ?? null,
            "show_in_foreground" => true,
        ];

        $data = [
            "custom_notification" => [
                "title" => $params['title'],
                "body" => $params['body'],
                'action_id' => $params['action_id'] ?? null,
                'action' => $params['action'] ?? null,
                'sound' => 'default',
                'badge' => '1',
                "show_in_foreground" => true,
                "contentAvailable" =>  true
            ]
        ];

        if (isset($params['devices']) && count($params['devices']) > 0) {
            foreach ($params['devices'] as $device) {

                $arrayToSend = array(
                    'collapse_key' => 'new' . time(),
                    'data' => $data,
                    'notification' => $notification,
                    "priority" => "high",
                    'to' => trim($device->token),
                    "show_in_foreground" => true,
                    "contentAvailable" =>  true
                );

                $json = json_encode($arrayToSend);
                $headers = array();
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Authorization: key=' . $serverKey;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($ch);
                if ($response === FALSE) {
                    //    die('FCM Send Error: ' . curl_error($ch));
                }
                $resparray = json_decode($response);
                //  echo json_encode($resparray);
                curl_close($ch);
            }
        }

        return true;
    }

    private static function SendFCMNotificationV2($params = [])
    {
        $accessToken = self::getAccessToken();

        // Your Firebase project ID
        $projectId = config("services.fcm.project_id");

        $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ];
        $notification = [
            "title" => $params['title'],
            "body" => $params['body'],
            // 'action_id' => $params['action_id'] ?? null,
            // 'action' => $params['action'] ?? null,
        ];
        // $data = [
        //     'action_id' => $params['action_id'] ?? "",
        //     'action' => $params['action'] ?? "",
        //     'user_id' => $params['user_id'] ?? "",
        //     'branch_id' => $params['branch_id'] ?? "",
        // ];
        $data = [];
        if (isset($params['action_id']) && !empty($params['action_id'])) {
            $data['action_id'] =  (string)  $params['action_id'];
        }
        if (isset($params['action']) && !empty($params['action'])) {
            $data['action'] =   (string)  $params['action'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $data['user_id'] =   (string)  $params['user_id'];
        }
        if (isset($params['user_type']) && !empty($params['user_type'])) {
            $data['user_type'] =   (string)  $params['user_type'];
        }
        if (isset($params['type']) && !empty($params['type'])) {
            $data['type'] =   (string)  $params['type'];
        }
        if (isset($params['image']) && !empty($params['image'])) {
            $data['image'] =  (string)   $params['image'];
        }

        $android = [
            'collapse_key' => 'new' . time(),
            "priority" => "HIGH",
            "data" => $data,
            "notification" =>  $notification,
            "direct_boot_ok" => true
        ];

        $apns = [
            "headers" => $data,
            "payload" => [
                "aps" =>
                ["alert" => $notification]
            ]
        ];

        if (isset($params['devices']) && count($params['devices']) > 0) {
            foreach ($params['devices'] as $device) {
                $arrayToSend = [
                    "validate_only" => false,
                    "message" => [
                        'data' => $data,
                        'notification' => $notification,
                        'token' => trim($device->token),
                        // "android" =>   $android,
                        // "apns" =>   $apns
                    ]
                ];

                Log::info("FCM Notification : ",  $arrayToSend);

                $json = json_encode($arrayToSend);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($ch);
                if ($response === FALSE) {
                    //    die('FCM Send Error: ' . curl_error($ch));
                }
                $resparray = json_decode($response, true);
                //  echo json_encode($resparray);
                Log::info("FCM Notification Result : ", $resparray);
                curl_close($ch);
            }
        }

        return true;
    }

    public static function sendWebPush($notifications)
    {
        $auth = [
            'VAPID' => [
                'subject' => 'mailto:' . config('services.web_push.email'), // can be a mailto: or your website address
                'publicKey' => config('services.web_push.public_key'),
                'privateKey' => config('services.web_push.private_key'),
                // 'publicKey' => 'BFkDjAEFNCd6eYU4JCkWogiFrHcKFZXNYRLu6Xmvy23P87alcCg5aZ0gb9h3_93Y6136fzbo2co4WowsnYO6x8g', // (recommended) uncompressed public key P-256 encoded in Base64-URL
                // 'privateKey' => 'EwY9ix8sMQaY1PrSCFSHs39Z0HtGVP_4DjtFsh4w-3M', // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL
            ],
        ];
        $webPush = new WebPush($auth);
        foreach ($notifications as $notification) {
            $webPush->sendOneNotification(
                $notification['subscription'],
                $notification['payload'] // optional (defaults null)
            );
        }

        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            if ($report->isSuccess()) {
                // echo "[v] Message sent successfully for subscription {$endpoint}.";
            } else {
                // echo "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
            }
        }
    }

    public static function SendPush($obj)
    {
        $userDeviceToken = UserDevice::where('user_id', $obj['user_id'])
            ->where('user_type', $obj['user_type'])
            ->where("push_type", "FCM")
            ->groupBy("token")
            ->get();
        if (count($userDeviceToken) > 0) {
            $obj['devices'] = $userDeviceToken;
            self::SendFCMNotificationV2(params: $obj);
        }
    }
}
