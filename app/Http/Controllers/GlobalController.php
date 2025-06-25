<?php

namespace App\Http\Controllers;

use App\Enums\ChatMessageTypes;
use App\Enums\CmsCode;
use App\Enums\UserTypes;
use App\Enums\Days;
use App\Enums\EnumUtils;
use App\Enums\MasterCategoryTypes;
use App\Enums\NotificationPushType;
use App\Enums\Status;
use App\Exceptions\TransException;
use App\Models\Cms;
use App\Models\MasterCategory;
use App\Models\MasterCountry;
use App\Models\MasterDocumentType;
use App\Models\MasterState;
use App\Models\ModelTrim;
use App\Models\Setting;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GlobalController extends Controller
{
    public function GetServices()
    {
        try {
            $list = MasterCategory::where("type", MasterCategoryTypes::SERVICES)->where("status", Status::ACTIVE)->get();
            $resp = $list->toArray();
            $InsuranceIndex = -1;
            $WarrantyIndex = -1;
            foreach ($resp as $i => &$item) {
                if ($item['name'] === 'Warranty') {
                    $WarrantyIndex = $i;
                } else if ($item['name'] === 'Insurance') {
                    $InsuranceIndex = $i;
                }
            }

            if ($InsuranceIndex > 0 && $WarrantyIndex > 0) {
                $insurance = $resp[$InsuranceIndex];
                $resp[$InsuranceIndex] = $resp[$WarrantyIndex];
                $resp[$WarrantyIndex] = $insurance;
            }
            $resp = ['list' => array_values($resp)];
            return $this->sendSuccess(msg("RECORDS_FOUND"), $resp);
        } catch (Exception $e) {
            return $this->sendErrorException($e);
        }
    }

    public function GetDDServices()
    {
        try {
            $list = MasterCategory::where("type", MasterCategoryTypes::SERVICES)
                ->whereNotIn("name", ["Insurance", "Warranty", "Battery Health Monitoring"])
                ->where("status", Status::ACTIVE)->get();
            return $this->sendSuccess(msg("RECORDS_FOUND"), $list);
        } catch (Exception $e) {
            return $this->sendErrorException($e);
        }
    }

    public function GetDDStatus()
    {
        try {
            $list = EnumUtils::parseValues(Status::cases());
            return $this->sendSuccess(msg("RECORDS_FOUND"), ['list' => $list]);
        } catch (Exception $e) {
            return $this->sendErrorException($e);
        }
    }

    public function GetDDDayCodes()
    {
        try {
            $list = EnumUtils::parseValues(Days::cases());
            return $this->sendSuccess(msg("RECORDS_FOUND"), ['list' => $list]);
        } catch (Exception $e) {
            return $this->sendErrorException($e);
        }
    }

    public function GetDDStates()
    {
        try {
            $list = MasterState::where("status", Status::ACTIVE)->get();
            return $this->sendSuccess(msg("RECORDS_FOUND"), $list);
        } catch (Exception $e) {
            return $this->sendErrorException($e);
        }
    }

    public function GetDDCountries()
    {
        try {
            $list = MasterCountry::where("status", Status::ACTIVE)->get();
            return $this->sendSuccess(msg("RECORDS_FOUND"), $list);
        } catch (Exception $e) {
            return $this->sendErrorException($e);
        }
    }

    public function GetDDDocumentTypes()
    {
        try {
            $list = MasterDocumentType::where("status", Status::ACTIVE)->get();
            return $this->sendSuccess(msg("RECORDS_FOUND"), $list);
        } catch (Exception $e) {
            return $this->sendErrorException($e);
        }
    }

    public function GetDDVehicleTypes()
    {
        try {
            $list = MasterCategory::where("type", MasterCategoryTypes::VEHICLE_TYPE)->where("status", Status::ACTIVE)->get();
            return $this->sendSuccess(msg("RECORDS_FOUND"), $list);
        } catch (Exception $e) {
            return $this->sendErrorException($e);
        }
    }

    public function GetDDVehicleMakes()
    {
        try {
            $list = MasterCategory::where("type", MasterCategoryTypes::MANUFACTURER)->where("status", Status::ACTIVE)->get();
            return $this->sendSuccess(msg("RECORDS_FOUND"), $list);
        } catch (Exception $e) {
            return $this->sendErrorException($e);
        }
    }

    public function GetDDVehicleModel($id)
    {
        try {
            $list = MasterCategory::where("parent_id", $id)->where("type", MasterCategoryTypes::MODEL)->where("status", Status::ACTIVE)->get();
            return $this->sendSuccess(msg("RECORDS_FOUND"), $list);
        } catch (Exception $e) {
            return $this->sendErrorException($e);
        }
    }

    public function GetDDVehicleModelTrims($id)
    {
        try {
            $list = ModelTrim::where("model_id", $id)->with('trim')->get();
            $resp = [];
            foreach ($list as $l) {
                $resp[] = [
                    "id" => $l->id,
                    "name" => $l->trim->name,
                    "drivetrain" => $l->trim->drivetrain,
                ];
            }

            return $this->sendSuccess(msg("RECORDS_FOUND"), collect($resp));
        } catch (Exception $e) {
            return $this->sendErrorException($e);
        }
    }

    public function GetDDUserTypes()
    {
        try {
            $list = EnumUtils::parseValues(UserTypes::cases());
            return $this->sendSuccess(msg("RECORDS_FOUND"), ['list' => $list]);
        } catch (Exception $e) {
            return $this->sendErrorException($e);
        }
    }

    public function GetDDMessageTypes()
    {
        try {
            $list = EnumUtils::parseValues(ChatMessageTypes::cases());
            return $this->sendSuccess(msg("RECORDS_FOUND"), ['list' => $list]);
        } catch (Exception $e) {
            return $this->sendErrorException($e);
        }
    }

    public function GetDDPushTypes()
    {
        try {
            $list = EnumUtils::parseValues(NotificationPushType::cases());
            return $this->sendSuccess(msg("RECORDS_FOUND"), ['list' => $list]);
        } catch (Exception $e) {
            return $this->sendErrorException($e);
        }
    }

    public function GetContactSupportInfo()
    {
        try {
            $info = Setting::FindById(1);
            return $this->sendSuccess(msg("RECORD_FOUND"), $info);
        } catch (Exception $e) {
            return $this->sendErrorException($e);
        }
    }

    public function GetPage($type)
    {
        try {
            $info = Cms::FindByCode($type);
            if (!$info) {
                throw new NotFoundHttpException(msg("PAGE_NOT_FOUND"));
            }
            return  response($info->description, 200)->header('Content-Type', 'text/html');;
        } catch (Exception $e) {
            return $this->sendErrorException($e);
        }
    }

    // this is temporary logging restful api.
    public function GetLogs()
    {
        // Define the path to the log file
        $logFilePath = storage_path('logs/laravel.log');

        // Check if the log file exists
        if (!file_exists($logFilePath)) {
            return response()->json(['error' => 'Log file not found.'], 404);
        }

        // Read the log file
        $logs = file_get_contents($logFilePath);

        // // Get today's date in the format used in the log file
        // $today = now()->format('Y-m-d');

        // // Read the log file and filter for today's logs
        // $todayLogs = array_filter(file($logFilePath), function ($line) use ($today) {
        //     return strpos($line, $today) !== false;
        // });

        return response(str_replace("\n", "</br>", $logs));
        // return response(implode(" ", array_values($todayLogs)));

    }

    public function DownloadLogs()
    {
        $filePath = storage_path('logs/laravel.log');

        // Check if file exists
        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }

        // Return the download response
        return response()->download($filePath);
    }

    public function ResetLogs()
    {
        $filePath = storage_path('logs/laravel.log');
        // Check if the file exists
        if (file_exists($filePath)) {
            // Open the file in write mode ('w')
            $file = fopen($filePath, 'w');

            // Truncate the file to 0 length
            ftruncate($file, 0);

            // Close the file
            fclose($file);

            return response()->json(['message' => 'File emptied successfully']);
        }

        return response()->json(['message' => 'File not found'], 404);
    }
}
