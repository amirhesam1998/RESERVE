<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\roles\CreateRole;
use App\Http\Requests\roles\EditRoleRequest;
use App\Http\Requests\users\EditPassRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Throwable;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('roles', ['view-roles']);

        try {
            $roles = Role::latest()->get();
            return view('roles.index', compact('roles'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('roles', ['create-roles']);

        try {
            $permissions = Permission::latest()->get();
            return view('roles.create', compact('permissions'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRole $request)
    {
        $this->authorize('roles', ['create-roles']);

        try {
            $data = $request->validated();
            $role = Role::create($data);
            if ($request->has('permissions')) {
                $role->permissions()->sync($request->input('permissions'));
            }
            return redirect()->route('roles.index')->with('success', 'role created with permission');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Somthing went wrong, Try again later');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $this->authorize('roles', ['view-roles']);

        try {
            return view('roles.single', compact('role'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $this->authorize('roles', ['edit-roles']);

        try {
            $permissions = Permission::latest()->get();

            return view('roles.edit', compact('role', 'permissions'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EditRoleRequest $request, Role $role)
    {

        $this->authorize('roles', ['edit-roles']);

        try {
            $role->update($request->validated());
            $permissions = $request->input('permissions', []);
            if (! empty($permissions)) {
                $role->permissions()->sync($permissions);
            } else {
                $role->permissions()->sync([]);
            }
            return redirect()->route('roles.index')->with('success', 'roles updated successfully');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Somthing went wrong, Try again later');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $this->authorize('roles', ['delete-roles']);

        try {
            $role->delete();
            return redirect()->route('roles.index');
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later');
        }
    }
}
