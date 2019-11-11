<?php

namespace UncleProject\UncleLaravel\Traits;

use App;
use Illuminate\Support\Facades\Storage;

trait HasUpload {

    public function getAttribute($key) {
        if (! $key) {
            return;
        }
        if (!isset($this->uploadable)) {
            $this->uploadable = [];
        }
        if (in_array($key, $this->uploadable)) {
            $value = $this->attributes[$key];
            if (strstr($value, 'http://') === FALSE && strstr($value, 'https://') === FALSE) {
                $url = config('app.url').'/'.config('app.uploadable.url');
                $value = self::retrieveUrl($this, $url, $value);
            }
            return $value;
        }
        return parent::getAttribute($key);
    }

    public static function boot() {
        parent::boot();
        $path = self::getStoragePath();
        self::updating(function($model) use ($path){
            self::deleteFiles($model, $path);
        });
        self::deleting(function($model) use ($path){
            if ($model->forceDeleting) {
                self::deleteFiles($model, $path);
            }
        });
    }

    public function getFilePath($key) {
        $value = $this->original[$key];
        if (in_array($key, $this->uploadable)) {
            $path = self::getStoragePath();
            $url = self::retrieveUrl($this, $path, $value);
            $value = $url;
        }
        return $value;
    }

    private static function retrieveUrl($model, $url, $value) {
        $resource = $model->table;
        if (method_exists($model, 'getUploadableResource')) {
            $resource = $model->getUploadableResource();
        }
        $id = $model->id;
        if (method_exists($model, 'getUploadableId')) {
            $id = $model->getUploadableId();
        }
        $url = str_replace('{resource}', $resource, $url);
        $url = str_replace('{id}', $id, $url);
        $url = str_replace('{imageName}', $value, $url);
        return $url;
    }

    private static function getStoragePath() {
        if (env('APP_ENV') == 'testing') {
            return Storage::disk('uploads')->getDriver()->getAdapter()->getPathPrefix().config('app.uploadable.testingPath');
        } else {
            return Storage::disk()->getDriver()->getAdapter()->getPathPrefix().config('app.uploadable.path');
        }
    }

    private static function deleteFiles($model, $path) {
        $changes = $model->getDirty();
        foreach ($changes as $kup => $vup) {
            if (in_array($kup, $model->uploadable)) {
                $value = $model->getOriginal($kup);
                if(!empty($value)) {
                    $url = self::retrieveUrl($model, $path, $value);
                    if(file_exists($url)) {
                        try {
                            unlink($url);
                        } catch(\Exception $e) {

                        }
                    }
                }
            }
        }
    }

}