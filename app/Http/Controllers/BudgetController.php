<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Income;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $incomes = $user->incomes()->latest()->get();
        $expenses = $user->expenses()->latest()->get();

        return Inertia::render('Budget', [
            'incomes' => $incomes,
            'expenses' => $expenses,
            'totalIncome' => $incomes->sum('amount'),
            'totalExpenses' => $expenses->sum('amount'),
        ]);
    }

    public function addIncome(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        auth()->user()->incomes()->create($data);

        return back();
    }

    public function addExpense(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
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
