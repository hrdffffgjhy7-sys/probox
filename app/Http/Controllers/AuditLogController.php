<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Support\DateRange;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('audit.view'), 403);
        $range = DateRange::resolve($request);

        $logs = AuditLog::with('user')
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $w->where('description', 'like', '%'.$request->q.'%')
                    ->orWhere('action', 'like', '%'.$request->q.'%')
                    ->orWhere('module', 'like', '%'.$request->q.'%');
            }))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->action))
            ->tap(fn ($q) => DateRange::apply($q, $range, 'created_at'))
            ->orderByDesc('id')->paginate(30)->withQueryString();

        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('audit.index', compact('logs', 'actions', 'range'));
    }
}
