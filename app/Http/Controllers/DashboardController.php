<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Request as Req, Data, Alert, TransactionType};
use Auth;

class DashboardController extends Controller
{
    function index(){
        if(auth()->user()->role == "Approver"){
            return redirect()->route('request.request')->send();
        }
        elseif(auth()->user()->role == "Super Admin"){
            return redirect()->route('admin.admin')->send();
        }

        $array = TransactionType::where('inDashboard', 1);

        if(auth()->user()->role == "Admin"){
            $array = $array->where('admin_id', auth()->user()->id);
        }
        elseif(auth()->user()->role == "RHU"){
            $array = $array->join('rhus as r', 'r.admin_id', '=', 'transaction_types.admin_id');
            $array = $array->where('r.user_id', auth()->user()->id);
        }

        $ttId = $array->pluck('transaction_types.id');
        $data = Data::whereIn('transaction_types_id', $ttId)->orderByDesc('transaction_date')->get();
        $data->load('transaction_type');
        $data->load('reorder.medicine');

        $data = $data->groupBy('transaction_types_id');

        return $this->_view('dashboard', [
            'title'         => 'Dashboard',
            'widgets'       => $data
        ]);
    }

    private function _view($view, $data = array()){
        return view($view, $data);
    }
}
