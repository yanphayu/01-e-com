<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Notifications\ProductReported;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function filteredQuery(Request $request): Builder
    {
        $query = Report::with(['user', 'product.images']);
        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());

        if (in_array($status, ['pending', 'resolved', 'dismissed'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('reason', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%")
                    ->orWhereHas('product', fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('user', fn (Builder $query): Builder => $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        return $query->latest();
    }

    public function index(Request $request): View
    {
        $readAt = now();
        $request->user()
            ->unreadNotifications()
            ->where('type', ProductReported::class)
            ->update([
                'read_at' => $readAt,
                'updated_at' => $readAt,
            ]);

        $reports = $this->filteredQuery($request)->paginate(15)->withQueryString();

        return view('admin.reports.index', compact('reports'));
    }

    public function resolve(Request $request, Report $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:resolved,dismissed,pending'],
        ]);

        $report->update(['status' => $validated['status']]);

        return back()->with('status', 'Report updated.');
    }
}
