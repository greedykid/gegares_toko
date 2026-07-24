<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        $allowedSorts = ['name', 'email', 'role', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $users = User::query()
            ->when($request->search, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$s.'%')->orWhere('email', 'like', '%'.$s.'%')))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->role))
            ->orderBy($sort, $direction)
            ->paginate(15)->appends($request->except('partial'));

        if ($request->boolean('partial')) {
            return view('admin.users._table', compact('users'));
        }
        $userStats = User::selectRaw("
            count(*) as total,
            sum(case when role = 'admin' then 1 else 0 end) as admin_count,
            sum(case when role = 'user' then 1 else 0 end) as customer_count,
            sum(case when created_at >= ? then 1 else 0 end) as new_this_month
        ", [now()->startOfMonth()->toDateTimeString()])->first();

        $totalUsers = $userStats->total ?? 0;
        $totalAdmin = $userStats->admin_count ?? 0;
        $totalCustomer = $userStats->customer_count ?? 0;
        $newUsersThisMonth = $userStats->new_this_month ?? 0;

        return view('admin.users.index', compact(
            'users', 'totalUsers', 'totalAdmin', 'totalCustomer', 'newUsersThisMonth'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,user',
        ]);

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return back()->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,user',
        ]);

        if ($user->role === 'admin' && $data['role'] === 'user') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Tidak dapat mengubah peran karena ini adalah satu-satunya akun administrator yang tersisa.');
            }
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        if ($user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Tidak dapat menghapus administrator terakhir di sistem.');
            }
        }

        $user->delete();
        return back()->with('success', 'Pengguna berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:users,id',
        ])['ids'];

        // Never delete yourself or admin accounts via bulk action.
        $deletable = User::whereIn('id', $ids)
            ->where('id', '!=', auth()->id())
            ->where('role', '!=', 'admin');

        $count = $deletable->count();
        (clone $deletable)->delete();

        $skipped = count($ids) - $count;
        $msg = "{$count} pengguna berhasil dihapus.".($skipped > 0 ? " {$skipped} dilewati (admin/akun sendiri)." : '');

        return back()->with('success', $msg);
    }
}
