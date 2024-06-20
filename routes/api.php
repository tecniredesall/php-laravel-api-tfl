<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get( '/version', function( ){
    return "V.2.0.4";
});
Route::post( '/sign', 'API\Sign@in' );
Route::post( '/signout', 'API\Sign@out' );
Route::post( '/reset', 'API\Sign@reset' );
Route::post( '/isonline', 'API\Sign@isactive' );
Route::post( '/crypt', function( Request $request ){
    $f = isset( $request->out ) ? $request->out : 0;
    $id = isset( $request->id ) ? $request->id : 0;
    $email = isset( $request->email ) ? $request->email : '';
    return \App\Api::sendResetPass( $id, ucfirst( $request->model ), $f, $email );
});
Route::get( '/version', function(){
    return "2.0";
});
Route::get( '/resetp', 'API\Sign@resetp' );
Route::any('reports/download/pdf/{id}','API\WEB\Reports@getPDF')->name('download-pdf');
Route::any('reports/download/xlsx/{id}','API\WEB\Reports@getXLSX')->name('download-xlsx');

/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| BEGIN API - WEB
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
*/
Route::middleware( 'auth:api' )->namespace( 'API\WEB' )->prefix( 'web' )->group( function(){

    /*
    |--------------------------------------------------------------------------
    | Web service
    |--------------------------------------------------------------------------
    |
    */
    Route::get( '/dashboard/commodity-stocks', 'ComoditiesStock@index' );
    Route::get( '/dashboard/tickets/{where}', 'OpenTickets@tickets' );
    Route::get( '/dashboard/location/{id}', 'ComoditiesStock@location' );
    Route::get( '/dashboard/tank/{id}', 'TanksStock@byTank' );

    Route::get( 'open-tickets/{lid}/detail/{tkid}', 'OpenTickets@detail' )->middleware( 'candoit:3' );
    Route::resource( 'open-tickets', 'OpenTickets', [ 'except' => [ 'create', 'edit' ] ] )->middleware( 'candoit:3' );
    Route::resource( 'commodity-stocks', 'ComoditiesStock', [ 'except' => [ 'create', 'edit' ] ] )->middleware( 'candoit:4' );
    Route::resource( 'sb-stocks', 'SbStocks', [ 'except' => [ 'create', 'edit', 'store', 'update', 'destroy' ] ] );
    Route::resource( 'tank-stocks', 'TanksStock', [ 'except' => [ 'create', 'edit' ] ] )->middleware( 'candoit:5' );

    Route::resource( 'contracts', 'Contracts', [ 'except' => [ 'create', 'edit' ] ] )->middleware( 'candoit:8' );
    Route::get( 'related/{id}', 'Contracts@related' )->middleware( 'candoit:8' );
    Route::post( 'contractFeature', 'Contracts@contractFeature' )->middleware( 'candoit:8' );
    Route::post( 'ticketsContract', 'Contracts@ticketsContract' )->middleware( 'candoit:8' );
    Route::post( 'batchContract', 'Batch@index' )->middleware( 'candoit:8' );
   // Route::get( 'batchTicket/{batch_id}', 'Batch@batchTicket' )->middleware( 'candoit:8' );
    //Route::get( 'featuresValue/{batch_id}', 'Batch@featuresValue' )->middleware( 'candoit:8' );
    Route::post( 'storeValuesFeatures', 'Contracts@storeValuesFeatures' )->middleware( 'candoit:8' );
    Route::get('reports/{use_id}', 'Reports@index');
    Route::post( 'sendmailPrepprove', 'Batch@sendmailPrepprove' )->middleware( 'candoit:8' );
    Route::post( 'generateFiles', 'Batch@generateFiles' )->middleware( 'candoit:8' );
    Route::post( 'linkTickets', 'Contracts@linkTickets' )->middleware( 'candoit:8' );
    Route::get( 'batchDetail/{batch_id}', 'Batch@batchDetail' )->middleware( 'candoit:8' );
    Route::post( 'changeStatusBatch', 'Batch@changeStatusBatch' )->middleware( 'candoit:8' );
    Route::post( 'ticketsBatch', 'Batch@ticketsBatch' )->middleware( 'candoit:8' );
    Route::post( 'attachTicketsBatch', 'Batch@attachTicketsBatch' )->middleware( 'candoit:8' );
    Route::post( 'deleteTicketsBatch', 'Batch@deleteTicketsBatch' )->middleware( 'candoit:8' );
    Route::get( 'deleteBatch/{batch_id}', 'Batch@deleteBatch' )->middleware( 'candoit:8' );
    Route::post('reports/{report_id}/{lang}/{format}', 'Reports@reports');
    //Route::post('reports_generate', 'Reports@reports_generate');
    Route::post('reports_farms', 'Reports@farms');
    Route::post('reports_seller_farms', 'Reports@seller_farms');
    Route::post('company_info', 'Company@company_info');
    Route::get('get_company_info', 'Company@get_company_info');
    Route::get('getSellers', 'LinkedSeller@getSellers');
    Route::post( 'linked', 'LinkedSeller@linked' );
    Route::get('getInstance', 'Instances@getInstance');
    /*Route::get('getData', 'ProdTanks@getData');
    Route::get('getVirtualTanks/{id}', 'Production_Tanks@getVirtualTanks');
    Route::get('getTanksCommodity/{id}', 'Production_Tanks@getTanksCommodity');
    Route::get('getTanksTransformationType/{id}', 'Production_Tanks@getTanksTransformationType');
    Route::get('getMeasurementUnitDefault/{id}', 'Production_Tanks@getMeasurementUnitDefault');
    Route::get('getCommodities', 'ProdTanks@getCommodities');
    Route::resource( 'prodTanks', 'ProdTanks', [ 'except' => [ 'create', 'edit' ] ] );
    Route::get('getTransformationsTypes', 'Production_Tanks@getTransformationsTypes');
    Route::resource( 'productionTanks', 'Production_Tanks', [ 'except' => [ 'create', 'edit' ] ] );
    Route::resource( 'transformationsTypes', 'TransformationsTypes', [ 'except' => [ 'create', 'edit' ] ] );
    Route::resource( 'processes', 'Processes', [ 'except' => [ 'create', 'edit' ] ] );
    Route::resource('commoditiesTransformation', 'CommodityTransformations', ['except' => ['create', 'edit']]);
    Route::resource('prodCommodities', 'ProdCommodities', ['except' => ['create', 'edit']]);
    Route::get('getCertificationsFarm/{id}', 'CertificationsFarms@getCertificationsFarm');*/

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    |
    */
    Route::resource( 'users', 'Users', [ 'except' => [ 'create', 'edit' ] ] )->middleware( 'candoit:4' );
    Route::post( 'security', 'Users@securityGrant' );

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    */
    Route::resource( 'permissions', 'Permissions', [ 'except' => [ 'create', 'edit' ] ] );

    /*
    |--------------------------------------------------------------------------
    | Sellers
    |--------------------------------------------------------------------------
    |
    */
    Route::post( 'sellers/{id}/{email}/reset', 'Sellers@reset' )->middleware( 'candoit:7' );
    Route::resource( 'sellers', 'Sellers', [ 'except' => [ 'create', 'edit' ] ] )->middleware( 'candoit:7' );

    /*
    |--------------------------------------------------------------------------
    | Farms
    |--------------------------------------------------------------------------
    |
    */
    Route::resource( 'farms', 'Farms', [ 'except' => [ 'create', 'edit' ] ] )->middleware( 'candoit:7' );

    /*
    |--------------------------------------------------------------------------
    | Buyers
    |--------------------------------------------------------------------------
    |
    */
    Route::resource( 'buyers', 'Buyers', [ 'except' => [ 'create', 'edit' ] ] )->middleware( 'candoit:6' );
    /*
    |--------------------------------------------------------------------------
    | Locations
    |--------------------------------------------------------------------------
    |
    */
    Route::resource( 'location', 'Locations', [ 'except' => [ 'create', 'edit' ] ] )->middleware( 'candoit:27' );

    /*
    |--------------------------------------------------------------------------
    | Commodities
    |--------------------------------------------------------------------------
    |
    */
    Route::resource( 'commodities', 'Commodities', [ 'except' => [ 'create', 'edit' ] ] )->middleware( 'candoit:4' );
    /*
    |--------------------------------------------------------------------------
    | Commodities General
    |--------------------------------------------------------------------------
    |
    */
    Route::get( 'getCommoditiesGeneral', 'CommoditiesGeneral@getCommoditiesGeneral' )->middleware( 'candoit:4' );
    Route::get( 'listCommoditiesGeneral/', 'CommoditiesGeneral@listCommoditiesGeneral' )->middleware( 'candoit:4' );
    Route::put( 'CommoditiesGeneral/', 'CommoditiesGeneral@updateCommoditiesGeneral' )->middleware( 'candoit:4' );

    /*
       |--------------------------------------------------------------------------
       | CommoditiesFeatures
       |--------------------------------------------------------------------------
       |
       */
    Route::resource( 'commoditiesFeatures', 'CommoditiesFeatures', [ 'except' => [ 'create', 'edit' ] ]);

    /*
    |--------------------------------------------------------------------------
    | tanks
    |--------------------------------------------------------------------------
    |
    */
    Route::post( 'tanks/{id}/reset', 'Tanks@reset' )->middleware( 'candoit:14' );
    Route::resource( 'tanks', 'Tanks', [ 'except' => [ 'create', 'edit' ] ] )->middleware( 'candoit:5' );
    Route::post( 'delete_tanks', 'Tanks@delete_tanks' )->middleware( 'candoit:5' );

    /*
    |--------------------------------------------------------------------------
    | Locations
    |--------------------------------------------------------------------------
    |
    */
    Route::get( '/locations', function(){ return \App\Locations::where( 'status', 1 )->get(); });

    /*
    |--------------------------------------------------------------------------
    | Tickets
    |--------------------------------------------------------------------------
    |
    */
    Route::post( '/ticket/{where}', 'Ticket@search' );
    Route::post( '/ticket/{where}/revert', 'Ticket@revert' )->middleware( 'candoit:21' );
    //Route::resource( 'ticket', 'Ticket', [ 'except' => [ 'index', 'show', 'store', 'update', 'destroy' ] ] );

    /*
    |--------------------------------------------------------------------------
    | Seasons
    |--------------------------------------------------------------------------
    |
    */
    Route::post( 'season', 'Seasons@change' );
});
/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| END API - WEB
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| BEGIN API - MOBILE
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
*/
Route::middleware(['secret-token'])->namespace( 'API\MOBILE' )->prefix( 'mobile' )->group( function(){
    Route::get( 'ticket/download/{type}/{central_id}/{branch_id}', 'OpenTickets@getTicket' );
});

Route::middleware(['secret-token'])->namespace( 'API\WEB' )->prefix( 'web' )->group( function(){
    Route::resource( 'loads', 'LoadsController' );
});


Route::middleware( 'auth:api' )->namespace( 'API\MOBILE' )->prefix( 'mobile' )->group( function(){

    /*
    |--------------------------------------------------------------------------
    | Mobile service
    |--------------------------------------------------------------------------
    |
    */
    Route::get( 'open-tickets/{lid}/detail/{tkid}', 'OpenTickets@detail' )->middleware( 'candoit:3' );
    Route::resource( 'open-tickets', 'OpenTickets', [ 'except' => [ 'create', 'edit', 'store', 'update', 'destroy' ] ] )->middleware( 'candoit:3' );
    Route::get( 'commodity-stocks/{cid}/{lid}/tanks/{tid?}', 'ComoditiesStock@tanks' )->middleware( 'candoit:4' );
    Route::resource( 'commodity-stocks', 'ComoditiesStock', [ 'except' => [ 'create', 'edit', 'store', 'update', 'destroy' ] ] )->middleware( 'candoit:4' );
    Route::get( 'stocks-sb', 'SbStocks@clients' );
    // Route::get( 'stocks-sb/{id}', 'SbStocks@client' );
    Route::resource( 'sb-stocks', 'SbStocks', [ 'except' => [ 'create', 'edit', 'store', 'update', 'destroy' ] ] );
    // Route::resource( 'tank-stocks', 'TanksStock', [ 'except' => [ 'create', 'edit', 'store', 'update', 'destroy' ] ] )->middleware( 'candoit:5' );
    //Route::get( 'open-tickets/{lid}/detail/{tkid}', 'OpenTickets@detail' )->middleware( 'candoit:3' );

    /*
    |--------------------------------------------------------------------------
    | Sellers
    |--------------------------------------------------------------------------
    |
    */
    Route::resource( 'sellers', 'Sellers', [ 'except' => [ 'create', 'edit', 'store', 'update', 'destroy' ] ] )->middleware( 'candoit:7' );

    /*
    |--------------------------------------------------------------------------
    | Buyers
    |--------------------------------------------------------------------------
    |
    */
    Route::resource( 'buyers', 'Buyers', [ 'except' => [ 'create', 'edit', 'store', 'update', 'destroy' ] ] )->middleware( 'candoit:6' );

    /*
    |--------------------------------------------------------------------------
    | Silos
    |--------------------------------------------------------------------------
    |
    */
    Route::resource( 'silos', 'Silos', [ 'except' => [ 'create', 'edit', 'store', 'update', 'destroy' ] ] )->middleware( 'candoit:5' );

    /*
    |--------------------------------------------------------------------------
    | Seasons
    |--------------------------------------------------------------------------
    |
    */
    //Route::post( 'season', 'Seasons@change' );

    /*
    |--------------------------------------------------------------------------
    | Locations
    |--------------------------------------------------------------------------
    |
    */
    Route::get( '/locations', function(){ return \App\Locations::where( 'status', 1 )->get(); });

    /*
    |--------------------------------------------------------------------------
    | Tickets
    |--------------------------------------------------------------------------
    |
    */
    Route::post( '/ticket/{where}', 'Ticket@search' );
    Route::post( '/open_tickets', 'OpenTickets@open_tickets' );
    Route::post( '/ticket/{where}/revert', 'Ticket@revert' )->middleware( 'candoit:21' );
    //Route::resource( 'ticket', 'Ticket', [ 'except' => [ 'index', 'show', 'store', 'update', 'destroy' ] ] );
    Route::post('reports/{report_id}/{lang}/{format}', 'Reports@reports');
    Route::resource('reports', 'Reports');
    Route::post('reports_seller_farms', 'Reports@seller_farms');
    Route::get('tickets_count', 'OpenTickets@tickets_count');

});
/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| END API - MOBILE
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
*/