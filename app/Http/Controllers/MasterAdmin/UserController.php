<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function list(){
        $users = User::where('id', '!=', 1)->get();
        return view('master.users.list', compact('users'));
    }
    public function add(){
        $roles = Role::where('id', '!=', 1)->get();
        return view('master.users.create', compact('roles'));
    }
    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required',
            'user_type' => 'required',
            'role_id' => 'required_if:user_type,staff',
        ], [
            'role_id.required_if' => 'Please select role',
        ]);

        if ($validator->fails()) {
            $message = $validator->getMessageBag();
            return redirect()->back()->with('error', $message->first())->withInput();
        }
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->user_type = $request->user_type;
        $user->password = Hash::make($request->password);
        $user->save();
        if ($request->user_type == 'staff') {
            $role = Role::findOrFail($request->role_id);
            $user->assignRole($role);
        }
        return redirect()->route('master.users.list')->with('success', 'User created successfully');
    }

    public function edit($id){
        $user = User::findOrFail($id);
        $roles = Role::where('id', '!=', 1)->get();
        return view('master.users.edit', compact('user', 'roles'));
    }
    public function update(Request $request, $id){
        if($id == 1){
            abort(404);
        }
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email,' . $id,
            'user_type' => 'required',
            'role_id' => 'required_if:user_type,staff',
        ], [
            'role_id.required_if' => 'Please select role',
        ]);
        if ($validator->fails()) {
            $message = $validator->getMessageBag();
            return redirect()->back()->with('error', $message->first());
        }
        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->user_type = $request->user_type;
        if($request->password){
            $user->password = Hash::make($request->password);
        }
        $user->save();
        $user->roles()->detach();
        if($request->user_type == 'staff') {
            $role = Role::findOrFail($request->role_id);
            $user->assignRole($role);
        }
        return redirect()->route('master.users.list')->with('success', 'User updated successfully');
    }
    public function delete($id){
        if($id == 1){
            abort(404);
        }
        $user = User::findOrFail($id);
        $user->roles()->detach();
        $user->delete();
        return redirect()->route('master.users.list')->with('success', 'User deleted successfully');
    }

    public function myProfile(){
        return view('master.users.my-profile');
    }
    public function updateMyProfile(Request $request){
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email,' . Auth::user()->id,
        ]);
        if ($validator->fails()) {
            $message = $validator->getMessageBag();
            return redirect()->back()->with('error', $message->first());
        }
        $user = User::findOrFail(Auth::user()->id);
        $user->name = $request->name;
        $user->email = $request->email;
        if($request->password){
            $user->password = Hash::make($request->password);
        }
        $user->save();
        return redirect()->back()->with('success', 'Profile updated successfully');
    }
}
