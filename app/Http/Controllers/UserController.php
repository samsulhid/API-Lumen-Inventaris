<?php

namespace App\Http\Controllers;

use App\Models\user;
use Illuminate\Http\Request;
use App\Helpers\APIFormatter;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = User::all()->toArray();

            return APIFormatter::sendResponse(200, 'success', $data);
        }catch(\Exception $err){
            return  APIFormatter::sendError(400, 'bad request', $err->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
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
            'username' => 'required',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,staff',
        ]);

        // $password = substr($request->username, 0, 3) . substr($request->email, 0, 3);

        $data = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        return APIFormatter::sendResponse(200, 'success', $data);
        }   catch (\Exception $err) {
        return APIFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $data = User::where('id', $id)->first();

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            $getUser = User::find($id);

            if (!$getUser){
                return  APIFormatter::sendResponse(404, flase, 'Data user not found');
            } else {
                $this->validate($request, [
                    'username' => 'required',
                    'email' => 'required|email|unique:username,email',
                    'role' => 'required|in:admin,staff',
                
                ]);

                if ($request->password) {
                    $checkProses = User::where('id', $id)->update([
                        'username' => $request->username,
                        'email' => $request->email,
                        'role' => $request->role,
                        'password' => Hash::make($request->password),
                    ]);
                } else {
                    $checkProses = User::where('id', $id)->update([
                        'username' => $request->username,
                        'email' => $request->email,
                        'role' => $request->role,
                    ]);
                }

                if (!$checkProses) {
                    $data = User::find($id);
                    return APIFormatter::sendResponse(200, 'success', $data);
                } else {
                    return APIFormatter::sendResponse(400, 'bad request', 'Gagal mengubah data!');
                }
            }
        } catch (\Exception $err) {
            return APIFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $checkProses = User::where('id', $id)->delete();

            return  APIFormatter::sendResponse(200, 'success', 'Data deleted successfully');
        } catch (\Exception $err) {
            return APIFormatter::sendResponse(400, 'bad request', $err->getMesaage());
        }
    }

    public function trash()
    {
        try {
            $data = User::onlyTrashed()->get();

            return APIFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return APIFormatter::sendResponse(400, 'bad request', $err->gatMessage());
        }
    }

    public function restore($id)
    {
        try {
            $checkProses  = User::onlyTrashed()->where('id', $id)->restore();

            if ($checkProses) {
                $data = User::find($id);
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
            $checkProses = User::onlyTrashed()->where('id', $id)->forceDelete();

            return APIFormatter::sendResponse(200, 'success', 'Berhasil menghapus data User!');
        } catch (\Exception $e){
            return APIFormatter::sendResponse(400,'Bad Request',$e->getMessage());
        }
    }

    public function login(Request $request) 
    {
        try {
            $this->validate($request, [
                'email' => 'required',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return APIFormatter::sendResponse(400, false, 'Login failed user doesnt exists');
            } else {
                $isValid = Hash::check($request->password, $user->password);

                if (!$isValid) {
                    return APIFormatter::sendResponse(400, false, 'Login failed! password doesnt match');
                } else {
                    $generateToken = bin2hex(random_bytes(40));

                    $user->update([
                        'token' =>$generateToken
                    ]);

                    return APIFormatter::sendResponse(200, 'Login successfully', $user);
                }
            }
        } catch (\Exception $e) {
            return APIFormatter::sendResponse(400, false, $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return APIFormatter::sendResponse(400, 'Login failed user doesnt exists');
            }else{
                if (!$user->token) {
                    return  APIFormatter::sendResponse(400, 'Logout failed user doesnt login sciene');
                } else {
                    $logout = $user->update(['token' => null]);

                    if ($logout){
                        return APIFormatter::sendResponse(200, 'Logout  Successfully');
                    }
                }
            }
        } catch (\Exception $e) {
            return  APIFormatter::sendResponse(400, $e->getMessage());
        }
    }

    public function __construct()
    {
        $this->middleware('auth:api');
    }
}