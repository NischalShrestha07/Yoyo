<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public function dashboard(Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [ChartOfAccount::class, $business]);

        $accounts = ChartOfAccount::where('business_id', $business->id)->where('is_active', true)->get()->groupBy('type');
        $recentEntries = JournalEntry::where('business_id', $business->id)->with('lines.chartOfAccount')->latest()->limit(10)->get();

        if (view()->exists('business.accounting.dashboard')) {
            return view('business.accounting.dashboard', compact('business', 'accounts', 'recentEntries'));
        }

        return response()->json(['accounts' => $accounts, 'recent_entries' => $recentEntries]);
    }

    public function chartOfAccounts(Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [ChartOfAccount::class, $business]);

        $accounts = ChartOfAccount::where('business_id', $business->id)->with('parent')->orderBy('code')->get();

        if (view()->exists('business.accounting.chart-of-accounts')) {
            return view('business.accounting.chart-of-accounts', compact('business', 'accounts'));
        }

        return response()->json(['accounts' => $accounts]);
    }

    public function journalEntries(Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [JournalEntry::class, $business]);

        $entries = JournalEntry::where('business_id', $business->id)->with(['lines.chartOfAccount', 'user'])->latest()->paginate(30);

        if (view()->exists('business.accounting.journal-entries')) {
            return view('business.accounting.journal-entries', compact('business', 'entries'));
        }

        return response()->json(['entries' => $entries]);
    }
}
