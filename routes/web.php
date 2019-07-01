<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->post('/register','AuthController@register');
$router->post('/login','AuthController@login');

$router->group(['prefix' => 'checklist','middleware' => 'auth'], function () use ($router) {
        $router->get('/','CheklistController@index');
        $router->get('/template','CheklistController@indexTemplate');
        $router->get('/template/{id}','CheklistController@indexTemplatebyId');
        $router->get('/{id}','CheklistController@show');
        $router->get('/{id}/items','CheklistController@checklistItemById');
        $router->get('/{id}/items/{itemId}','CheklistController@ItemById');
        $router->get('/items/summary','CheklistController@summaryItem');
        $router->post('/','CheklistController@store');
        $router->post('/{id}/items','CheklistController@storeItem');
        $router->post('/{id}/items/_bulk','CheklistController@bulkUpdateItem');
        $router->post('/complete','CheklistController@completeItem');
        $router->post('/incomplete','CheklistController@incompleteItem');
        $router->post('/templates','CheklistController@storeTemplate');
        $router->put('/','CheklistController@update');
        $router->patch('/{id}/items/{itemId}','CheklistController@updateItem');
        $router->patch('/template/{id}','CheklistController@updateTemplate');
        $router->delete('/{id}','CheklistController@delete');
        $router->delete('/template/{id}','CheklistController@deleteTemplate');
        $router->delete('/{id}/items/{itemId}','CheklistController@deleteItem');
    });
