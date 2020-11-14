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
use Illuminate\Support\Arr;
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
        $modelTable = $this->repository->getModelInstance()->getTable();
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : $modelTable.'.id';
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

        if(method_exists($this,'addMeta'))
        {
            $meta = $this->addMeta('index');
            if($meta){
                if(!isset($items['meta'])) $items['meta'] = [];
                $items['meta'] = array_merge($items['meta'], $meta);
            }
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

        if(method_exists($this,'addMeta')) {
            $meta = $this->addMeta('show');
            if($meta){
                if(!isset($model['meta'])) $model['meta'] = [];
                $model['meta'] = array_merge($model['meta'], $meta);
            }
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

    public function getItemsCollection($orderBy = 'updated_at', $order = 'desc')
    {
        if ($this->useSoftDeletes) {
            return $this->repository->withTrashed()->get();
        }
        return $this->repository->get();
    }

    public function getPaginatorInstance($perPage = 15, $orderBy = 'updated_at', $order = 'desc') {
        if ($this->useSoftDeletes) {
            return $this->repository->withTrashed()->paginate($perPage);
        }
        return $this->repository->paginate($perPage);
    }

    public function store(Request $request) {
        $request = $this->getFormRequestInstance();

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

        if(method_exists($this,'addMeta')) {
            $meta = $this->addMeta('store');
            if($meta){
                if(!isset($data['meta'])) $data['meta'] = [];
                $data['meta'] =array_merge($data['meta'], $meta);
            }
        }

        return $this->validSuccessJsonResponse($message, $data);
    }

    public function update(Request $request, $key) {
        $request = $this->getFormRequestInstance();
        $model = $this->findFirstByKey($key);
        if (!$model) {
            return $this->validNotFoundJsonResponse();
        }
        $requestData = $this->setRequestUser($request, $request->validated(), $model);

        DB::beginTransaction();

        if(method_exists($this,'beforeUpdate'))
            $this->beforeUpdate($model, $request);

        try {
            $instance = $this->repository->update($requestData, $key);
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
            $primaryKey = $model->getKeyName();
            $data = $this->repository->find($model->$primaryKey);
        } else {
            $data = $model;
        }

        if(method_exists($this,'addMeta')) {
            $meta = $this->addMeta('update');
            if($meta){
                if(!isset($data['meta'])) $data['meta'] = [];
                $data['meta'] = array_merge($data['meta'], $meta);
            }
        }
        
        return $this->validSuccessJsonResponse($message, $data);
    }

    public function uploadFiles(Request $request, Model $model, $relativePath = null) {
        if (!$relativePath) {
            $relativePath = $this->getDefaultRelativePath();
        }
        $fileBag = $request->files;
        $requestData = $request->validated();
        $resource = $model->getTable();
        if (method_exists($model, 'getUploadableResource')) {
            $resource = $model->getUploadableResource();
        }
        $modelId = $model->id;
        if (method_exists($model, 'getUploadableId')) {
            $modelId = $model->getUploadableId();
        }
        $relativePath .= $resource.'/'.$modelId.'/';
        foreach ($fileBag->all() as $paramName => $uploadedFiles) {
            $attributes = $model->getFillable();
            if (in_array($paramName, $attributes)) {
                $this->handleNonMultipleFileUploads($model, $paramName, $uploadedFiles, $relativePath);
            } else {
                $requestData = $this->handleMultipleFileUploads($request, $model, $paramName, $uploadedFiles, $relativePath);
            }
        }
        return $requestData;
    }

    public function destroyMany(Request $request){

        $this->getFormRequestInstance();

        $data = [];
        $message = '';
        DB::beginTransaction();
        foreach($request->get('ids') as $key){
            $model = $this->findFirstByKey($key);

            if (!$model) {
                return $this->validNotFoundJsonResponse();
            }

            try {
                $this->repository->delete($key);
            } catch (\Exception $e) {
                DB::rollback();

                $message = $this->destroyFailedMessage($key, $e->getMessage());
                throw new ResourceControllerException($message);
            }

            $message .= $this->destroySuccessfulMessage($key)."! ";
            array_push($data,$model);

        }

        DB::commit();

        if(method_exists($this,'addMeta')) {
            $meta = $this->addMeta('destroy');
            if($meta){
                if(!isset($data['meta'])) $data['meta'] = [];
                $data['meta'] = array_merge($data['meta'], $meta);
            }
        }

        return $this->validSuccessJsonResponse($message, $data);
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

        if(method_exists($this,'addMeta')) {
            $meta = $this->addMeta('destroy');
            if($meta){
                if(!isset($data['meta'])) $data['meta'] = [];
                $data['meta'] = array_merge($data['meta'], $meta);
            }
        }

        return $this->validSuccessJsonResponse($message, $data);
    }

    protected function storeDB(Request $request, $ignoreRelations = false){

        $requestData = $this->setRequestUser($request, $request->validated());

        try {
            $instance = $this->repository->create($requestData);
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

        $originalName = $uploadedFile->getClientOriginalName();
        $filename = $this->getFilename($uploadedFile);
        $destination = $this->getStoragePath($relativePath);

        $this->moveUploadedFile($uploadedFile, $filename, $relativePath);

        $location = $filename;

        if(in_array($paramName.'_name', $model->getFillable())) $model->{$paramName.'_name'} = $originalName;
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
     * Handle relations.
     *
     * @param array    $fillable The relation fillable.
     * @param Model    $model    The eloquent model.
     * @param Relation $relation The eloquent relation.
     *
     * @return void
     */

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

    /**
     * HasOne relation updateOrCreate logic.
     *
     * @param array    $fillable The relation fillable.
     * @param Model    $model    The eloquent model.
     * @param Relation $relation The eloquent relation.
     *
     * @return Model | null
     */
    protected function updateOrCreateHasOne(array $fillable, Model $model, Relation $relation)
    {
        $id = '';
        $primaryKey = $relation->getModel()->getKeyName();
        if (array_key_exists($primaryKey, $fillable)) {
            $id = $fillable[$primaryKey];
        }
        if (Arr::except($fillable, [$primaryKey])) {
            if (property_exists($this, 'pruneHasOne') && $this->pruneHasOne !== false) {
                $relation->update($fillable);
            }
            return $relation->updateOrCreate([$primaryKey => $id], $fillable);
        }

        return null;
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
                if (Arr::except($fields, ['id'])) {
                    $record = $relation->updateOrCreate(['id' => $id], $fields);
                    array_push($keys, $record->id);
                    array_push($records, $record);
                }
            } else {
                if (Arr::except($fillable, ['id'])) {
                    $record = $relation->updateOrCreate(['id' => $id], $fillable);
                    array_push($keys, $record->id);
                    array_push($records, $record);
                }
            }

            if(isset($record)) $this->updateOrCreateRelations($fields, $record);

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
     * BelongsToOne relation updateOrCreate logic.
     *
     * @param array    $fillable The relation fillable.
     * @param Model    $model    The eloquent model.
     * @param Relation $relation The eloquent relation.
     *
     * @return Model
     */
    protected function updateOrCreateBelongsToOne(array $fillable, Model $model, Relation $relation)
    {
        $related = $relation->getRelated();


        if (array_key_exists('id', $fillable)) {
            $record = $relation->associate($related->find($fillable['id']));
            $model->save();
            return $record;
        }

        if (Arr::except($fillable, ['id'])) {
            if (!$relation->first()) {
                $record = $relation->associate($related->create($fillable));
                $model->save();
            } else {
                $record = $relation->update($fillable);
            }
            return $record;
        }

        return null;
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
            if (Arr::except($fields, ['id'])) {
                $record = $related->where($fields)->first();
                if (!$record) {
                    $record = $related->updateOrCreate(['id' => $id], $fields);
                }
                array_push($keys, $record->id);
                array_push($records, $record);
            }

            if(isset($record)) $this->updateOrCreateRelations($fields, $record);
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
        var_dump($relationName);
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

    /*private function setRequestUser(&$request) {
        $user = $request->user();
        if ($user) {
            $isAdmin = in_array('admin', $user->getRoleNames()->toArray());
            if ($this->repository->getRelationship('user') == 'BelongsTo') {
                if (!isset($request->user_id) || !$isAdmin || ($isAdmin && isset($request->user_id) && $request->user_id == $request->user()->id)) {
                    $request->merge(['user_id' => $request->user()->id]);
                }
            }
        }
    }*/

    private function setRequestUser(&$request, $fields, $model = null) {
        $user = $request->user();
        if ($user) {
            $isAdmin = in_array('admin', $user->getRoleNames()->toArray());
            if ($this->repository->getRelationship('user') == 'BelongsTo') {
                if(isset($model))  {
                    if(isset($fields['user_id']) && !$isAdmin) {
                        $fields['user_id'] = $model->user_id;
                    }
                }
                else if(!isset($fields['user_id']) || !$isAdmin || ($isAdmin && isset($fields['user_id']) && $fields['user_id'] == $request->user()->id)) {
                    $fields['user_id'] = $request->user()->id;
                }
            }
        }
        return $fields;
    }

    protected function executeTransaction($callback){
        DB::beginTransaction();
        try {
            $output = $callback();
        } catch (\Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            throw new ResourceControllerException($message);
        }
        DB::commit();

        return $output;
    }

}
