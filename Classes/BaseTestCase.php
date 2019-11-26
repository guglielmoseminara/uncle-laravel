<?php

namespace UncleProject\UncleLaravel\Classes;

use Tests\TestCase;
use App;

abstract class BaseTestCase extends TestCase
{
    protected function createUserForRole($email,$role){

        $userRepository = App::make('UsersResource')->getRepository('User');
        $user = $userRepository->create(['email' => $email, 'password' => '12345678']);
        $user->assignRole($role);
        $credentials = [
            'email' => $email,
            'password' => '12345678'
        ];

        if($role == 'user') {
            $token = $this->loginUser($credentials, 'token');
            $userProfileClass = App::make('UsersResource')->getModelClassPath('UserProfile');
            $userProfile = factory($userProfileClass, 1)->make()[0];
            $user->profile()->create($userProfile->toArray());
            $response = $this->post('api/users/profile', $userProfile->toArray(), [
                'Authorization' => 'Bearer '.$token
            ]);
        }

        return $credentials;
    }

    protected function loginUser($credentials, $return = null){
        $response = $this->post('api/auth/login', $credentials);
        switch($return)
        {
            case 'token':
                return $response->json()['results']['data']['token'];
                break;

            case 'user':
                return $response->json()['results']['data']['user'];
                break;

            default:
                return $response->json()['results']['data'];
                break;

        }

    }

    protected function requestWithTest($method,$url, $params, $header = [], $code = null, $assertJsonStructure = null){

        if($method == 'GET') {
            $response = $this->get($url, $header);
        }

        if($method == 'POST') {
            $response = $this->post($url, $params, $header);
        }

        if($method == 'PUT') {
            $response = $this->put($url, $params, $header);
        }

        if($method == 'DELETE') {
            $response = $this->delete($url, $params, $header);
        }

        if($code)
        {
            if($code == 200)
            {
                $response->assertJson(['code' => '200'])
                    ->assertJsonStructure($assertJsonStructure)
                    ->isOk();
            } else {
                $response->assertJson(['code' => $code])
                    ->assertStatus($code);
            }

        }

        return $response;

    }

    public function createSearchQueryString($searchFields) {
        return implode(';', array_map(function($fieldKey) use ($searchFields){
            return $fieldKey.':'.$searchFields[$fieldKey];
        }, array_keys($searchFields)));
    }

    public function getRepository($resource, $model) {
        $resourceInstance = App::make($resource.'Resource');
        $repository = $resourceInstance->getRepository($model);
        App::forgetInstance($resource.'Resource');
        return $repository;
    }

    public function getModelKeys($resource, $model, $presenter = null) {
        if (!$presenter) {
            $presenter = $model;
        }
        $row = $this->getRepository($resource, $model)->firstWithPresenter($presenter);
        $keys = array_keys(get_object_vars($row));
        return $keys;
    }

    public function checkListConditions($rows, $conditions, $conditionType = null) {
        foreach($rows as $row) {
            foreach($conditions as $conditionKey => $conditionValue) {
                $value = array_get($row, $conditionKey);
                if (is_array($conditionValue) && count($conditionValue) == 2) {
                    $this->assertTrue($value !== null && $value >= $conditionValue[0] && $value <= $conditionValue[1]);
                } else if (is_array($value)) {
                    $this->assertTrue($value !== null && in_array($conditionValue, $value));
                } else if ($conditionType == 'like') {
                    $this->assertTrue(strstr($value, $conditionValue) !== FALSE);
                }
                else {
                    $this->assertTrue($value !== null && $value == $conditionValue);
                }
            }
        }
    }

    public function getResponseData($response) {
        return json_decode($response->getContent())->results->data;
    }

    public function getResponseDataAsArray($response) {
        return json_decode($response->getContent(), true)['results']['data'];
    }

    public function getResponseMeta($response) {
        return json_decode($response->getContent())->results->meta;
    }

    public function getResponseMetaAsArray($response) {
        return json_decode($response->getContent(), true)['results']['meta'];
    }

    public function assertResponseList($response, $modelKeys) {
        $response->assertJson([
            'code' => '200',
            'message' => 'Success'
        ])->assertJsonStructure([
            'results' => [
                'data' => [
                    '*' => $modelKeys
                ],
                'meta'
            ]
        ])->isOk();
    }

    public function assertResponseDetail($response, $modelKeys) {
        $response->assertJson([
            'code' => '200',
            'message' => 'Success'
        ])->assertJsonStructure([
            'results' => [
                'data' => $modelKeys
            ]
        ])->isOk();
    }

    public function assertIsUpdated ($response, $sendRequest){
        $result = $this->getResponseData($response);
        foreach($sendRequest as $key => $value)
        {
            $this->assertTrue($result->$key == $value);
        }

    }

    public function assertStoreElementExist ($indexResponse, $storeResponse){
        $indexResult = $this->getResponseData($indexResponse);
        $storeResult = $this->getResponseData($storeResponse);
        foreach($indexResult as $result)
        {
            if($result->id == $storeResult->id)
            {
                $this->assertTrue($result->id == $storeResult->id);
            }
        }

    }

    public function assertResponseListKeyEqualValue ($response, $key, $value){
        $results = $this->getResponseData($response);
        foreach($results as $result)
        {
            $this->assertTrue($result->$key == $value);
        }
    }

    public function assertResponseKeyEqualValue ($response, $key, $value){
        $productResult = $this->getResponseData($response);
        $this->assertTrue($productResult->$key == $value);
    }
}