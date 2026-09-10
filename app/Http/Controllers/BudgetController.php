<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        auth()->user()->incomes()->create($data);

        return back();
    }

    public function addExpense(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')->where('type', 'expense')],
            'name' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        auth()->user()->expenses()->create($data);

        return back();
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
