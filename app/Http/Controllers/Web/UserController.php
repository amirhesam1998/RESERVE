<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\users\EditPassRequest;
use App\Http\Requests\LoginRequest as RequestsLoginRequest;
use App\Http\Requests\UserRequest as RequestsUserRequest;
use App\Http\Requests\users\EditPassRequest as UsersEditPassRequest;
use App\Http\Requests\users\UserRequest;
use App\Http\Requests\users\EditRequest;
use App\Http\Requests\users\LoginRequest;
use App\Http\Requests\users\NewUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use ReflectionReference;
use Tymon\JWTAuth\Exceptions\JWTException as ExceptionsJWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use \Tymon\JWTAuth\Exceptions\JWTException;
use \Illuminate\Routing\Controllers\Middleware;
use League\CommonMark\Extension\Table\TableRow;
use Throwable;

class UserController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('users-onlyPermission', ['view-clients']);

        try {


            $users = User::where('level', 'user')->get();

            return view('users.index', compact('users'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later...');
        }
    }

    // ========================= Admins =======================
    public function admins()
    {
        $this->authorize('users-onlyPermission', ['view-admins']);

        try {

            $users = User::where('level', 'admin')->get();

            return view('users.index', compact('users'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later...');
        }
    }


    /**===================== SIGNUP FORM =============================
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('users.signup');
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later...');
        }
    }

    /**======================== SIGNUP ============================
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        try {
            $data = $request->validated();

            $data['password'] = bcrypt($data['password']);

            User::create($data);

            return redirect()->route('users.index');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Somthing went wrong, Try again later...');
        }
    }

    /**=========================================================
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('showone_user', [$user, 'view-clients']);

        try {
            return view('users.single', compact('user'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later...');
        }
    }

    /**==========================================================
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('users', [$user, 'edit-clients']);

        try {
            $roles = Role::latest()->get();

            return view('users.edit', compact('user', 'roles'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later...');
        }
    }

    /**==========================================================
     * Update the specified resource in storage.
     */
    public function update(EditRequest $request, User $user)
    {
        $this->authorize('users', [$user, 'edit-clients']);

        try {
            $user->update($request->validated());

            $roles = $request->input('roles');
            if (! empty($roles)) {
                $this->authorize('users-onlyPermission', ['assign-roles']);

                $user->roles()->sync($request->input('roles'));

                $user->level = 'admin';
            } else {
                $user->roles()->sync([]);

                $user->level = 'user';
            }

            $user->save();

            return Redirect()->route('users.show', Auth::user())->with('success', 'user-role table updated successfully');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Somthing went wrong, Try again later...');
        }
    }

    /**======================================================
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        $this->authorize('users', [$user, 'delete-clients']);

        try {
            $currentUser = Auth::user();

            if ($currentUser->id === $user->id) {
                Auth::logout();
                $user->delete();

                try {
                    if ($access = $request->cookie('access_token')) {
                        JWTAuth::setToken($request->cookie($access))->invalidate();
                    }
                    if ($refresh = $request->cookie('refresh_token')) {
                        JWTAuth::setToken($request->cookie($refresh))->invalidate();
                    }
                } catch (ExceptionsJWTException) {
                }

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('user.create')->withCookie(cookie()->forget('access_token'))->withCookie(cookie()->forget('refresh_token'));
            } else {
                $user->delete();
                return redirect()->route('users.index')->with('success', 'User deleted successfully');
            }
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later...');
        }
    }

    // ======================= LOGIN FORM =============================
    public function loginForm()
    {
        try {
            return view('users.login');
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later...');
        }
    }

    // ============================= EDIT PASSFORM ============================
    public function editPassForm(User $user)
    {
        $this->authorize('users', [$user, 'editpass-clients']);

        try {
            return view('users.editPass', compact('user'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later...');
        }
    }

    // ============================== EDIT PASS ===============================
    public function editPass(EditPassRequest $request, User $user)
    {
        $this->authorize('users', [$user, 'editpass-clients']);

        try {

            if (! Hash::check($request->curruntPass, $user->password))
                return back()->withErrors([
                    'curruntPass' => 'Current password is incorrect.'
                ]);

            $newPass = Hash::make($request->newPass);

            $user->update([
                'password' => $newPass
            ]);
            return redirect()->route('users.show', Auth::user())->with('success', "Password updated successfully.");
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Somthing went wrong, Try again later...');
        }
    }

    // =========================== LOGOUT ============================
    public function logout(Request $request)
    {
        try {
            $token = $request->cookie('access_token');
            $refresh_token = $request->cookie('refresh_token');
            try {
                JWTAuth::setToken($token)->invalidate();
                JWTAuth::setToken($refresh_token)->invalidate();
            } catch (JWTException $e) {
            }
            $accessForget = cookie()->forget('access_token');
            $refreshForget = cookie()->forget('refresh_token');

            return redirect()->route('login')->withCookie($accessForget)->withCookie($refreshForget);
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later...');
        }
    }

    // =============================== NEW USER FORM =========================
    public function createNewUserForm()
    {
        $this->authorize('users-onlyPermission', ['create-clients']);

        try {
            $roles = Role::latest()->get();
            return view('users.newUser', compact('roles'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later...');
        }
    }

    // ============================== CREATE NEW USER ========================
    public function createNewUser(NewUserRequest $request)
    {
        $this->authorize('users-onlyPermission', [Auth::user(), 'create-clients']);

        try {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);

            $user = User::create($request->validated());
            if ($request->has('roles')) {
                $this->authorize('users-onlyPermission', ['assign-roles']);
                $user->roles()->sync($request->input('roles'));
                $user->level = 'admin';
            }
            $user->save();
            return redirect()->route('users.index');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Somthing went wrong, Try again later...');
        }
    }
}
