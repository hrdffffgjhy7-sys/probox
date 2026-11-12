<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(private AuditService $audit)
    {
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->can('users.manage'), 403);

        $users = User::with('roleModel')
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('name', 'like', '%'.$request->q.'%')
                        ->orWhere('username', 'like', '%'.$request->q.'%')
                        ->orWhere('email', 'like', '%'.$request->q.'%');
                });
            })
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->role))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        abort_unless(auth()->user()->can('users.manage'), 403);

        return view('users.form', [
            'user' => new User(['is_active' => true, 'locale' => 'en']),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('users.manage'), 403);
        $data = $this->validated($request);
        $data['password'] = bcrypt($data['password']);
        $data['password_changed_at'] = now();

        $user = User::create($data);
        $this->audit->log('created', 'users', $user, 'User created', [], $user->makeHidden('password')->toArray());

        return redirect()->route('users.index')->with('success', __('Saved successfully.'));
    }

    public function edit(User $user)
    {
        abort_unless(auth()->user()->can('users.manage'), 403);

        return view('users.form', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        abort_unless($request->user()->can('users.manage'), 403);
        $old = $user->makeHidden('password')->toArray();
        $data = $this->validated($request, $user);

        if (! empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
            $data['password_changed_at'] = now();
            $data['must_change_password'] = false;
        } else {
            unset($data['password']);
        }

        if ($user->id === $request->user()->id && ! ($data['is_active'] ?? true)) {
            return back()->withErrors(['is_active' => __('You cannot deactivate your own account.')]);
        }

        $user->update($data);
        $this->audit->log('updated', 'users', $user, 'User updated', $old, $user->fresh()->makeHidden('password')->toArray());

        return redirect()->route('users.index')->with('success', __('Updated successfully.'));
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($request->user()->can('users.manage'), 403);
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['delete' => __('You cannot delete your own account.')]);
        }
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['delete' => __('At least one administrator is required.')]);
        }

        $old = $user->makeHidden('password')->toArray();
        $user->delete();
        $this->audit->log('deleted', 'users', $user, 'User deleted', $old, []);

        return redirect()->route('users.index')->with('success', __('Deleted successfully.'));
    }

    public function restore(Request $request, int $id)
    {
        abort_unless($request->user()->can('users.manage'), 403);
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        $user->update(['is_active' => true]);
        $this->audit->log('restored', 'users', $user, 'User restored');

        return redirect()->route('users.index')->with('success', __('Restored successfully.'));
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('users', 'username')->ignore($user?->id)],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role_id' => ['required', 'exists:roles,id'],
            'locale' => ['nullable', Rule::in(['en', 'ur'])],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $role = Role::findOrFail($data['role_id']);
        $data['role'] = $role->name;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['locale'] = $data['locale'] ?? 'en';

        return $data;
    }
}
