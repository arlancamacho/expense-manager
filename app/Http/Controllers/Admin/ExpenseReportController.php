<?php

namespace App\Http\Controllers\Admin;

use App\Expense;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class ExpenseReportController extends Controller
{
    public function index()
    {
        $from = Carbon::parse(sprintf(
            '%s-%s-01',
            request()->query('y', Carbon::now()->year),
            request()->query('m', Carbon::now()->month)
        ));
        $to      = clone $from;
        $to->day = $to->daysInMonth;

        $expenses = Expense::with('expense_category')
            ->whereBetween('entry_date', [$from, $to]);

        $expensesTotal   = $expenses->sum('amount');
        $groupedExpenses = $expenses->whereNotNull('expense_category_id')->orderBy('amount', 'desc')->get()->groupBy('expense_category_id');

        $expensesSummary = [];

        foreach ($groupedExpenses as $exp) {
            foreach ($exp as $line) {
                if (!isset($expensesSummary[$line->expense_category->name])) {
                    $expensesSummary[$line->expense_category->name] = [
                        'name'   => $line->expense_category->name,
                        'amount' => 0,
                    ];
                }

                $expensesSummary[$line->expense_category->name]['amount'] += $line->amount;
            }
        }

        return view('admin.expenseReports.index', compact(
            'expensesSummary',
            'expensesTotal',
        ));
    }
}
