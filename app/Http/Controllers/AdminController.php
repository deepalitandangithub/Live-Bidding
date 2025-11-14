<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Bid;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;


class AdminController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $actions = Action::with('bids')->orderByDesc('created_at')->get();

            return datatables()->of($actions)
                ->addIndexColumn()
                ->addColumn('status', function($action) {
                    return $action->is_open ? 'Open' : 'Closed';
                })
                ->addColumn('participants', function($action) {
                    return $action->bids->pluck('bidder_name')->unique()->count();
                })
                ->addColumn('highest_bid', function($action) {
                    $highest = $action->highestBid(); 
                    return $highest ? '₹' . $highest->amount : '';
                })
                ->addColumn('ends_at', function($action) {
                    return $action->ends_at->format('d M Y, h:i A');
                })
                ->addColumn('actions', function($action) {
                    $btn = '<a href="'.route('action.bids', $action).'" class="btn btn-info btn-sm">View Bids</a> ';
                    if ($action->is_open) {
                        $btn .= '<form action="'.route('action.close', $action).'" method="POST" style="display:inline;">
                                    '.csrf_field().'
                                    <button class="btn btn-danger btn-sm">Close</button>
                                </form>';
                    }
                    return $btn;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.index');
    }


    public function createAction() {
        return view('admin.create-action');
    }

    public function storeAction(Request $request) {
        $request->validate([
            'title' => 'required|string|max:255',
            'ends_at' => 'required|date|after:now',
        ]);

        Action::create($request->only('title', 'description', 'ends_at'));
        return redirect()->route('index')->with('success', 'Action created successfully');
    }

    public function closeAction(Action $action) {
        $action->update(['is_open' => false]);
        return redirect()->back()->with('success', 'Action closed');
    }

    public function viewBids(Request $request, Action $action)
    {
        if ($request->ajax()) {
            $bids = $action->bids()->orderByDesc('created_at');

            return DataTables::of($bids)
                ->addIndexColumn()
                ->editColumn('amount', function($bid) {
                    return '₹' . number_format($bid->amount, 2);
                })
                ->editColumn('created_at', function($bid) {
                    return $bid->created_at->format('d-m-Y H:i:s');
                })
                ->make(true);
        }

        return view('admin.view-bids', compact('action'));
    }

}

