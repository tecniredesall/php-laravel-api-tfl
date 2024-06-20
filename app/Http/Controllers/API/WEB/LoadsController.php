<?php

namespace App\Http\Controllers\API\WEB;
use Illuminate\Http\Request;
use App\Http\Requests\API\WEB\LoadsRequest;
use App\Models\CmodityLoads;
use App\Http\Controllers\Controller;
class LoadsController extends Controller
{
    public function index( LoadsRequest  $request)
    {
        $loads= $request->getAllLoads();
        return response()->json($loads,200);
    }

    public function store(LoadsRequest $request)
    {
        return response()->json($request->updateLoads());
    }


    public function show($id)
    {
        return  new JsonResource(CmodityLoads::find($id));
    }


    public function update( $id,Request $request)
    {
        $load= CmodityLoads::find($id);
        if($load){
            $load->was_processed=1;
            $load->had_error=$request->input('error',0);
            $load->process_date= date('Y-m-d h:is');
            $load->save();

        }
        return response()->json($load);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
