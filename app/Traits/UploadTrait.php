<?php

namespace App\Traits;

use App\Exceptions\TransException;
use Aws\S3\S3Client;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

trait UploadTrait
{
    public static function uploadOne($uploadedFile, $folder = null, $fileName = null, $expiry = 10080)
    {
        try {
            $disk = config("filesystems.default");
            $name = !is_null($fileName) ? $fileName : (Str::random(25) . "." . $uploadedFile->getClientOriginalExtension());
            $filePath = $folder . "/" . $name;

            if ($disk == 's3') {
                $s3Client = new S3Client([
                    'region' => config('filesystems.disks.s3.region'),
                    'version' => 'latest',
                ]);
                $s3Bucket = config('filesystems.disks.s3.bucket');
                $s3Key =  $folder . "/" . $name; // your file name
                $s3Options = [];
                $options = [
                    'Bucket' => $s3Bucket,
                    'Key' => $s3Key,
                    'MetaData' => $s3Options,
                ];

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (in_array($fileExt, ['xlsx', 'pdf'])) {
                    // Define the custom headers (metadata)
                    $s3Options = [
                        'ContentType' => ($fileExt == 'pdf') ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=UTF-8',
                        'CacheControl' => 'max-age=3600',  // Set cache expiration
                        'ContentDisposition' => 'inline',  // Or 'attachment' for download
                    ];

                    $options['MetaData'] = $s3Options;
                    $options['ContentType'] = $s3Options['ContentType'];
                    $options['SourceFile'] = $uploadedFile;
                    $options['CacheControl'] = $s3Options['CacheControl'];
                    $options['ContentDisposition'] = $s3Options['ContentDisposition'];

                    $command = $s3Client->getCommand('PutObject', $options);

                    // Upload the file to S3
                    $result = $s3Client->execute($command);

                    // if (file_exists(public_path($filePath))) {
                    //     unlink(public_path($filePath));
                    // }

                    // Return the URL
                    return $result['ObjectURL'];
                }

                $command = $s3Client->getCommand('PutObject',  $options);
                $request = $s3Client->createPresignedRequest($command, now()->addMinutes($expiry));
                $presignedUrl = (string) $request->getUri();
                return self::uploadFile($presignedUrl,  $uploadedFile);
            }

            return Storage::disk($disk)->put($filePath, file_get_contents($uploadedFile));
        } catch (Exception $e) {
            Log::info("S3 UploadOne Exception: " . $e->getMessage() . " @File: " . $e->getFile() . " @Line: " . $e->getLine());
            throw $e;
        }
    }

    private static function uploadFile($url,  $data)
    {
        try {
            $image = fopen($data->getPathName(), "rb");
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_PUT, 1);
            curl_setopt($curl, CURLOPT_UPLOAD, 1);
            curl_setopt($curl, CURLOPT_INFILE, $image);
            curl_setopt($curl, CURLOPT_INFILESIZE, filesize($data->getPathName()));
            $result = curl_exec($curl);
            curl_close($curl);
            Log::info("S3 Upload Response" . $result);
        } catch (Exception $e) {
            Log::info("S3 Upload File Exception: " . $e->getMessage() . " @File: " . $e->getFile() . " @Line: " . $e->getLine());
            throw $e;
        }
    }

    public static function deleteOne($folder, $filename = null)
    {
        $disk = config("filesystems.default");
        if ($disk == 's3') {
            if (!empty($filename)) {
                if (self::fileExists($folder, $filename)) {
                    $s3Client = new S3Client([
                        'region' => config('filesystems.disks.s3.region'),
                        'version' => 'latest',
                    ]);

                    $s3Bucket = config('filesystems.disks.s3.bucket');
                    $s3Key = $folder . "/" . $filename; // your file name
                    $s3Client->deleteObject(
                        array(
                            'Bucket' => $s3Bucket,
                            'Key' => $s3Key,
                        )
                    );
                }
            }
        } else {
            if (!empty($filename)) {
                $url = explode("?", $filename);
                $filename = basename($url[0]);
                if (self::fileExists($folder, $filename)) {
                    Storage::disk($disk)->delete($folder . '/' . $filename);
                }
            }
        }
    }

    public static function getURL($folder, $filename = null, $expiry = 10)
    {
        $disk = config("filesystems.default");
        if (!empty($filename)) {
            if (self::fileExists($folder, $filename)) {
                if ($disk == "localexcel") {
                    return url($folder . '/' . $filename);
                }
                if ($disk == 's3') {
                    return Storage::disk($disk)->temporaryUrl($folder . "/" . $filename, now()->addMinutes($expiry));
                }

                return Storage::disk($disk)->url($folder . '/' . $filename);
            }
        }

        // return url('placeholder.png');
        return null;
    }

    public static function fileExists($folder, $filename = null)
    {
        $disk = config("filesystems.default");
        if (!empty($filename)) {
            $file = basename(parse_url($filename, PHP_URL_PATH));
            if (Storage::disk($disk)->exists($folder . '/' . $file)) {
                return true;
            }
        }

        return false;
    }

    public static function fileNameSlug($requestFile, $prefix = null)
    {
        $filename = $requestFile->getClientOriginalName();
        if ($prefix != null) {
            $filename = Str::slug($prefix);
        } else {
            $filename = Str::slug(pathinfo($filename, PATHINFO_FILENAME));
        }
        return $filename . '_' . time()  . "." . $requestFile->getClientOriginalExtension();
    }

    public static function getBasename($url)
    {
        if (is_null($url) || empty($url)) {
            return null;
        }
        $disk = config("filesystems.default");
        if ($disk == 's3') {
            $url = urldecode($url);
            $baseURL = explode("?", $url)[0];
            $baseName = pathinfo($baseURL, PATHINFO_BASENAME);
            return $baseName;
        }
        return basename($url);
    }
}
