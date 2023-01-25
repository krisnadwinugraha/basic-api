<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Hash;
use Spatie\Permission\Models\Role;

use App\Models\User;

use App\Http\Controllers\Controller;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $data = User::role('Member')->paginate(request()->input('results', 3));    
        return response()->json($data, 200);     
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required'
        ]);

        $masterData = new User;
        $masterData->name = $request->name;
        $masterData->email = $request->email;
        $masterData->password = Hash::make($request->password);
        $masterData->assignRole('Member');

        $masterData->save();

        return response()->json([
            'message' => 'Member data created successfully.'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id)->role('Member');
        $user->update([
            'name'     => $request->name,
            'email'   => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Member updated successfully.'
        ], 200);
    }

    public function delete($id)
    {
        $user = User::findOrFail($id)->role('Member');
        $user->delete();

        return response()->json([
            'message' => 'Member deleted successfully.'
        ], 200);
    }
}
