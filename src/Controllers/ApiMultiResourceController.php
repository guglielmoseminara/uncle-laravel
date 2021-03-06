<?php

namespace UncleProject\UncleLaravel\Controllers;

use Illuminate\Http\Request;
use League\Fractal\Resource\Item;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use UncleProject\UncleLaravel\Classes\BaseSpatieSearch;
use UncleProject\UncleLaravel\Traits\ControllerHelper;
use UncleProject\UncleLaravel\Classes\BaseRequestParser;
use App;


class ApiMultiResourceController {

    use ControllerHelper;

    /**
     * Display a listing of the resource.
     *
     * @param Request $request The request object.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $this->getFormRequestInstance();
        $searchResults = (new BaseSpatieSearch());
        $types = $request->get('type') ? explode('|', $request->get('type')) : [];
        foreach($this->resources as $km => $vm) {
            $searchQuery = BaseRequestParser::parserSearchData($request->get('model'));
            if (count($types) == 0 || in_array(strtolower($km), $types)){
                $resourceName = isset($vm['resource']) ? $vm['resource'] : $km;
                $modelClass = App::make($resourceName.'Resource')->getModelClassPath($vm['model']);
                if (isset($vm['where'])) {
                    $whereQuery = [];
                    foreach ($vm['where'] as $where) {
                        $whereQuery[$where[0]] = ($where[1] == '!=' ? '!' : '').$where[2];
                    }
                    $searchQuery = array_merge($searchQuery, $whereQuery);
                }
                if (isset($vm['group_by'])) {
                    $searchQuery['group_by'] = ['group_by', $vm['group_by']];
                }
                if (isset($vm['scopes'])) {
                    $searchQuery['scopes'] = ['scopes', $vm['scopes']];
                }
                if (isset($vm['filter_text'])) {
                    $searchQuery['filter_text'] = ['filter_text', $vm['filter_text']];
                }
                $vm['fields'][] = $searchQuery;
                $searchResults = $searchResults->registerModel($modelClass, $vm['fields']);    
            }
        }
        $searchValue = '';
        if($request->has('search') && !empty($request->input('search'))) {
            $searchValue = $request->input('search');
        }
        $searchResults = $searchResults->search($searchValue);
        $manager = new Manager();
        $collection = [];
        foreach($searchResults as $key => $value) {
            $resourceIndex = ucfirst($value->type);
            $issetResource = isset($this->resources[$resourceIndex]);
            $issetPresenter = isset($this->resources[$resourceIndex]['presenter']);
            if($issetResource && $issetPresenter) {
                $presenterClass = App::make($resourceIndex.'Resource')->getPresenterClassPath($this->resources[$resourceIndex]['presenter']);
                $presenter = new $presenterClass();
                $resource = new Item($value->searchable, $presenter->getTransformer());
                $suggestion = array(
                    'type' => $value->type,
                    'model' => $manager->createData($resource)->toArray()['data']
                );
                $collection[] = $suggestion;
            } else {
                $suggestion = array(
                    'type' => $value->type,
                    'model' => $value->searchable
                );
                $collection[] = $suggestion;
            }
        }
        return $this->validSuccessJsonResponse('Success', $collection);
    }

    /**
     * Get the FormRequest instance.
     *
     * @return mixed
     */
    public function getFormRequestInstance()
    {
        if (!$this->formRequest) {
            return new FormRequest;
        }
        return app()->make($this->formRequest);
    }
}
