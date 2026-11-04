<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Exam;
use App\Models\Material;
use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();

        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $activityQuery = ActivityLog::with('user')->latest('created_at');

        if (! $user->isSuperAdmin()) {
            $activityQuery->where('user_id', $user->id);
        }

        return view('pages.Dashboard', [
            'user' => $user,
            'school' => SchoolProfile::current(),
            'totalUsers' => $roleCounts->sum(),
            'totalSuperAdmins' => (int) $roleCounts->get('superadmin', 0),
            'totalGuru' => (int) $roleCounts->get('guru', 0),
            'totalMurid' => (int) $roleCounts->get('murid', 0),
            'totalMaterials' => Material::count(),
            'totalExams' => Exam::count(),
            'recentActivities' => $activityQuery->take(6)->get(),
            'recentMaterials' => Material::with('author')->latest()->take(4)->get(),
        ]);
    }
}
