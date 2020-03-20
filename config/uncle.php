<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    'resources' => [
        //Add Resource - Uncle Comment (No Delete)

    ],

    'uploadable' => [
        'url' => 'api/{resource}/{id}/images/{imageName}',
        'path' => 'uploads/{resource}/{id}/{imageName}',
        'testingPath' => '{resource}/{id}/{imageName}'
    ],

    /*
    |--------------------------------------------------------------------------
    | Project Commands
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    'project_commands' => [



    ]
];
