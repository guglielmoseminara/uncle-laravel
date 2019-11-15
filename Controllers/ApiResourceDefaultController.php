<?php

namespace UncleProject\UncleLaravel\Controllers;

use RafflesArgentina\ResourceController\ApiResourceController;
use RafflesArgentina\ResourceController\Exceptions\ResourceControllerException;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Http\Request;
use UncleProject\UncleLaravel\Traits\ControllerHelper;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Database\Eloquent\Relations\Relation;
use DB;
use App;
use Storage;
use File;
use Lang;

class ApiResourceDefaultController extends ApiResourceController{

    use ControllerHelper;

    public $pruneHasMany = true;

    public function index(Request $request) {
        $this->getFormRequestInstance();
        $perPage = $request->get('perPage');
        $presenter = $request->get('presenter');
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $sortedBy = $request->get('sortedBy') ? $request->get('sortedBy') : 'asc';
        if (isset($this->indexPresenter)) {
            $this->repository->setPresenter($this->indexPresenter);
        }
        if (isset($this->indexCriteria)) {
            $this->repository->pushCriteria($this->indexCriteria);
        }
        if ($presenter) {
            try {
                $this->repository->setPresenter($this->repository->resource()->getPresenterClassPath(ucfirst($presenter)));
            } catch (\Exception $e) {

            }
        }
        if ($perPage == '-1') {
            $items = $this->getItemsCollection($orderBy, $sortedBy);
        } else {
            $items = $this->getPaginatorInstance($perPage, $orderBy, $sortedBy);
        }
        return $this->validSuccessJsonResponse('Success', $items);
    }

    public function show(Request $request, $key) {
        $presenter = $request->get('presenter');
        if (isset($this->showPresenter)) {
            $this->repository->setPresenter($this->showPresenter);
        }
        if ($presenter) {
            try {
                $this->repository->setPresenter($this->repository->resource()->getPresenterClassPath(ucfirst($presenter)));
            } catch (\Exception $e) {

            }
        }
        $model = $this->findFirstByKey($key);
        if (!$model) {
            return $this->validNotFoundJsonResponse();
        }

        return $this->validSuccessJsonResponse('Success', $model);
    }

    public function findFirstByKey($key) {
        if ($this->useSoftDeletes) {
            return $this->repository
                ->withTrashed()
                ->find($key);
        }
        return $this->repository
            ->find($key);
    }

    public function getPaginatorInstance($perPage = 15, $orderBy = 'updated_at', $order = 'desc') {
        if ($this->useSoftDeletes) {
            return $this->repository->withTrashed()->paginate($perPage);
        }
        return $this->repository->paginate($perPage);
    }

    public function updateOrCreateRelations($requestData, Model $model) {
        foreach ($requestData as $name => $attributes) {
            if (is_array($attributes)) {
                if($this->_checkRelationExists($model, $name))
                {
                    $relation = $model->{$name}();
                    $this->handleRelations($attributes, $model, $relation);
                }
            }
        }
    }

    public function store(Request $request) {
        $this->getFormRequestInstance();

        DB::beginTransaction();

        if(method_exists($this,'beforeStore'))
            $this->beforeStore($request);

        $store = $this->storeDB($request);

        try {
            if (method_exists($this, 'afterStore'))
                $this->afterStore($store, $request);
        } catch (\Exception $e) {
            DB::rollback();

            $message = $this->storeFailedMessage($e->getMessage());
            throw new ResourceControllerException($message);
        }

        DB::commit();

        $message = $this->storeSuccessfulMessage($store['number']);
        if (isset($this->storePresenter)) {
            $this->repository->setPresenter($this->storePresenter);
            $data = $this->repository->find($store['model']->id);
        } else {
            $data = $store['model'];
        }


        return $this->validSuccessJsonResponse($message, $data);
    }


    public function update(Request $request, $key) {
        $this->getFormRequestInstance();
        $model = $this->findFirstByKey($key);
        if (!$model) {
            return $this->validNotFoundJsonResponse();
        }
        $this->setRequestUser($request);

        DB::beginTransaction();

        if(method_exists($this,'beforeUpdate'))
            $this->beforeUpdate($model, $request);

        try {
            $instance = $this->repository->update($request->all(), $key);
            $model = $instance;
            $mergedRequest = $this->uploadFiles($request, $model);
            $this->updateOrCreateRelations($mergedRequest, $model);
        } catch (\Exception $e) {
            DB::rollback();

            $message = $this->updateFailedMessage($key, $e->getMessage());
            throw new ResourceControllerException($message);
        }

        try {
            if (method_exists($this, 'afterUpdate'))
                $this->afterUpdate($model, $request);
        } catch (\Exception $e) {
            DB::rollback();

            $message = $this->updateFailedMessage($e->getMessage());
            throw new ResourceControllerException($message);
        }

        DB::commit();

        $message = $this->updateSuccessfulMessage($key);
        if (isset($this->updatePresenter)) {
            $this->repository->setPresenter($this->updatePresenter);
            $data = $this->repository->find($model->id);
        } else {
            $data = $model;
        }
        return $this->validSuccessJsonResponse($message, $data);
    }

    public function uploadFiles(Request $request, Model $model, $relativePath = null) {
        if (!$relativePath) {
            $relativePath = $this->getDefaultRelativePath();
        }
        $fileBag = $request->files;
        $requestData = $request->all();
        $relativePath .= $model->getTable().'/'.$model->id.'/';
        foreach ($fileBag->all() as $paramName => $uploadedFiles) {
            $attributes = $model->getAttributes();
            if (array_key_exists($paramName, $attributes)) {
                $this->handleNonMultipleFileUploads($model, $paramName, $uploadedFiles, $relativePath);
            } else {
                $requestData = $this->handleMultipleFileUploads($request, $model, $paramName, $uploadedFiles, $relativePath);
            }
        }
        return $requestData;
    }

    public function destroy(Request $request, $key){
        $this->getFormRequestInstance();

        $model = $this->findFirstByKey($key);

        if (!$model) {
            return $this->validNotFoundJsonResponse();
        }

        DB::beginTransaction();

        try {
            $this->repository->delete($key);
        } catch (\Exception $e) {
            DB::rollback();

            $message = $this->destroyFailedMessage($key, $e->getMessage());
            throw new ResourceControllerException($message);
        }

        DB::commit();

        $message = $this->destroySuccessfulMessage($key);
        $data = $model;

        return $this->validSuccessJsonResponse($message, $data);
    }

    protected function storeDB(Request $request, $ignoreRelations = false){

        $this->setRequestUser($request);

        try {
            $instance = $this->repository->create($request->all());
            $model = $instance;
            $number = $model->{$model->getRouteKeyName()};
            $requestData = $this->uploadFiles($request, $model);
            if(!$ignoreRelations) $this->updateOrCreateRelations($requestData, $model);
        } catch (\Exception $e) {
            DB::rollback();

            $message = $this->storeFailedMessage($e->getMessage());
            throw new ResourceControllerException($message);
        }

        return [
            'model' => $model,
            'number' => $number
        ];
    }

    protected function handleMultipleFileUploads(Request $request, Model $model, $paramName, $uploadedFiles, $relativePath)
    {
        $this->_checkFileRelationExists($model, $paramName);

        $data = $request->all();
        $fileBag = $request->files;
        $counter = 0;
        foreach ($uploadedFiles as $index => $uploadedFile) {
            if (is_array($uploadedFile)) {
                $index = array_keys($uploadedFile)[0];
                $uploadedFile = array_values($uploadedFile)[0];
            }
            if (!$uploadedFile->isValid()) {
                throw new UploadException($uploadedFile->getError());
            }
            $filename = $this->getFilename($uploadedFile);
            $destination = $this->getStoragePath($relativePath);
            $this->moveUploadedFile($uploadedFile, $filename, $relativePath);

            $location = $filename;
            $data[$paramName][$counter][$index] = $location;
            $counter++;
        }
        return $data;
    }

    protected function handleNonMultipleFileUploads(Model $model, $paramName, $uploadedFile, $relativePath) {
        if (!$uploadedFile->isValid()) {
            throw new UploadException($uploadedFile->getError());
        }

        $filename = $this->getFilename($uploadedFile);
        $destination = $this->getStoragePath($relativePath);

        $this->moveUploadedFile($uploadedFile, $filename, $relativePath);

        $location = $filename;

        $model->{$paramName} = $location;
        $model->save();
    }

    protected function getStoragePath($relativePath) {
        return Storage::disk()->getDriver()->getAdapter()->getPathPrefix().$relativePath;
    }

    protected function moveUploadedFile($uploadedFile, $filename, $destination) {
        if (env('APP_ENV') == 'testing') {
            $destination = str_replace('uploads/', '', $destination);
            return Storage::disk('uploads')->put($destination.$filename, File::get($uploadedFile->getrealpath()));
        } else {
            return Storage::put($destination.$filename, File::get($uploadedFile->getrealpath()));
        }
    }

    /**
     * HasMany relation updateOrCreate logic.
     *
     * @param array    $fillable The relation fillable.
     * @param Model    $model    The eloquent model.
     * @param Relation $relation The eloquent relation.
     *
     * @return array
     */
    protected function updateOrCreateHasMany(array $fillable, Model $model, Relation $relation)
    {
        $keys = [];
        $id = '';
        $records = [];
        foreach ($fillable as $fields) {
            if (is_array($fields)) {
                if (array_key_exists('id', $fields)) {
                    $id = $fields['id'];
                }
                if (array_except($fields, ['id'])) {
                    $record = $relation->updateOrCreate(['id' => $id], $fields);
                    array_push($keys, $record->id);
                    array_push($records, $record);
                }
            } else {
                if (array_except($fillable, ['id'])) {
                    $record = $relation->updateOrCreate(['id' => $id], $fillable);
                    array_push($keys, $record->id);
                    array_push($records, $record);
                }
            }
        }
        if ($keys && (property_exists($this, 'pruneHasMany') && $this->pruneHasMany !== false)) {
            $reflection = new \ReflectionClass($model);
            $notIn = $relation->getRelated()->whereHas(strtolower($reflection->getShortName()), function($query) use($model) {
                $query->where('id', $model->id);
            })->whereNotIn('id', $keys)->get();
            foreach ($notIn as $record) {
                $record->forceDelete();
            }
        }
        return $records;
    }

    protected function renderImage($resource, $id, $imageName){
        $filePath = Storage::disk('uploads')->getDriver()->getAdapter()->getPathPrefix().$resource.'/'.$id.'/'.$imageName;
        $type = File::mimeType($filePath);
        $file = File::get($filePath);
        return $this->validSuccessImageResponse($file, $type);
    }


    /**
     * BelongsToMany relation updateOrCreate logic.
     *
     * @param array    $fillable The relation fillable.
     * @param Model    $model    The eloquent model.
     * @param Relation $relation The eloquent relation.
     *
     * @return array
     */
    protected function updateOrCreateBelongsToMany(array $fillable, Model $model, Relation $relation)
    {
        $keys = [];
        $records = [];

        $related = $relation->getRelated();
        foreach ($fillable as $fields) {
            if (array_key_exists('id', $fields)) {
                $id = $fields['id'];
                array_push($keys, $id);
            } else {
                $id = '';
            }
            if (array_except($fields, ['id'])) {
                $record = $related->where($fields)->first();
                if (!$record) {
                    $record = $related->updateOrCreate(['id' => $id], $fields);
                }
                array_push($keys, $record->id);
                array_push($records, $record);
            }
        }
        $reflection = new \ReflectionClass($model);
        $notIn = $relation->newPivot()->whereHas(strtolower($reflection->getShortName()), function($query) use($model) {
            $query->where('id', $model->id);
        })->whereNotIn('id', $keys)->get();
        foreach ($notIn as $record) {
            $record->forceDelete();
        }
        $relation->sync($keys);
        return $records;
    }

    private function _checkFileRelationExists(Model $model, $relationName) {
        if ((!method_exists($model, $relationName) && !$model->{$relationName}() instanceof Relation)) {
            if (Lang::has('resource-controller.filerelationinexistent')) {
                $message = trans('resource-controller.filerelationinexistent', ['relationName' => $relationName]);
            } else {
                $message = "Request file '{$relationName}' is not named after an existent relation.";
            }
            throw new UploadException($message);
        }
    }

    private function _checkRelationExists(Model $model, string $relationName) {
        /*if (!method_exists($model, $relationName) || !$model->{$relationName}()) {
            if (Lang::has('resource-controller.data2relationinexistent')) {
                $message = trans('resource-controller.data2relationinexistent', ['relationName' => $relationName]);
            } else {
                $message = "Array type request data '{$relationName}' must be named after an existent relation.";
            }

            throw new MassAssignmentException($message);
        }*/

        return !(!method_exists($model, $relationName) || !$model->{$relationName}());
    }

    private function setRequestUser(&$request) {
        $user = $request->user();
        if ($user) {
            $isAdmin = in_array('admin', $user->getRoleNames()->toArray());
            if ($this->repository->getRelationship('user') == 'BelongsTo') {
                if (!isset($request->user_id) || !$isAdmin || ($isAdmin && isset($request->user_id) && $request->user_id == $request->user()->id)) {
                    $request->merge(['user_id' => $request->user()->id]);
                }
            }
        }
    }

}
