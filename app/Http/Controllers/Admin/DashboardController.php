<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users' => User::count(),
                'newUsersThisMonth' => User::where('created_at', '>=', $startOfMonth)->count(),
                'categories' => Category::count(),
                'incomeEntries' => Income::count(),
                'expenseEntries' => Expense::count(),
                'totalIncome' => (float) Income::sum('amount'),
                'totalExpenses' => (float) Expense::sum('amount'),
            ],
            'recentUsers' => User::with('role')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role?->name,
                    'created_at' => $user->created_at->toDateString(),
                ]),
            'monthlyActivity' => $this->monthlyActivity(),
        ]);
    }

    /**
     * Income and expense totals for the last six months, oldest first.
     *
     * @return array<int, array{period: string, label: string, income: float, expenses: float}>
     */
    protected function monthlyActivity(): array
    {
        return collect(range(5, 0))
            ->map(function (int $monthsAgo): array {
                $start = Carbon::now()->startOfMonth()->subMonths($monthsAgo);
                $end = $start->copy()->endOfMonth();

                return [
                    'period' => $start->format('Y-m'),
                    'label' => $start->translatedFormat('M'),
                    'income' => (float) Income::whereBetween('created_at', [$start, $end])->sum('amount'),
                    'expenses' => (float) Expense::whereBetween('created_at', [$start, $end])->sum('amount'),
                ];
            })
            ->all();
    }
}
