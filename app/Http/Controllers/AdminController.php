<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Traits\AccessTrait;

class AdminController extends Controller
{
    use AccessTrait;

    public function index()
    {

        return view('admins.index');
    }

    public function create()
    {
        $app_permissions = $this->getAccessControl(['Contractor']);

        //  dd($permissions);
         return view('admins.create', compact('app_permissions'));
        //return view('admins.test', compact('app_permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'surname' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:255',
            'nin' => 'required|string|max:255',
            'gender' => 'required|in:male,female,other',
            'permissions_menu' => 'required|array',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        try {
            $validated['name'] = trim($validated['surname'] . ' ' . $validated['first_name'] . ' ' . ($validated['middle_name'] ?? ''));

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'nin' => $validated['nin'],
                'gender' => $validated['gender'],
                'business_id' => 1,
                'branch_id' => Business::find(1)?->branches()->first()?->id,
                'status' => $validated['status'],
                'allowed_branches' => [1],
                'permissions' => $validated['permissions_menu'],
                'password' => '',
                'service_points' => [],
            ]);

            Password::sendResetLink(['email' => $user->email]);

            return redirect()->route('admins.index')->with('success', 'Admin created successfully.');
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Failed to create admin: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        // dd('I am here');
        $admin = User::findOrFail($id);
        $app_permissions = $this->getAccessControl();
        return view('admins.show', compact('admin', 'app_permissions'));
    }

    public function edit($id)
    {
        $admin = User::findOrFail($id);
        $nameParts = explode(' ', $admin->name, 3);
        $surname = $nameParts[0] ?? '';
        $first_name = $nameParts[1] ?? '';
        $middle_name = $nameParts[2] ?? '';
        $app_permissions = $this->getAccessControl(['Contractor']);
        return view('admins.edit', compact('admin', 'surname', 'first_name', 'middle_name', 'app_permissions'));
    }

    public function update(Request $request, $id)
    {
        $admin = User::findOrFail($id);

        $validated = $request->validate([
            'surname' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'phone' => 'required|string|max:255',
            'nin' => 'required|string|max:255',
            'gender' => 'required|in:male,female,other',
            'permissions_menu' => 'required|array',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        try {
            $validated['name'] = trim($validated['surname'] . ' ' . $validated['first_name'] . ' ' . ($validated['middle_name'] ?? ''));

            $admin->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'nin' => $validated['nin'],
                'gender' => $validated['gender'],
                'permissions' => $validated['permissions_menu'],
                'status' => $validated['status'],
            ]);

            return redirect()->route('admins.index')->with('success', 'Admin updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update admin: ' . $e->getMessage());
        }
    }
}
