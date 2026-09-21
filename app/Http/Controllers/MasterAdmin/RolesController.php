<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class RolesController extends Controller
{
    public function list(){
        $roles = Role::where('id', '!=', 1)->get();
        return view('master.roles.list-roles', compact('roles'));
    }
    public function add(){
        $modules = config('permissions-modules.modules');
        return view('master.roles.add-roles', compact('modules'));
    }
    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'name' => 'required|unique:roles,name',
        ]);
        if($validator->fails()){
            $message = $validator->getMessageBag();
            return redirect()->back()->with('error', $message->first())->withInput();
        }
        $modules=config('permissions-modules.modules');
        $role = new Role();
        $role->name = $request->name;
        $role->save();
        $all_permissions=array();
        foreach($modules as $row){
            $add = $row . "_add";
			$edit = $row . "_edit";
			$delete = $row . "_delete";
			$list = $row . "_list";
            $view = $row."_view";
            if($request->$add == 1){
                $name = str_replace("_", " ", $add);
				$all_permissions[] = $name;
				$add_perm = Permission::findByName($name);
				$add_perm->assignRole($role);
            }
            if($request->$edit == 1){
                $name = str_replace("_", " ", $edit);
                $all_permissions[] = $name;
                $edit_perm = Permission::findByName($name);
                $edit_perm->assignRole($role);
            }
            if($request->$delete == 1){
                $name = str_replace("_", " ", $delete);
                $all_permissions[] = $name;
                $delete_perm = Permission::findByName($name);
                $delete_perm->assignRole($role);
            }
            if($request->$list == 1){
                $name = str_replace("_", " ", $list);
                $all_permissions[] = $name;
                $list_perm = Permission::findByName($name);
                $list_perm->assignRole($role);
            }
            if($request->$view == 1){
                $name = str_replace("_", " ", $view);
                $all_permissions[] = $name;
                $view_perm = Permission::findByName($name);
                $view_perm->assignRole($role);
            }
        }
        $role->syncPermissions($all_permissions);
        return redirect()->route('master.roles.list')->with('success', 'Role added successfully');
    }
    public function edit($id){
        if($id == 1){
            abort(404);
        }
        $role = Role::findOrFail($id);
        $modules = config('permissions-modules.modules');
        return view('master.roles.edit-roles', compact('role', 'modules'));
    }
    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'name' => 'required|unique:roles,name,'.$id,
        ]);
        if($validator->fails()){
            $message = $validator->getMessageBag();
            return redirect()->back()->with('error', $message->first());
        }
        $modules=config('permissions-modules.modules');
        $role = Role::findOrFail($id);
        $role->name = $request->name;
        $role->save();
        $all_permissions=array();
        foreach($modules as $row){
            $add = $row . "_add";
            $edit = $row . "_edit";
            $delete = $row . "_delete";
            $list = $row . "_list";
            $view = $row."_view";
            if ($request->$add == 1) {
				$name = str_replace("_", " ", $add);
				$all_permissions[] = $name;
				$add_perm = Permission::findByName($name);
				$add_perm->assignRole($role);
			}
			if ($request->$edit == 1) {
				$name = str_replace("_", " ", $edit);
				$all_permissions[] = $name;
				$edit_perm = Permission::findByName($name);
				$edit_perm->assignRole($role);
			}
			if ($request->$delete == 1) {
				$name = str_replace("_", " ", $delete);
				$all_permissions[] = $name;
				$delete_perm = Permission::findByName($name);
				$delete_perm->assignRole($role);
			}
			if ($request->$list == 1) {
				$name = str_replace("_", " ", $list);
				$all_permissions[] = $name;
				$list_perm = Permission::findByName($name);
				$list_perm->assignRole($role);
			}
            if($request->$view == 1){
                $name = str_replace("_", " ", $view);
                $all_permissions[] = $name;
                $view_perm = Permission::findByName($name);
                $view_perm->assignRole($role);
            }
        }
        $role->syncPermissions($all_permissions);
        return redirect()->route('master.roles.list')->with('success', 'Role updated successfully');
    }
    public function delete($id){
        if($id == 1){
            abort(404);
        }
        $role = Role::findOrFail($id);
        if($role->users->isEmpty()){
            $role->delete();
            return redirect()->route('master.roles.list')->with('success', 'Role deleted successfully');
        }
        else{
            return redirect()->route('master.roles.list')->with('failed', 'Cannot delete role. It is assigned to one or more users');
        }
    }

}
