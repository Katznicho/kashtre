<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Qualification;
use App\Models\Department;
use App\Models\Section;
use App\Models\Title;
use App\Models\ServicePoint;
use Illuminate\Support\Facades\Auth;
use App\Traits\AccessTrait;

class UserController extends Controller
{
    use AccessTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        #fecth all users
        $users = User::all();
        // Pass businesses to populate select dropdown (optional: only if admin)
        $businesses = Business::all();

        return view('users.index', compact('users', 'businesses'));
    }



    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

        $businesses = Business::all();
        // $permissions = $this->getAllPermissions();
        $permissions = $this->getAccessControl();

        // dd($permissions);

        return view('users.create', compact('businesses', 'permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'surname' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'status' => 'required|in:active,inactive,suspended',
            'business_id' => 'required|exists:businesses,id',
            'branch_id' => 'required|exists:branches,id',
            'profile_photo_path' => 'nullable|image|max:2048',
            'phone' => 'required|string|max:255',
            'nin' => 'required|string|max:255',
            'gender' => 'required|in:male,female,other',
            'qualification_id' => 'required|exists:qualifications,id',
            'department_id' => 'required|exists:departments,id',
            'section_id' => 'required|exists:sections,id',
            'title_id' => 'required|exists:titles,id',
            'service_points' => 'required|array',
            'allowed_branches' => 'required|array',
            'permissions_menu' => 'required|array',
            // Contractor profile fields (conditionally required)
            'bank_name' => 'required_if:permissions_menu.*,Contractor|string|nullable',
            'account_name' => 'required_if:permissions_menu.*,Contractor|string|nullable',
            'account_number' => 'required_if:permissions_menu.*,Contractor|string|nullable',
        ]);

        try {
            // Concatenate name fields
            $validated['name'] = trim($validated['surname'] . ' ' . $validated['first_name'] . ' ' . ($validated['middle_name'] ?? ''));

            // Upload profile photo if provided
            if ($request->hasFile('profile_photo_path')) {
                $path = $request->file('profile_photo_path')->store('profile_photos', 'public');
                $validated['profile_photo_path'] = $path;
            }

            // Create the user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $validated['status'],
                'business_id' => $validated['business_id'],
                'branch_id' => $validated['branch_id'],
                'profile_photo_path' => $validated['profile_photo_path'] ?? null,
                'phone' => $validated['phone'],
                'nin' => $validated['nin'],
                'gender' => $validated['gender'],
                'qualification_id' => $validated['qualification_id'],
                'department_id' => $validated['department_id'],
                'section_id' => $validated['section_id'],
                'title_id' => $validated['title_id'],
                'service_points' => $validated['service_points'],
                'allowed_branches' => $validated['allowed_branches'],
                'permissions' => $validated['permissions_menu'],
                'password' => '',
            ]);
             // Send password setup link (uses Laravel’s password reset logic)
             Password::sendResetLink(['email' => $user->email]);

            // If Contractor permission is selected, create ContractorProfile
            if (in_array('Contractor', $validated['permissions_menu'])) {
                \App\Models\ContractorProfile::create([
                    'business_id' => $validated['business_id'],
                    'bank_name' => $validated['bank_name'],
                    'account_name' => $validated['account_name'],
                    'account_number' => $validated['account_number'],
                ]);
            }


            return redirect()->route('users.index')->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::with('business', 'branch')->findOrFail($id);
        $contractorProfile = null;
        if (in_array('Contractor', (array) $user->permissions)) {
            $contractorProfile = \App\Models\ContractorProfile::where('business_id', $user->business_id)->first();
        }
        $businesses = Business::all();
        // $permissions = $this->getAllPermissions();
        $permissions = $this->getAccessControl();
        return view('users.show', compact('user', 'contractorProfile', 'businesses', 'permissions'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $businesses = \App\Models\Business::with('branches')->get()->keyBy('id');
        $qualifications = \App\Models\Qualification::all();
        $departments = \App\Models\Department::all();
        $sections = \App\Models\Section::all();
        $titles = \App\Models\Title::all();
        $servicePoints = \App\Models\ServicePoint::all();
        $contractorProfile = null;
        if (in_array('Contractor', (array) $user->permissions)) {
            $contractorProfile = \App\Models\ContractorProfile::where('business_id', $user->business_id)->first();
        }
        // Split name for form
        $nameParts = explode(' ', $user->name, 3);
        $surname = $nameParts[0] ?? '';
        $first_name = $nameParts[1] ?? '';
        $middle_name = $nameParts[2] ?? '';

        $permissions = $this->getAccessControl(); 
        return view('users.edit', compact('user', 'businesses', 'qualifications', 'departments', 'sections', 'titles', 'servicePoints', 'contractorProfile', 'surname', 'first_name', 'middle_name', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'surname' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'status' => 'required|in:active,inactive,suspended',
            'business_id' => 'required|exists:businesses,id',
            'branch_id' => 'required|exists:branches,id',
            'profile_photo_path' => 'nullable|image|max:2048',
            'phone' => 'required|string|max:255',
            'nin' => 'required|string|max:255',
            'gender' => 'required|in:male,female,other',
            'qualification_id' => 'required|exists:qualifications,id',
            'department_id' => 'required|exists:departments,id',
            'section_id' => 'required|exists:sections,id',
            'title_id' => 'required|exists:titles,id',
            'service_points' => 'required|array',
            'allowed_branches' => 'required|array',
            'permissions_menu' => 'required|array',
            // Contractor profile fields (conditionally required)
            'bank_name' => 'required_if:permissions_menu.*,Contractor|string|nullable',
            'account_name' => 'required_if:permissions_menu.*,Contractor|string|nullable',
            'account_number' => 'required_if:permissions_menu.*,Contractor|string|nullable',
        ]);
        try {
            $validated['name'] = trim($validated['surname'] . ' ' . $validated['first_name'] . ' ' . ($validated['middle_name'] ?? ''));
            if ($request->hasFile('profile_photo_path')) {
                $path = $request->file('profile_photo_path')->store('profile_photos', 'public');
                $validated['profile_photo_path'] = $path;
            }
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $validated['status'],
                'business_id' => $validated['business_id'],
                'branch_id' => $validated['branch_id'],
                'profile_photo_path' => $validated['profile_photo_path'] ?? $user->profile_photo_path,
                'phone' => $validated['phone'],
                'nin' => $validated['nin'],
                'gender' => $validated['gender'],
                'qualification_id' => $validated['qualification_id'],
                'department_id' => $validated['department_id'],
                'section_id' => $validated['section_id'],
                'title_id' => $validated['title_id'],
                'service_points' => $validated['service_points'],
                'allowed_branches' => $validated['allowed_branches'],
                'permissions' => $validated['permissions_menu'],
            ]);
            // Contractor profile logic
            if (in_array('Contractor', $validated['permissions_menu'])) {
                \App\Models\ContractorProfile::updateOrCreate(
                    ['business_id' => $validated['business_id']],
                    [
                        'bank_name' => $validated['bank_name'],
                        'account_name' => $validated['account_name'],
                        'account_number' => $validated['account_number'],
                    ]
                );
            } else {
                \App\Models\ContractorProfile::where('business_id', $validated['business_id'])->delete();
            }
            return redirect()->route('users.index')->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
