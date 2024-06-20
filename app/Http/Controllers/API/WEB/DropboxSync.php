<?php

namespace App\Http\Controllers\API\WEB;

use App\Http\Requests\API\WEB\DropboxSyncRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;

class DropboxSync extends Controller
{

    public function index(DropboxSyncRequest $request)
    {

        $header = [
            'Content-Type' => 'text/plain',
            'X-Content-Type-Options' => 'nosniff'

        ];
        Log::channel('slack')->info($request->all());
        return response($request->input('challenge'), 200, $header);

    }

    public function store(Request $request)
    {
        Log::channel('slack')->info(json_encode($request->all()));
        $dropbox = new Dropbox(new DropboxApp(env('DROPBOX_APP_KEY'), env('DROPBOX_APP_SECRET'), env('DROPBOX_APP_TOKEN')));
        return response()->json($request->all());

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
