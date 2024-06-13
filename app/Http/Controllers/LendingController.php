<?php

namespace App\Http\Controllers;

use App\Helpers\APIFormatter;
use Illuminate\Http\Request;
use App\Models\lending;
use App\Models\stuff;
use App\Models\stuffStock;


class LendingController extends Controller
{
    public function __construct()
   {
    $this->middleware('auth:api');
   }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{
            $getLending = Lending::with('stuff', 'user')->get();

            return Apiformatter::sendResponse(200, 'succesfully Get All Lending Data', $getLending);
        } catch (\Exception $err) {
         return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            $this->validate($request, [
                'stuff_id' => 'required',
                'date_time' => 'required',
                'name' => 'required',
                'user_id' => 'required',
                'notes' => 'required',
                'total_stuff' => 'required',
            ]);

            $createLending = Lending::create([
                'stuff_id' => $request->stuff_id,
                'date_time' => $request->date_time,
                'name' => $request->name,
                'user_id' => $request->user_id,
                'notes' => $request->notes,
                'total_stuff' => $request->total_stuff,
            ]);

            $getStuffStock = stuffStock::where('stuff_id', $request->stuff_id)->first();
            $updateStock = $getStuffStock->update([
                'total_available' => $getStuffStock['total_available'] - $request->total_stuff,
            ]);

            return APiFOrmatter::sendResponse(200, 'Successfully Create A Lending Data', $createLending);
        } catch (\Exception $e) {
            return APIFormatter::sendResponse(400, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\lending  $lending
     * @return \Illuminate\Http\Response
     */
    public function show(lending $lending)
    {
        try{
            $getLending = Lending::where('id', $id)->with('stuff', 'user', 'restoration')->first();

            if (!$getLending){
                return APIFormatter::sendResponse(404, 'Data Lending Not Found');
        } else {
            return APIFormatter::sendResponse(200, 'SUccessfully Get A Lending Data');
        } 
    } catch (\Exception $e) {
        return APIFormatter::sendResponse(400, $e->getMessage());
    }
}

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\lending  $lending
     * @return \Illuminate\Http\Response
     */
    public function edit(lending $lending)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\lending  $lending
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, lending $lending)
    {
        try{
            $getLending = Lending::find('id');

            if ($getLending) {
                $this->validate($request, [
                    'stuff_id' => 'required',
                    'data_time' => 'required',
                    'name' => 'required',
                    'user_id' => 'required',
                    'notes' => 'required',
                    'total_stuff' => 'required',
                ]);

                // get stock berdasarkan request stuff id
                $getStuffStock = stuffStock::where('stuff_id', $request->stuff_id)->first(); 

                //get stock berdasarkan id lending
                $getCurrentStock = stuffStock::where('stuff_id', $getLending['stuff_id'])->first(); 

                if ($request->stuff_id == $getCurrentStock['stuff_id']) {
                    $updateStock = $getCurrentStock->update([
                        'total_available' => $getCurrentStock[ 'total_available'] + $getLending['total_stuff'] -
                        $request->total_stuff,
                    ]); // total available lama akan dijumlahkan dengan total peminjaman barang lama lalu dikurangkan dengan total peminjaman yan baru 
                } else {
                    $updateStock = $getCurrentStock->update([
                        'total_available' => $getCurrentStock[ 'total_available'] + $getLending['total_stuff'],
                    ]); // total available lama dijumlahkan dengan total pinjaman barang yang lama

                    $updateStock = $getCurrentStock->update([
                        'total_available' => $getStuffStock['total_available'] - $request['total_stuff'],
                    ]); // total available baru dikurangi dengan total pinjaman baru
                }

                $updateLending = $getLending->update([
                    'stuff_id' => $request->stuff_id,
                    'data_time' => $request->data_time,
                    'name' => $request->name,
                    'user_id' => $request->user_id,
                    'notes' => $request->notes,
                    'total_stuff' => $request->total_stuff,
                ]);

                $getUpdateLending = Lending::where('id', $id)->with('stuff', 'user', 'restoration')->first();

                return APIFormatter::sendResponse(200, 'Successfully Update A Lending Data', $getUpdateLending);
            }
        } catch (\Exception $e) {
            return APIFormatter::sendResponse(400, $e->getMessge());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\lending  $lending
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            $getLending = Lending::find($id);

            if (!$getLending) {
                return APIFormatter::sendResponse(404, false, 'Data Lending Not Found');
            } else {
                $addStock = StuffStock::where('stuff_id', $getLending['stuff_id'])->first();
                $updateStock = $addStock->update([
                    'total_available' => $addStock['total_available'] + $getLending['total_stuff'],
                ]);

                $deleteLending = $getLending->delete();

                if ($deleteLending && $updateStock) {
                    return APIFormatter::sendResponse(200, true, 'Successfully Delete A Lending Data');
                }
            }
        } catch (\Exception $e) {   
            return APIFormatter::sendResponse(400, false, $e->getMessage());
        }
    }

    public function recycleBin()
    {
        try {

            $lendingDeleted = Lending::onlyTrashed()->get();

            if (!$lendingDeleted) {
                return APIFormatter::sendResponse(404, false, 'Deletd Data Lending Doesnt Exists');
            } else {
                return APIFormatter::sendResponse(200, true, 'Successfully Get Delete All Lending Data', $lendingDeleted);
            }
        } catch (\Exception $e) {
            return APIFormatter::sendResponse(400, false, $e->getMessage());
        }
    }

    public function restore($id)
    {
        try {

            $getLending = Lending::onlyTrashed()->where('id', $id);

            if (!$getLending) {
                return APIFormatter::sendResponse(404, false, 'Restored Data Lending Doesnt Exists');
            } else {
                $restoreLending = $getLending->restore();

                if ($restoreLending) {
                    $getRestore = Lending::find($id);
                    $addStock = StuffStock::where('stuff_id', $getRestore['stuff_id'])->first();
                    $updateStock = $addStock->update([
                        'total_available' => $addStock['total_available'] - $getRestore['total_stuff'],
                    ]);

                    return APIFormatter::sendResponse(200, true, 'Successfully Restore A Deleted Lending Data', $getRestore);
                }
            }
        } catch (\Exception $e) {
            return APIFormatter::sendResponse(400, false, $e->getMessage());
        }
    }

    public function deletePermanent($id)
    {
        try {

            $getLending = Lending::onlyTrashed()->where('id', $id);

            if (!$getLending) {
                return APIFormatter::sendResponse(404, false, 'Data Lending for Permanent Delete Doesnt Exists');
            } else {
                $forceStuff = $getLending->forceDelete();

                if ($forceStuff) {
                    return APIFormatter::sendResponse(200, true, 'Successfully Permanent Delete A Lending Data');
                }
            }
        } catch (\Exception $e) {
            return APIFormatter::sendResponse(400, false, $e->getMessage());
        }
    }
}
