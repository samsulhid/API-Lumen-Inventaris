<?php

namespace App\Http\Controllers;

use App\Helpers\APIFormatter;
use App\Models\stuffStock;
use Illuminate\Http\Request;

class StuffStockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{
            $getStuffStock =  stuffStock::with('stuff')->get();

            return APIFormatter::sendResponse(200, 'Successfully get all stuff stock data', $getStuffStock);
        } catch (\Exception $e){
            return APIFormatter::sendResponse(400, false, $e->getMessage());
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\stuffStock  $stuffStock
     * @return \Illuminate\Http\Response
     */
    public function show(stuffStock $stuffStock)
    {
        try {
            $data = User::with('stuff')->where('id', $id)->first();

            if (is_null($data)) {
                return APIFormatter::sendResponse(400, 'bad request', 'Data not found');
            } else {
                return APIFormatter::sendResponse(200, 'success', $data);
            }
        } catch (\Exception $err) {
            return APIFormatter::sendResponse(400, 'bad request', $err->getMesaage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\stuffStock  $stuffStock
     * @return \Illuminate\Http\Response
     */
    public function edit(stuffStock $stuffStock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\stuffStock  $stuffStock
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, stuffStock $stuffStock)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\stuffStock  $stuffStock
     * @return \Illuminate\Http\Response
     */
    public function destroy(stuffStock $stuffStock)
    {
        try {
            $chekProses = Stuff::where('id', $id)->delete();

            return APIFormatter::sendResponse(200, 'Success', 'Data deleted successfully');
        } catch (\Exception $err) {
            return APIFormatter::sendResponse(400, 'Bad request', $err->getMessage());
        }
    }

    public function deletePermanent($id) {
        try {
            $data = Stuff::onlyTrashed()->where($id)->forceDelete();

            return APIFormatter::sendResponse(200, 'success', 'Data deleted stuff stock successfully');
        } catch (\Exception $err) {
            return APIFormatter::sendResponse(400, 'Bad request', $err->getMessage());
        }
    }

    public function trash()
    {
        try {
            $data = Stuff::onlyTrashed()->get();

            return APIFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return APIFormatter::sendResponse(400, 'bad request', $err->gatMessage());
        }
    }

    public function restore($id) {
        try {
            $chekProses = stuff::onlyTrashed()->where('id', $id)->restore();

            if ($checkProses) {
                $data = Stuff::find($id);
                return APIFormatter::sendResponse(200, 'success', $data);
            } else {
                return APIFormatter::sendResponse(400, 'bad request', 'Gagal mengembalikan data!');
            }
        }catch (\Exception $err) {
            return APIFormatter::sendResponse(500, 'bad requst', $err->getMessage());
        }
    }

    public function addStock(Request $request, $id) 
    {
        try {

            $getStuffStock = stuffStock::find($id);

            if (!$getStuffStock) {
                return APIFormatter::sendResponse(404, 'Data Stuff stock not found');
            } else {
                $this->validate($request, [
                    'total_available' => 'required',
                    'total_defec' => 'required'
                ]);

                $addStock = $getStuffStock->update([
                    'total_available' => $getStuffStock['total_available'] + $request->total_available,
                    'total_defec' => $getStuffStock['total_defec'] + $request->total_defec,
                ]);

                if ($addStock) {
                    $getStockAdded = stuffStock::where('id', $id)->with('stuff')->first();
                    
                    return APIFormatter::sendResponse(200, 'Successfully add a stock of stuff stock data',  $getStockAdded);
                }
            }
        } catch (\Exception $e) {
            return APIFormatter::sendResponse(400, 'Bad request', $e->getMessage());
        }
    }

    public function subStock(Request $request, $id)
    {
        try{
            $getStuffStock = stuffStock::find($id);

            if (!$getStuffStock) {
                return APIFormatter::sendResponse(404, false, 'Data stuff stock not found');
            } else {
                $this->validate($request, [
                    'total_available' => 'required',
                    'total_defec' => 'required'
                ]);

                    $iStockAvailable = $getStuffStock['total_available'] - $request->total_available;
                    $iStockDefec = $getStuffStock['total_defec'] - $request->total_defec;

                    if ($isStockAvailable < 0 || $isStockDefec < 0) {
                        return APIFormatter::sendResponse(200, 'A substraction stock cant less than a stock storeed');
                    } else {
                        $subStock = $getStuffStock->update([
                            'total_available' => $isStockAvailable,
                            'total_defec' => $isStockDefec,
                        ]);

                        if ($subStock){
                            $getStuffStock = stuffStock::where('id', $id)->with('stuff')->first();

                            return APIFormatter::sendResponse(200, 'Successfully sub a stock of stuff stock data', $subStock);
                        }
                    }
            }
        } catch (\Exception $e) {
            return  APIFormatter::sendResponse(500, false, "Bad request", $e->getMessage());
        }
    }

        public function __construct()
    {
        $this->middleware('auth:api');
    }
}
