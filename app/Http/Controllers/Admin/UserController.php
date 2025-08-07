<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-user|edit-user|delete-user', ['only' => ['index','show']]);
        $this->middleware('permission:create-user', ['only' => ['create','store']]);
        $this->middleware('permission:edit-user', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-user', ['only' => ['destroy']]);
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $hotel = $request->session()->get('hotel_id');
        $users = User::paginate(10);
        
        return view('auth.users.index', compact('users', 'hotel'));
    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $roles = Role::pluck('name')->all();
        return view('auth.users.form', compact('hotel', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $params = $request->all();
        User::create($params);
        session()->flash('success', 'Пользователь ' . $request->name . ' добавлен');
        return redirect()->route('users.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user, Request $request): View
    {
        $hotel = $request->session()->get('hotel_id');
        // Check Only Super Admin can update his own Profile
        if ($user->hasRole('Super Admin')){
            if($user->id != auth()->user()->id){
                abort(403, 'USER DOES NOT HAVE THE RIGHT PERMISSIONS');
            }
        }

        return view('auth.users.form', [
            'user' => $user,
            'roles' => Role::pluck('name')->all(),
            'userRoles' => $user->roles->pluck('name')->all(),
            'hotel' => $hotel
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $input = $request->all();

        if(!empty($request->password)){
            $input['password'] = Hash::make($request->password);
        }else{
            $input = $request->except('password');
        }

        $user->update($input);

        $user->syncRoles($request->roles);

        return redirect()->route('users.index')
            ->withSuccess('User is updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // About if user is Super Admin or User ID belongs to Auth User
        if ($user->hasRole('Super Admin') || $user->id == auth()->user()->id)
        {
            abort(403, 'USER DOES NOT HAVE THE RIGHT PERMISSIONS');
        }

        $user->syncRoles([]);
        $user->delete();
        return redirect()->route('users.index')
            ->withSuccess('Пользователь ' . $user->title . ' удален');
    }

    /**
     * Store a newly created users for hotel.
     */
    public function storeHotel(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                'string',
                'min:6',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{6,}$/',
                'confirmed',
            ],
        ]);

        $params = $request->all();
        
        if ( auth()->user()->hasRole('Hotel') ) {

            // Проверить, есть ли уже пользователь с таким email
            $exists = User::where('email', $request->email)->exists();

                if ($exists) {

                    return response()->json([
                        'message' => 'duplicate'
                    ], 409); // 409 Conflict

                }else{
            
                    $user = User::create($params);
                    $user->assignRole($request->roles);
                    return response()->json($user);

                    if ($user) {
                        return response()->json([
                            'message' => 'success',
                            // 'user' => $user
                        ], 201); // 201 Created
                    } else {
                        return response()->json([
                            'message' => 'error'
                        ], 500); // 500 Internal Server Error
                    }
                }
            
        }else{
            return response()->json([
                'message' => 'error'
            ], 500); // 500 Internal Server Error
        }

    }

    public function listHotel(Request $request): View
    {
        $hotel = $request->session()->get('hotel_id');
        if( !empty($hotel) ){
            $users = User::where('hotel_id', $hotel)->paginate(10);
        }else{
            $users = [];
        }
        
        return view('auth.users.listhotel', compact('users', 'hotel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editHotelUser(User $user, Request $request): View
    {
        $hotel = $request->session()->get('hotel_id');
        // Check Only Hotel can update his own Profile
        if ($user->hasRole('Hotel')){
            if($user->id != auth()->user()->id){
                abort(403, 'USER DOES NOT HAVE THE RIGHT PERMISSIONS');
            }
        }

        return view('auth.users.editHotelForm', [
            'user' => $user,
            'roles' => Role::pluck('name')->all(),
            'userRoles' => $user->roles->pluck('name')->all(),
            'hotel' => $hotel
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateHotel(Request $request, User $user): RedirectResponse
    {
        $input = $request->all();

        if(!empty($request->password)){
            $input['password'] = Hash::make($request->password);
        }else{
            $input = $request->except('password');
        }

        $user->update($input);

        $user->syncRoles($request->roles);

        return redirect()->route('users.listHotel')
            ->withSuccess('User is updated successfully.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createView(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $roles = Role::pluck('name')->all();
        return view('auth.users.editHotelForm', compact('hotel', 'roles'));
    }

    /**
     * Store a newly created users for hotel.
     */
    public function createHotel(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{6,}$/|confirmed',
            'email' => 'required|string|email|max:255|unique:users,email',
            'name' => 'required|max:255',
        ]);

        $params = $request->all();
        $user = User::create($params);
        $user->assignRole($request->roles);
        session()->flash('success', 'Пользователь ' . $request->name . ' добавлен');
        return redirect()->route('users.listHotel');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyHotel(User $user): RedirectResponse
    {
        // About if user is Super Admin or User ID belongs to Auth User
        if ($user->hasRole('Super Admin') || $user->id == auth()->user()->id)
        {
            abort(403, 'USER DOES NOT HAVE THE RIGHT PERMISSIONS');
        }

        $user->syncRoles([]);
        $user->delete();
        return redirect()->route('users.listHotel')
            ->withSuccess('Пользователь ' . $user->title . ' удален');
    }

}
