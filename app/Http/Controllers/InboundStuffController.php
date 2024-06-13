<?php

namespace App\Http\Controllers;

use App\Helpers\APIFormatter;
use App\Models\inboundStuff;
use App\Models\stuff;
use App\Models\stuffStock;
use Illuminate\Http\Request;

class InboundStuffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{
            $getInboundStuff = InboundStuff::with('stuff', 'stuff.stuffStock')->get();
    
            return Apiformatter::sendResponse(200, 'Successfully Get All Inbound Stuff Data', $getInboundStuff);
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
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'total' => 'required',
                'date' => 'required',
                'proff_file' => 'required|mimes:jpeg,png,jpg,pdf|max:2048',
            ]);

            $checkStuff = Stuff::where('id', $request->stuff_id)->first();

            if (!$checkStuff) {
                return APIFormatter::sendResponse(400, false, 'Data Stuff does not exists');
            } else {
                if ($request->hasFile('proff_file')) {
                    $proff = $request->file('proff_file');
                    $destinationPath = 'proof/';
                    $profName = date('YmdHis') . "." . $proff->getClientOriginalExtension();
                    $proff->move($destinationPath, $profName);
                }
    
                $createStock = InboundStuff::create([
                    'stuff_id' => $request->stuff_id,
                    'total' => $request->total,
                    'date' => $request->date,
                    'proff_file' => $profName,
                ]);
    
                if ($createStock) {
                    $getStuff = Stuff::where('id', $request->stuff_id)->first();
                    $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first();
    
                    if (!$getStuffStock) {
                        $updateStock = StuffStock::create([
                            'stuff_id' => $request->stuff_id,
                            'total_available' => $request->total,
                            'total_defec' => 0,
                        ]);
                    } else {
                        $updateStock = $getStuffStock->update([
                            'stuff_id' => $request->stuff_id,
                            'total_available' => $getStuffStock['total_available'] + $request->total,
                            'total_defec' => $getStuffStock['total_defec'],
                        ]);
                    }
    
                    if ($updateStock) {
                        $getStuff = StuffStock::where('stuff_id', $request->studd_id)->first();
                        $stuff = [
                            'stuff' => $getStuff,
                            'inboundStuff' => $createStock,
                            'stuffStock' => $getStuff
                        ];
    
                        return APIFormatter::sendResponse(200, 'Successfully create a inbound Stuff data', $stuff);
                    } else {
                        return APIFromatter::sendResponse(400, false, 'Failed to update a stuff stock data');
                    }
                } else {
                    return APIFormatter::sendResponse(400, false, 'Failed to create a inbound stuff data');
                }
            }
        } catch (\Exception $e) {
            return APIFormatter::sendResponse(400, false, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\inboundStuff  $inboundStuff
     * @return \Illuminate\Http\Response
     */
    public function show(inboundStuff $inboundStuff)
    {
        try {
            $data = inboundStuff::with('stuff', 'stuff.stuffStock')->find($id);

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
     * @param  \App\Models\inboundStuff  $inboundStuff
     * @return \Illuminate\Http\Response
     */
    public function edit(inboundStuff $inboundStuff)
    {
        // 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\inboundStuff  $inboundStuff
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            // get data inbound yang mau di update
            $getInboundStuff = InboundStuff::find($id); 

            if (!$getInboundStuff) {
                return APIFormatter::sendResponse(404, 'Data inbound stuff not found');
            } else {
                
            $this->validate($request, [
                'stuff_id' => 'required',
                'total' => 'required',
                'date' => 'required',
            ]);
    
            if ($request->hasFile('proff_file')) {
                $proff = $request->file('proff_file');
                $destinationPath = 'proof/';
                $profName = date('YmdHis') . "." . $proff->getClientOriginalExtension();
                $proff->move($destinationPath, $profName);
                
                unlink(base_path('public/proof' . $getInboundStuff['proof_file']));
            } else {
                $prooofName = $getInboundStuff['proof_file'];
            }

            // get data stuff berdasarkan stuff id di variabel awal
            $getStuff = Stuff::where('id', $getInboundStuff['stuff_id'])->first();

            // stuff_id request tidak berubah
            $getStuffStock = stuffStock::where('stuff_id', $getInboundStuff[ 'stuff_id'])->first();

            // stuff_id request berubah
            $getCurrentStock = stuffStock::where('stuff_id', $request['stuff_id'])->first();

            if ($getStuffStock->stuff_id == $request->stuff_id) {
                $updateStock = $getStuffStock->update([
                    'total_available' => $getStuffStock->total_available - $getInboundStuff->total + $request->total,
                ]); // Mengurangi total_available dengan total data lama kemudian menambahkan total data baru
            } else {
                $updateStock = $getStuffStock->update([
                    'total_available' => $getStuffStock->total_available - $getInboundStuff->total,
                ]); // Mengurangi total_available dengan total data lama
            
                $updateNewStock = $getCurrentStock->update([
                    'total_available' => $getStuffStock->total_available + $request->total,
                ]); // Menambahkan total_available dengan total yang baru
            }
            
            $updateInbound = $getInboundStuff->update([
                'stuff_id' => $request->stuff_id,
                'total' => $request->total,
                'date' => $request->date,
                'proof_file' => $profName
            ]);
            
            $getStock = stuffStock::where('stuff_id', $request->stuff_id)->first();
            $getInbound = inboundStuff::find($id)->with('stuff', 'stuffStock')->first(); // Memanggil with() setelah find()
            $getCurrentStock = stuff::where('id', $request->stuff_id)->first();
            
            $stuff = [
                'stuff' => $getCurrentStuff, // Anda perlu mendefinisikan $getCurrentStuff sebelumnya
                'inboundStuff' => $getInbound,
                'stuffStock' => $getStock
            ];

            return APIFormatter::sendResponse(200, 'Successfully update A  Inbound Stuff Data', $stuff);
            
        }
    } catch (\Exception $e){
        return APIFormatter::sendResponse(400, $e->getMessage());
    }
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\inboundStuff  $inboundStuff
     * @return \Illuminate\Http\Response
     */
    public function destroy(inboundStuff $inboundStuff)
    {
        try {
            $checkProses = InboundStuff::where('id', $id)->first();

            if ($checkProses) {
                $dataStock = StuffStock::where('stuff_id', $checkProses->stuff_id)->first();

                if ($dataStock->total_available < $checkProses->total) {
                    return APIFromatter::sendResponse(400, 'Bad Request', 'TOtal avaialbe kurang dari total data yang dipinjam');
                } else {
                    $stuffId = $checkProses->stuff_id;
                $totalInbound = $checkProses->total;
                $checkProses->delete();
                    

                if ($dataStock) {
                    $total_available = (int)$dataStock->total_available - (int)$totalInbound;
                    $minusTotalStock = $dataStock->update(['total_available' => $total_available]);
    
                    if ($minusTotalStock) {
                        $updateStufAndInbound = Stuff::where('id', $stuffId)->with('inboundStuffs', 'stuffStock')->first();
                        return APIFormatter::sendResponse(200, 'success', $updateStufAndInbound);
                    }
                } else {
                    // Tangani jika data stok tidak ditemukan
                    return APIFormatter::sendResponse(404, 'not found', 'Data stok stuff tidak ditemukan');
                }
                }
            } else {
                // Tangani jika data InboundStuff tidak ditemukan
                return APIFormatter::sendResponse(404, 'not found', 'Data InboundStuff tidak ditemukan');
            }
        } catch (\Exception $err) {
            // Tangani kesalahan
            return APIFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }


    public function trash()
    {
        try{
            $data= InboundStuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, 'success', $data);
        }catch(\Exception $err){
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }


    public function restore(InboundStuff $inboundStuff, $id)
    {
        try {
            // Memulihkan data dari tabel 'inbound_stuffs'
            $checkProses = InboundStuff::onlyTrashed()->where('id', $id)->restore();
    
            if ($checkProses) {
                // Mendapatkan data yang dipulihkan
                $restoredData = InboundStuff::find($id);
    
                // Mengambil total dari data yang dipulihkan
                $totalRestored = $restoredData->total;
    
                // Mendapatkan stuff_id dari data yang dipulihkan
                $stuffId = $restoredData->stuff_id;
    
                // Memperbarui total_available di tabel 'stuff_stocks'
                $stuffStock = StuffStock::where('stuff_id', $stuffId)->first();
                
                if ($stuffStock) {
                    // Menambahkan total yang dipulihkan ke total_available
                    $stuffStock->total_available += $totalRestored;
    
                    // Menyimpan perubahan pada stuff_stocks
                    $stuffStock->save();
                }
    
                return ApiFormatter::sendResponse(200, 'success', $restoredData);
            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal mengembalikan data!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    
    public function deletePermanent(InboundStuff $inboundStuff, Request $request, $id)
    {
        try {
            $getInbound = InboundStuff::onlyTrashed()->where('id',$id)->first();

            unlink(base_path('public/proff/'.$getInbound->proff_file));
            // Menghapus data dari database
            $checkProses = InboundStuff::where('id', $id)->forceDelete();
    
            // Memberikan respons sukses
            return ApiFormatter::sendResponse(200, 'success', 'Data inbound-stuff berhasil dihapus permanen');
        } catch(\Exception $err) {
            // Memberikan respons error jika terjadi kesalahan
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }   
    
    private function deleteAssociatedFile(InboundStuff $inboundStuff)
    {
        // Mendapatkan jalur lengkap ke direktori public
        $publicPath = $_SERVER['DOCUMENT_ROOT'] . '/public/proff';

        // Menggabungkan jalur file dengan jalur direktori public
        $filePath = public_path('proff/' . $inboundStuff->proff_file);

        // Periksa apakah file ada
        if (file_exists($filePath)) {
            // Hapus file jika ada
            unlink($filePath);
        }
    }

        public function __construct()
    {
        $this->middleware('auth:api');
    }

}
