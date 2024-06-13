<?php

namespace App\Http\Controllers;

use App\Helpers\APIFormatter;
use App\Models\stuff;
use Illuminate\Http\Request;

class StuffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $stuffs = Stuff::with('stuffStock','inboundstuff','lendings')->get(); // Menadapatkan keseluruhan data dari tabel stuffs

            return APIFormatter::sendResponse(200, true, 'Successfully Get All Stuff Data', $stuffs);
        } catch (\Exception $e) { // Exception adalah objek yang menjelaskan kesalahan atau perilaku tak terduga dari skrip PHP
            return APIFormatter::sendResponse(400, false, $e->getMessage());
        }
        // Try-Catch digunakan untuk pengecekan suatu proses berhasil atau tidak. Semua baris pada try akan dijalankan terlebih dahulu, jika  $stuffs = Stuff::all() berhasil diproses tanpa adanya error, maka akan mengembalikan response JSON berupa data hasil dari tabel dengan menggunakan static method sendResponse() dari Response Formatter. Jika ada error pada baris kode try maka prosesnya akan dialihkan pada baris kode catch yang mengembalikan response JSON error dengan menggunakan static method error() dari ResponseFormatter.
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
                'name' => 'required',
                'category' => 'required',
            ]);

            $data = Stuff::create([
                'name' => $request->name,
                'category' => $request->category,
            ]);

            return APIFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return APIFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\stuff  $stuff
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $data = Stuff::where('id', $id)->with('stuffStock', 'inboundStuff', 'lendings')->first();

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
     * @param  \App\Models\stuff  $stuff
     * @return \Illuminate\Http\Response
     */
    public function edit(stuff $stuff)
    {
        //
    }

    /** 
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\stuff  $stuff
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'category' => 'required'
            ]);

            $checkProses = Stuff::where('id', $id)->update([
                'name' => $request->name,
                'category' => $request->category
            ]);

            if (!$checkProses) {
                $data = Stuff::find($id);
                return APIFormatter::sendResponse(200, 'success', $data);
            } else {
                return APIFormatter::sendResponse(400, 'bad request', 'Gagal mengubah data!');
            }
        } catch (\Exception $err) {
            return APIFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\stuff  $stuff
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $checkProses = Stuff::where('id', $id)->first();

            if (!$checkProses->inboundStuffs || !$checkProses->stuffStock || !$chekProses->lendings) {
                return APIFormatter::sendResponse(400, 'Bad request', 'Tidak Dapat Menghapus Data Stuff, sudah terdapat data inbound');
            } else {
                $checkProses->delete();

                return  APIFormatter::sendResponse(200, 'success', 'Data berhasil dihapus!');

            }

        } catch (\Exception $err) {
            return APIFormatter::sendResponse(400, 'bad request', $err->getMesaage());
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

    public function restore($id)
    {
        try {
            $checkProses  = Stuff::onlyTrashed()->where('id', $id)->restore();

            if ($checkProses) {
                $data = Stuff::find($id);
                return APIFormatter::sendResponse(200, 'success', $data);
            } else {
                return APIFormatter::sendResponse(400, 'bad request', 'Gagal mengembalikan data!');
            }
        }  catch (\Exception $err) {
            return APIFormatter::sendResponse(500, 'bad requst', $err->getMessage());
        }
    }

    public function deletePermanent($id)
    {
        try {
            $checkProses = Stuff::onlyTrashed()->where('id', $id)->forceDelete();

            return APIFormatter::sendResponse(200, 'success', 'Berhasil menghapus data stuff!');
        } catch (\Exception $e){
            return APIFormatter::sendResponse(400,'Bad Request',$e->getMessage());
        }
    }

        public function __construct()
    {
        $this->middleware('auth:api');
    }
}