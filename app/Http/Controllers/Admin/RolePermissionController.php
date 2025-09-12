<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Role Permission Controller
 *
 * Handles CRUD operations for users, roles, and permissions in the admin panel.
 * Provides comprehensive user management with role-based access control.
 */
class RolePermissionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

	   /**
	    * Display a listing of users.
	    *
	    * @return View|RedirectResponse
	    */
	   public function users()
	   {
	       try {
	           $users = User::with('roles')->orderBy('name')->paginate(20);
	           $roles = Role::orderBy('name')->get();

	           return view('admin.users.index', compact('users', 'roles'));
	       } catch (\Exception $e) {
	           Log::error('Error fetching users: ' . $e->getMessage());
	           return back()->with('error', 'Failed to load users.');
	       }
	   }

	   /**
	    * Assign a role to a user.
	    *
	    * @param Request $request
	    * @param User $user
	    * @return RedirectResponse
	    */
	   public function assignUserRole(Request $request, User $user): RedirectResponse
	   {
	       try {
	           $request->validate([
	               'role' => 'required|string|exists:roles,name'
	           ]);

	           $user->syncRoles([$request->role]);

	           Log::info("Role '{$request->role}' assigned to user '{$user->email}' by " . Auth::user()?->email);

	           return back()->with('success', 'Role updated successfully.');
	       } catch (\Exception $e) {
	           Log::error('Error assigning role: ' . $e->getMessage());
	           return back()->with('error', 'Failed to update role.');
	       }
	   }

	   /**
	    * Show the form for creating a new user.
	    *
	    * @return View
	    */
	   public function createUserForm(): View
	   {
	       $roles = Role::orderBy('name')->get();
	       return view('admin.users.create', compact('roles'));
	   }

	   /**
	    * Store a newly created user in storage.
	    *
	    * @param Request $request
	    * @return RedirectResponse
	    */
	   public function storeUser(Request $request): RedirectResponse
	   {
	       try {
	           $validated = $request->validate([
	               'name' => 'required|string|max:255',
	               'email' => 'required|string|email|max:255|unique:users',
	               'password' => ['required', 'confirmed', Password::defaults()],
	               'user_type' => 'required|in:admin,customer',
	               'phone' => 'nullable|string|max:20',
	               'address' => 'nullable|string|max:500',
	               'role' => 'nullable|string|exists:roles,name',
	           ]);

	           $user = User::create([
	               'name' => $validated['name'],
	               'email' => $validated['email'],
	               'password' => Hash::make($validated['password']),
	               'user_type' => $validated['user_type'],
	               'phone' => $validated['phone'] ?? null,
	               'address' => $validated['address'] ?? null,
	           ]);

	           // Auto-assign role based on user_type if no role specified
	           if (!empty($validated['role'])) {
	               $user->assignRole($validated['role']);
	           } elseif ($validated['user_type'] === 'customer') {
	               $user->assignRole('customer');
	           } elseif ($validated['user_type'] === 'admin') {
	               $user->assignRole('admin');
	           }

	           Log::info("User '{$user->email}' created by " . Auth::user()?->email);

	           return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
	       } catch (\Exception $e) {
	           Log::error('Error creating user: ' . $e->getMessage());
	           return back()->withInput()->with('error', 'Failed to create user.');
	       }
	   }

	   /**
	    * Show the form for editing the specified user.
	    *
	    * @param User $user
	    * @return View
	    */
	   public function editUser(User $user): View
	   {
	       $roles = Role::orderBy('name')->get();
	       $userRole = $user->getRoleNames()->first();
	       return view('admin.users.edit', compact('user', 'roles', 'userRole'));
	   }

	   /**
	    * Update the specified user in storage.
	    *
	    * @param Request $request
	    * @param User $user
	    * @return RedirectResponse
	    */
	   public function updateUser(Request $request, User $user): RedirectResponse
	   {
	       try {
	           $validated = $request->validate([
	               'name' => 'required|string|max:255',
	               'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
	               'password' => 'nullable|confirmed|' . Password::defaults(),
	               'user_type' => 'required|in:admin,customer',
	               'phone' => 'nullable|string|max:20',
	               'address' => 'nullable|string|max:500',
	               'role' => 'nullable|string|exists:roles,name',
	           ]);

	           $updateData = [
	               'name' => $validated['name'],
	               'email' => $validated['email'],
	               'user_type' => $validated['user_type'],
	               'phone' => $validated['phone'] ?? null,
	               'address' => $validated['address'] ?? null,
	           ];

	           // Only update password if provided
	           if (!empty($validated['password'])) {
	               $updateData['password'] = Hash::make($validated['password']);
	           }

	           $user->update($updateData);

	           // Update role if provided
	           if (!empty($validated['role'])) {
	               $user->syncRoles([$validated['role']]);
	           } else {
	               $user->syncRoles([]);
	           }

	           Log::info("User '{$user->email}' updated by " . Auth::user()?->email);

	           return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
	       } catch (\Exception $e) {
	           Log::error('Error updating user: ' . $e->getMessage());
	           return back()->withInput()->with('error', 'Failed to update user.');
	       }
	   }

	   /**
	    * Remove the specified user from storage.
	    *
	    * @param User $user
	    * @return RedirectResponse
	    */
	   public function destroyUser(User $user): RedirectResponse
	   {
	       try {
	           // Prevent self-deletion
	           if ($user->id === Auth::id()) {
	               return back()->with('error', 'You cannot delete your own account.');
	           }

	           // Prevent deletion of super admin or other protected users
	           if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
	               return back()->with('error', 'Cannot delete the last admin user.');
	           }

	           $userEmail = $user->email;
	           $user->delete();

	           Log::info("User '{$userEmail}' deleted by " . Auth::user()?->email);

	           return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
	       } catch (\Exception $e) {
	           Log::error('Error deleting user: ' . $e->getMessage());
	           return back()->with('error', 'Failed to delete user.');
	       }
	   }

	   /**
	    * Display a listing of roles.
	    *
	    * @return View|RedirectResponse
	    */
	   public function roles()
	   {
	       try {
	           $roles = Role::with('permissions')->orderBy('name')->get();
	           $permissions = Permission::orderBy('name')->get();

	           return view('admin.roles.index', compact('roles', 'permissions'));
	       } catch (\Exception $e) {
	           Log::error('Error fetching roles: ' . $e->getMessage());
	           return back()->with('error', 'Failed to load roles.');
	       }
	   }

	   /**
	    * Sync permissions for a specific role.
	    *
	    * @param Request $request
	    * @param Role $role
	    * @return RedirectResponse
	    */
	   public function syncRolePermissions(Request $request, Role $role): RedirectResponse
	   {
	       try {
	           $request->validate([
	               'permissions' => 'array',
	               'permissions.*' => 'string|exists:permissions,name'
	           ]);

	           $permissionNames = $request->input('permissions', []);
	           $role->syncPermissions($permissionNames);

	           Log::info("Permissions updated for role '{$role->name}' by " . Auth::user()?->email);

	           return back()->with('success', 'Permissions updated successfully.');
	       } catch (\Exception $e) {
	           Log::error('Error syncing role permissions: ' . $e->getMessage());
	           return back()->with('error', 'Failed to update permissions.');
	       }
	   }

	   /**
	    * Show the form for creating a new role.
	    *
	    * @return View
	    */
	   public function createRoleForm(): View
	   {
	       $permissions = Permission::orderBy('name')->get();
	       return view('admin.roles.create', compact('permissions'));
	   }

	   /**
	    * Store a newly created role in storage.
	    *
	    * @param Request $request
	    * @return RedirectResponse
	    */
	   public function storeRole(Request $request): RedirectResponse
	   {
	       try {
	           $validated = $request->validate([
	               'name' => 'required|string|unique:roles,name|regex:/^[a-zA-Z0-9_-]+$/',
	           ]);

	           $role = Role::create($validated);

	           Log::info("Role '{$role->name}' created by " . Auth::user()?->email);

	           return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
	       } catch (\Exception $e) {
	           Log::error('Error creating role: ' . $e->getMessage());
	           return back()->withInput()->with('error', 'Failed to create role.');
	       }
	   }

	   /**
	    * Show the form for editing the specified role.
	    *
	    * @param Role $role
	    * @return View
	    */
	   public function editRole(Role $role): View
	   {
	       $permissions = Permission::orderBy('name')->get();
	       $rolePermissions = $role->permissions->pluck('name')->toArray();
	       return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
	   }

	   /**
	    * Update the specified role in storage.
	    *
	    * @param Request $request
	    * @param Role $role
	    * @return RedirectResponse
	    */
	   public function updateRole(Request $request, Role $role): RedirectResponse
	   {
	       try {
	           $validated = $request->validate([
	               'name' => 'required|string|unique:roles,name,' . $role->id . '|regex:/^[a-zA-Z0-9_-]+$/',
	           ]);

	           $role->update($validated);

	           Log::info("Role '{$role->name}' updated by " . Auth::user()?->email);

	           return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
	       } catch (\Exception $e) {
	           Log::error('Error updating role: ' . $e->getMessage());
	           return back()->withInput()->with('error', 'Failed to update role.');
	       }
	   }

	   /**
	    * Remove the specified role from storage.
	    *
	    * @param Role $role
	    * @return RedirectResponse
	    */
	   public function destroyRole(Role $role): RedirectResponse
	   {
	       try {
	           // Prevent deletion of default roles
	           if (in_array($role->name, ['admin', 'customer'])) {
	               return back()->with('error', 'You cannot delete the default admin or customer roles.');
	           }

	           // Check if role is assigned to users
	           if ($role->users()->count() > 0) {
	               return back()->with('error', 'Cannot delete role that is assigned to users.');
	           }

	           $roleName = $role->name;
	           $role->delete();

	           Log::info("Role '{$roleName}' deleted by " . Auth::user()?->email);

	           return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
	       } catch (\Exception $e) {
	           Log::error('Error deleting role: ' . $e->getMessage());
	           return back()->with('error', 'Failed to delete role.');
	       }
	   }

	   /**
	    * Display a listing of permissions.
	    *
	    * @return View|RedirectResponse
	    */
	   public function permissions()
	   {
	       try {
	           $permissions = Permission::orderBy('name')->paginate(50);
	           return view('admin.permissions.index', compact('permissions'));
	       } catch (\Exception $e) {
	           Log::error('Error fetching permissions: ' . $e->getMessage());
	           return back()->with('error', 'Failed to load permissions.');
	       }
	   }

	   /**
	    * Show the form for creating a new permission.
	    *
	    * @return View
	    */
	   public function createPermissionForm(): View
	   {
	       return view('admin.permissions.create');
	   }

	   /**
	    * Store a newly created permission in storage.
	    *
	    * @param Request $request
	    * @return RedirectResponse
	    */
	   public function storePermission(Request $request): RedirectResponse
	   {
	       try {
	           $validated = $request->validate([
	               'name' => 'required|string|unique:permissions,name|regex:/^[a-zA-Z0-9_.]+$/',
	           ]);

	           $permission = Permission::create($validated);

	           Log::info("Permission '{$permission->name}' created by " . Auth::user()?->email);

	           return redirect()->route('admin.permissions.index')->with('success', 'Permission created successfully.');
	       } catch (\Exception $e) {
	           Log::error('Error creating permission: ' . $e->getMessage());
	           return back()->withInput()->with('error', 'Failed to create permission.');
	       }
	   }

	   /**
	    * Show the form for editing the specified permission.
	    *
	    * @param Permission $permission
	    * @return View
	    */
	   public function editPermission(Permission $permission): View
	   {
	       return view('admin.permissions.edit', compact('permission'));
	   }

	   /**
	    * Update the specified permission in storage.
	    *
	    * @param Request $request
	    * @param Permission $permission
	    * @return RedirectResponse
	    */
	   public function updatePermission(Request $request, Permission $permission): RedirectResponse
	   {
	       try {
	           $validated = $request->validate([
	               'name' => 'required|string|unique:permissions,name,' . $permission->id . '|regex:/^[a-zA-Z0-9_.]+$/',
	           ]);

	           $permission->update($validated);

	           Log::info("Permission '{$permission->name}' updated by " . Auth::user()?->email);

	           return redirect()->route('admin.permissions.index')->with('success', 'Permission updated successfully.');
	       } catch (\Exception $e) {
	           Log::error('Error updating permission: ' . $e->getMessage());
	           return back()->withInput()->with('error', 'Failed to update permission.');
	       }
	   }

	   /**
	    * Remove the specified permission from storage.
	    *
	    * @param Permission $permission
	    * @return RedirectResponse
	    */
	   public function destroyPermission(Permission $permission): RedirectResponse
	   {
	       try {
	           // Check if permission is assigned to roles
	           if ($permission->roles()->count() > 0) {
	               return back()->with('error', 'Cannot delete permission that is assigned to roles.');
	           }

	           $permissionName = $permission->name;
	           $permission->delete();

	           Log::info("Permission '{$permissionName}' deleted by " . Auth::user()?->email);

	           return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted successfully.');
	       } catch (\Exception $e) {
	           Log::error('Error deleting permission: ' . $e->getMessage());
	           return back()->with('error', 'Failed to delete permission.');
	       }
	   }
	
	   /**
	    * Search customers for order creation.
	    *
	    * @param Request $request
	    * @return \Illuminate\Http\JsonResponse
	    */
	   public function searchCustomers(Request $request)
	   {
	       try {
	           $search = $request->get('search', '');
	           $role = $request->get('role', 'customer');
	
	           $query = User::query();
	
	           // Filter by role if specified
	           if ($role) {
	               $query->whereHas('roles', function ($q) use ($role) {
	                   $q->where('name', $role);
	               });
	           }
	
	           // Search by name or email
	           if (!empty($search)) {
	               $query->where(function ($q) use ($search) {
	                   $q->where('name', 'like', "%{$search}%")
	                     ->orWhere('email', 'like', "%{$search}%");
	               });
	           }
	
	           $customers = $query->select('id', 'name', 'email', 'phone')
	                              ->orderBy('name')
	                              ->limit(20)
	                              ->get();
	
	           return response()->json([
	               'success' => true,
	               'data' => $customers,
	               'count' => $customers->count()
	           ]);
	
	       } catch (\Exception $e) {
	           Log::error('Error searching customers: ' . $e->getMessage());
	           return response()->json([
	               'success' => false,
	               'message' => 'Failed to search customers'
	           ], 500);
	       }
	   }
	}
