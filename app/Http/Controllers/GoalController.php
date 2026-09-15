<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GoalController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Goals/Index', [
            'goals' => auth()->user()->goals()
                ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
                ->orderByRaw('CASE WHEN target_date IS NULL THEN 1 ELSE 0 END, target_date ASC')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_amount' => ['required', 'numeric', 'min:0.01'],
            'target_date' => ['nullable', 'date'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
        ]);

        auth()->user()->goals()->create($data);

        return redirect()->route('goals.index')->with('success', __('Goal created successfully!'));
    }

    public function update(Request $request, Goal $goal): RedirectResponse
    {
        // Ensure the goal belongs to the authenticated user
        if ($goal->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_amount' => ['required', 'numeric', 'min:0.01'],
            'current_amount' => ['required', 'numeric', 'min:0'],
            'target_date' => ['nullable', 'date'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
        ]);

        $goal->update($data);

        return back()->with('success', __('Goal updated successfully!'));
    }

    public function destroy(Goal $goal): RedirectResponse
    {
        // Ensure the goal belongs to the authenticated user
        if ($goal->user_id !== auth()->id()) {
            abort(403);
        }

        $goal->delete();

        return back()->with('success', __('Goal deleted successfully!'));
    }
}
