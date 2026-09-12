<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        // if($user->isAdmin()){
        //     dd('admin');
        // }

        $period = request('period', Carbon::now()->format('Y-m'));
        try {
            $startDate = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        } catch (\Exception $e) {
            $startDate = Carbon::now()->startOfMonth();
            $period = $startDate->format('Y-m');
        }
        $endDate = $startDate->copy()->endOfMonth();

        $incomes = $user->incomes()
            ->with('category')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->get();

        $expenses = $user->expenses()
            ->with('category')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->get();

        return Inertia::render('Budget', [
            'currentPeriod' => $period,
            'incomes' => $incomes,
            'expenses' => $expenses,
            'categories' => Category::where('type', 'income')->orWhere('type', 'expense')->get(),
            'totalIncome' => $incomes->sum('amount'),
            'totalExpenses' => $expenses->sum('amount'),
        ]);
    }

    public function addIncome(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')->where('type', 'income')],
            'name' => ['nullable', 'string', 'max:255'],
            'period' => ['nullable', 'date_format:Y-m'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $income = auth()->user()->incomes()->create(Arr::except($data, 'period'));
        $income->forceFill(['created_at' => $this->entryDateForPeriod($data['period'] ?? null)])->save();

        return back();
    }

    public function addExpense(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')->where('type', 'expense')],
            'name' => ['nullable', 'string', 'max:255'],
            'period' => ['nullable', 'date_format:Y-m'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $expense = auth()->user()->expenses()->create(Arr::except($data, 'period'));
        $expense->forceFill(['created_at' => $this->entryDateForPeriod($data['period'] ?? null)])->save();

        return back();
    }

    /**
     * Resolve the timestamp an entry should carry for the period being viewed.
     *
     * Entries added while browsing a past or future month belong to that month,
     * not to the month the entry happens to be created in.
     */
    private function entryDateForPeriod(?string $period): Carbon
    {
        $now = Carbon::now();

        if ($period === null || $period === $now->format('Y-m')) {
            return $now;
        }

        $startDate = Carbon::createFromFormat('Y-m', $period)->startOfMonth();

        return $startDate->isFuture() ? $startDate : $startDate->copy()->endOfMonth()->min($now);
    }

    public function deleteIncome(Income $income): RedirectResponse
    {
        Gate::authorize('delete', $income);
        $income->delete();

        return back();
    }

    public function deleteExpense(Expense $expense): RedirectResponse
    {
        Gate::authorize('delete', $expense);
        $expense->delete();

        return back();
    }
}
