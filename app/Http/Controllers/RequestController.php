<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as Req;
use App\Models\{Reorder, Data, Alert, Rhu, User, Stock};

use DB;

class RequestController extends Controller
{
    public function __construct(){
        $this->table = "requests";
    }

    public function index(){
        return $this->_view('index', [
            'title' => 'Requesition Entry'
        ]);
    }

    public function create(){
        return $this->_view('create', [
            'title' => 'Add Request'
        ]);
    }

    public function inputInfo(Request $req){
        $data = Req::where('reference', $req->ref)->where('status', 'Approved')->get();

        return $this->_view('inputInfo', [
            'title' => 'Input Info',
            'data' => $data,
        ]);
    }

    public function receive(Request $req){
        return $this->_view('receive', [
            'title' => 'Receive'
        ]);
    }

    public function get(Request $req){
        $array = Req::select($req->select);

        // IF HAS SORT PARAMETER $ORDER
        if($req->order){
            $array = $array->orderBy($req->order[0], $req->order[1]);
        }

        // IF HAS WHERE
        if($req->where){
            $array = $array->where($req->where[0], $req->where[1]);
        }

        $array = $array->get();

        // IF HAS LOAD
        if($array->count() && $req->load){
            foreach($req->load as $table){
                $array->load($table);
            }
        }

        // IF HAS GROUP
        if($req->group){
            $array = $array->groupBy($req->group);
        }

        echo json_encode($array);
    }

    public function getPendingRequests(Request $req){
        $array = Req::select($req->select);
        $array = $array->whereIn('status', ["For Approval", "For Delivery", "Approved"]);

        if(auth()->user()->role == "Admin"){
            $array = $array->where('admin_id', auth()->user()->id);
        }
        elseif(auth()->user()->role == "RHU"){
            $array = $array->join('rhus as r', 'r.admin_id', '=', 'requests.admin_id');
            $array = $array->where('r.user_id', auth()->user()->id);
        }

        // IF HAS SORT PARAMETER $ORDER
        if($req->order){
            $array = $array->orderBy($req->order[0], $req->order[1]);
        }

        // IF HAS WHERE
        if($req->where){
            $array = $array->where($req->where[0], $req->where[1]);
        }

        $array = $array->get();

        // IF HAS LOAD
        if($array->count() && $req->load){
            foreach($req->load as $table){
                $array->load($table);
            }
        }

        // IF HAS GROUP
        if($req->group){
            $array = $array->groupBy($req->group);
        }

        echo json_encode($array);
    }

    public function store(Request $req){
        foreach($req->data as $temp){
            $temp = (object)$temp;

            $data = new Req();

            $data->admin_id = RHU::where('user_id', auth()->user()->id)->first()->admin_id;
            $aId = auth()->user()->id;
            $data->stock = Reorder::where('user_id', RHU::where('user_id', $aId)->first()->admin_id)->where('medicine_id', $temp->medicine_id)->first()->stock;

            $data->user_id = $aId;
            $data->reference = $temp->reference;
            $data->requested_by = $temp->requested_by;
            $data->medicine_id = $temp->medicine_id;
            $data->request_qty = $temp->request_qty;
            $data->unit_price = $temp->unit_price;
            $data->amount = $temp->amount;
            $data->transaction_date = $temp->transaction_date . ' ' . now()->toTimeString();

            $data->save();
        }
    }

    public function update(Request $req){
        $query = DB::table($this->table);
        $id = null;

        if($req->where){
            $id = $req->where[1];
            $query = $query->where($req->where[0], $id)->update(array_merge($req->except(['id', '_token', 'where']), ["new" => 1]));
        }
        else{
            $id = $req->id;
            $query = $query->where('id', $id)->update(array_merge($req->except(['id', '_token']), ["new" => 1]));
        }

        if(isset($req->status) && $req->status == "For Delivery"){
            $request = Req::find($req->id);

            $data = new Data();
            $data->user_id = $request->user_id;
            $data->medicine_id = $request->medicine_id;
            $data->transaction_types_id = 6;
            $data->reference = $request->reference;
            $data->particulars = $request->requested_by;
            $data->lot_number = $request->lot_number;
            $data->expiry_date = $request->expiry_date;
            $data->qty = $request->approved_qty;
            $data->unit_price = $request->unit_price;
            $data->amount = $request->amount;
            $data->transaction_date = strlen($request->transaction_date) > 10 ? $request->transaction_date : $request->transaction_date . ' ' . now()->toTimeString();
            $data->save();

            // UPDATE STOCKS
            // $this->updateStock($admin_id, $request->medicine_id, "-", $request->approved_qty);
            // $this->updateStock($request->user_id, $request->medicine_id, "+", $request->approved_qty);
            $admin_id = null;
            if(auth()->user()->role == "Approver"){
                $admin_id = auth()->user()->admin_id; 
            }
            elseif(auth()->user()->role == "Admin"){
                $admin_id = auth()->user()->id;
            }

            $this->updateStock("-", $admin_id, $request);
            $this->updateStock("+", $request->user_id, $request);
        }
        elseif(isset($req->status) && $req->status == "Incomplete Qty"){
            $request = Req::find($id);

            $request->load('medicine');
            $item = $request->medicine->name;
            $ref = $request->reference;
            $rQty = $request->received_qty;
            $aQty = $request->approved_qty;

            $rid = Rhu::where('user_id', auth()->user()->id)->first()->admin_id;

            $this->createAlert("Incomplete Delivery of $item with Reference No. $ref: $rQty/$aQty", $rid);
        }
    }

    // public function updateStock($uid, $mid, $operator, $num){
    public function updateStock($operator, $uid, $request){
        $reorder = Reorder::where('user_id', $uid)->where('medicine_id', $request->medicine_id);

        if($operator == "+"){
            $reorder->increment('stock', $request->approved_qty);
        }
        elseif($operator == "-"){
            $reorder->decrement('stock', $request->approved_qty);
        }

        $reorder = $reorder->first();
        $stock = Stock::where('reorder_id', $reorder->id)
                    ->where('lot_number', $request->lot_number)
                    ->where('expiry_date', $request->expiry_date->toDateTimeString())
                    ->where('unit_price', $request->unit_price);

        if($stock->get()->count()){
            if($operator == "+"){
                $stock->increment('qty', $request->approved_qty);
            }
            elseif($operator == "-"){
                $stock->decrement('qty', $request->approved_qty);
            }
        }
        else{
            $stock = new Stock();
            $stock->reorder_id = $reorder->id;
            $stock->lot_number = $request->lot_number;
            $stock->expiry_date = $request->expiry_date;
            $stock->unit_price = $request->unit_price;
            $stock->qty = $request->approved_qty;
            $stock->save();
        }

        $reorder = $reorder->first();
        if($reorder->stock <= $reorder->point && User::find($uid)->role == "Admin"){
            $reorder->load('medicine');
            $name = $reorder->medicine->name;
            $point = $reorder->point;
            $this->createAlert("$name stock is low: $point");
        }
    }

    public function getNewAlerts(){
        $requests = null;

        if(in_array(auth()->user()->role, ['Admin'])){
            $requests = Req::where('admin_id', auth()->user()->id)->whereIn('status', ['For Approval', 'Delivered', 'Incomplete Qty', 'Cancelled'])->where('new', 1)->count();
        }
        elseif(in_array(auth()->user()->role, ['"Approver"'])){
            $requests = Req::where('admin_id', auth()->user()->admin_id)->whereIn('status', ['For Approval', 'Delivered', 'Incomplete Qty', 'Cancelled'])->where('new', 1)->count();
        }
        elseif(in_array(auth()->user()->role, ['RHU'])){
            $requests = Req::where('user_id', auth()->user()->id)->whereIn('status', ['Approved', 'Declined', 'For Delivery'])->where('new', 1)->count();
        }

        echo $requests;
    }

    public function seenNewAlerts(){
        if(in_array(auth()->user()->role, ['Admin'])){
            $requests = Req::where('admin_id', auth()->user()->id)->where('new', 1)->update(['new' => 0]);
        }
        elseif(in_array(auth()->user()->role, ["Approver"])){
            $requests = Req::where('admin_id', auth()->user()->admin_id)->where('new', 1)->update(['new' => 0]);
        }
        elseif(in_array(auth()->user()->role, ['RHU'])){
            $requests = Req::where('user_id', auth()->user()->id)->where('new', 1)->update(['new' => 0]);
        }
    }

    public function getAdminAlert(){
        $alert = Alert::where('user_id', auth()->user()->id)
                    ->where('seen', 0)
                    ->first();

        if($alert){
            $alert->update(['seen' => 1]);
        }

        echo json_encode($alert);
    }

    private function createAlert($message, $id = null){
        $alert = new Alert();
        $alert->user_id = $id ?? auth()->user()->id;
        $alert->message = $message;
        $alert->save();
    }

    public function delete(Request $req){
        Req::find($req->id)->delete();
    }

    private function _view($view, $data = array()){
        return view($this->table . "." . $view, $data);
    }
}
