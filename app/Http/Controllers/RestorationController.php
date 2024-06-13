<?php

namespace App\Http\Controllers;

use App\Helpers\APIFormatter;
use App\Models\restoration;
use App\Models\lending;
use App\Models\stuffStock;
use Illuminate\Http\Request;

class RestorationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        try {
            $this->validate($request, [
                'user_id' => 'required',
                'lending_id' => 'required',
                'date_time' => 'required',
                'total_good_stuff' => 'required',
                'total_defec_stuff' => 'required',
            ]);

            $getLending = Lending::where('id', $request->lending_id)->first(); // get data peminjaman yang sesuai dnegan pengembaliannya

            $totalStuff = $request->total_good_stuff + $request->total_defec_stuff; // variabel penampug jumalah barang yang akan dikembalikan

            if ($getLending['total_stuff'] != $totalStuff) { // pengecekan apakah jumlah barang yang dipinjam jumlahnya sama atau tidak 
                return APIFormatter::sendResponse(400, false, 'The amound of items returned does not match the amound borrowed');
            } else {
                $getStuffStock = stuffStock::where('stuff_id', $getLending['stuff_id'])->first(); // get data stuff yang barangnya sedang dipinjam

                $createRestoration = Restoration::create([
                    'user_id' => $request->user_id,
                    'lending_id' => $request->lending_id,
                    'date_time' => $request->date_time,
                    'total_good_stuff' => $request->total_good_stuff,
                    'total_defec_stuff' => $request->total_defec_stuff,
                ]);

                $updateStock = $getStuffStock->update([
                    'total_available' => $getStuffStock['total_available'] + $request->total_good_stuff, 
                    'total_defec' => $getStuffStock['total_defec'] + $request->total_defec_stuff,
                ]); // update jumlah barang yang tersedia yang ditambahkan dengan jumlah barang bagus yang dikembalikan dan update jumlah barang yang rusak ditambah dengan jumlah barang rusak yang dikembalikan 

                if ($createRestoration && $updateStock) {
                    return APIFormatter::sendResponse(200, 'Successfully Creat A Restoration Data', $createRestoration);
                }
            } 
        } catch (Exception $e) {
            return APIFormatter::sendResponse(400, false, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\restoration  $restoration
     * @return \Illuminate\Http\Response
     */
    public function show(restoration $restoration)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\restoration  $restoration
     * @return \Illuminate\Http\Response
     */
    public function edit(restoration $restoration)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\restoration  $restoration
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, restoration $restoration)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\restoration  $restoration
     * @return \Illuminate\Http\Response
     */
    public function destroy(restoration $restoration)
    {
        //
    }

    public function __construct()
{
    $this->middleware('auth:api');
}
}
