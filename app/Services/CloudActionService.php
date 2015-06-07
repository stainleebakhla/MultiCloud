<?php namespace App\Services;

use App\Cloud;
use \Auth;
use \App\Services\CloudService;

class CloudActionService {

    protected $dropBoxService;

    protected $googleDriveService;

    protected $yandexDiskService;

    public function __construct(DropBoxService $dropBoxServices,
                                GoogleDriveService $googleDriveService,
                                YandexDiskService $yandexDiskService)
    {
        $this->dropBoxService = $dropBoxServices;
        $this->googleDriveService = $googleDriveService;
        $this->yandexDiskService = $yandexDiskService;
    }

    public function create($attributes)
    {
        return Cloud::create(array_merge($attributes, ['user_id' => Auth::user()->id]));
    }

    public function getInfo($id)
    {
        $cloud = $this->getCloud($id);

        if($cloud->type === Cloud::DropBox) {
            $response = $this->dropBoxService->infoCloud($id);
        }
        elseif ($cloud->type === Cloud::GoogleDrive) {
            $response = $this->googleDriveService->infoCloud($id);
        }
        elseif ($cloud->type === Cloud::YandexDisk) {
            $response = $this->yandexDiskService->infoCloud($id);
        }
        else {
            $response = ["Error of type cloud"];
        }

        $response["cloud"] = $cloud;

        return $response;
    }

    public function rename($id, $name)
    {
        return CloudService::renameCloud($id, $name);
    }

    public function remove($id)
    {
        $cloud = $this->getCloud($id);

        if($cloud->type === Cloud::DropBox) {
            $response = $this->dropBoxService->removeCloud($id);
        }
        elseif ($cloud->type === Cloud::GoogleDrive) {
            $response = $this->googleDriveService->removeCloud($id);
        }
        elseif ($cloud->type === Cloud::YandexDisk) {
            $response = $this->yandexDiskService->removeCloud($id);
        }
        else {
            $response = "Error of type cloud";
        }

        return $response;
    }

    private function getCloud($id)
    {
        return Cloud::findOrFail((int)$id);
    }

}