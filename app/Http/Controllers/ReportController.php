<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Expense;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [Sale::class, $business]);

        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $salesToday = Sale::where('business_id', $business->id)->whereDate('created_at', $today)->where('status', '!=', 'draft')->sum('total_amount');
        $salesMonth = Sale::where('business_id', $business->id)->where('created_at', '>=', $thisMonth)->where('status', '!=', 'draft')->sum('total_amount');
        $totalSales = Sale::where('business_id', $business->id)->where('status', '!=', 'draft')->count();

        $expensesMonth = Expense::where('business_id', $business->id)->where('expense_date', '>=', $thisMonth)->sum('amount');
        $purchasesMonth = PurchaseOrder::where('business_id', $business->id)->where('status', 'received')->where('created_at', '>=', $thisMonth)->sum('total_amount');

        $lowStock = Product::query()
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->where('track_inventory', true)
            ->where('min_stock_level', '>', 0)
            ->withSum('stockBalances as total_stock', 'quantity')
            ->get()
            ->filter(fn ($p) => (int) ($p->total_stock ?? 0) <= (int) $p->min_stock_level)
            ->count();

        $recentSales = Sale::where('business_id', $business->id)->with(['customer'])->latest()->limit(10)->get();

        if (view()->exists('business.reports.index')) {
            return view('business.reports.index', compact('business', 'salesToday', 'salesMonth', 'totalSales', 'expensesMonth', 'purchasesMonth', 'lowStock', 'recentSales'));
        }

        return response()->json([
            'sales_today' => $salesToday,
            'sales_month' => $salesMonth,
            'total_sales' => $totalSales,
            'expenses_month' => $expensesMonth,
            'purchases_month' => $purchasesMonth,
            'low_stock_count' => $lowStock,
            'recent_sales' => $recentSales,
        ]);
    }

    public function sales(Business $business, Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', [Sale::class, $business]);
        $query = Sale::where('business_id', $business->id)->where('status', '!=', 'draft');
        if ($request->from) $query->whereDate('created_at', '>=', $request->from);
        if ($request->to) $query->whereDate('created_at', '<=', $request->to);
        $sales = $query->with(['customer', 'user'])->latest()->paginate(50);
        $total = $query->sum('total_amount');
        if (view()->exists('business.reports.sales')) {
            return view('business.reports.sales', compact('business', 'sales', 'total'));
        }
        return response()->json(['sales' => $sales, 'total' => $total]);
    }

    public function inventory(Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [Sale::class, $business]);
        $products = Product::where('business_id', $business->id)->where('track_inventory', true)->withSum('stockBalances as total_stock', 'quantity')->get();
        if (view()->exists('business.reports.inventory')) {
            return view('business.reports.inventory', compact('business', 'products'));
        }
        return response()->json(['products' => $products]);
    }

    public function profitLoss(Business $business, Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', [Sale::class, $business]);
        $from = $request->from ?? Carbon::now()->startOfMonth();
        $to = $request->to ?? Carbon::now()->endOfMonth();

        $sales = Sale::where('business_id', $business->id)->whereBetween('created_at', [$from, $to])->where('status', '!=', 'draft')->sum('total_amount');
        $purchases = PurchaseOrder::where('business_id', $business->id)->whereBetween('created_at', [$from, $to])->where('status', 'received')->sum('total_amount');
        $expenses = Expense::where('business_id', $business->id)->whereBetween('expense_date', [$from, $to])->sum('amount');

        $profit = $sales - $purchases - $expenses;

        if (view()->exists('business.reports.profit-loss')) {
            return view('business.reports.profit-loss', compact('business', 'sales', 'purchases', 'expenses', 'profit', 'from', 'to'));
        }
        return response()->json(['sales' => $sales, 'purchases' => $purchases, 'expenses' => $expenses, 'profit' => $profit]);
    }
}
