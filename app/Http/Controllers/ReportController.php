<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Data, Alert, TransactionType, Rhu, Request as Req, Stock};
use DB;

class ReportController extends Controller
{
    public function inventory(){
        return $this->_view('inventory', [
            'title' => 'Inventory Report',
        ]);
    }

    public function sales(){
        return $this->_view('sales', [
            'title' => 'Sales Report',
        ]);
    }

    public function purchaseOrder(){
        return $this->_view('purchaseOrder', [
            'title' => 'Purchase Order Report',
        ]);
    }

    public function binCard(){
        return $this->_view('binCard', [
            'title' => 'Bin Card Activity',
        ]);
    }

    public function alert(){
        return $this->_view('alert', [
            'title' => 'Alerts',
        ]);
    }

    public function dailySheet(){
        return $this->_view('dailySheet', [
            'title' => 'Daily Sheet',
        ]);
    }

    public function toRhu(){
        return $this->_view('toRhu', [
            'title' => 'Transferred to Franchise',
        ]);
    }

    public function toBarangay(){
        return $this->_view('toBarangay', [
            'title' => 'Transferred to Branch',
        ]);
    }

    public function wastedMedicine(){
        return $this->_view('wastedMedicine', [
            'title' => 'Wasted Medicinces',
        ]);
    }

    public function getInventory(Request $req){
        $temp = Data::select('data.*')
            ->where('transaction_types_id', $req->tType)
            ->where('bhc_id', 'like', $req->outlet)
            ->whereNotNull('bhc_id')
            ->whereBetween('transaction_date', [now()->parse($req->from)->startOfDay()->toDateTimeString(), now()->parse($req->to)->endOfDay()->toDateTimeString()]);

        if(auth()->user()->role == "RHU"){
            $temp = $temp->join('rhus as r', 'r.user_id', '=', 'data.user_id');
            $temp = $temp->where('r.user_id', '=', auth()->user()->id);
        }
        else{
            $temp = $temp->join('bhcs as b', 'b.id', '=', 'data.bhc_id');
            $temp = $temp->join('rhus as r', 'r.id', '=', 'b.rhu_id');
            $temp = $temp->where('r.admin_id', '=', auth()->user()->id);
        }

        $temp = $temp->get()->unique();

        $temp->load('reorder.medicine');
        $temp->load('transaction_type');
        $temp = $temp->groupBy('medicine_id');

        $from = $req->from;
        $to = $req->to;

        $dates = $this->getDates($from, $to);
        $array = [];

        foreach($temp as $medicine){
            $grandtotal = 0;
            $tempDates = [];
            foreach($dates as $date){
                $total = 0;
                foreach($medicine as $data){
                    if(now()->parse($data->transaction_date)->startOfDay()->toDateTimeString() == $date){
                        if($data->transaction_type->operator == "+"){
                            $total += $data->{$req->view};
                            $grandtotal += $data->{$req->view};
                        }
                        else{
                            $total -= $data->{$req->view};
                            $grandtotal -= $data->{$req->view};
                        }
                    }
                }

                $tempDates[now()->parse($date)->format('M d')] = $total;
            }

            array_push($array, 
                array_merge(
                    array_merge(
                        ["item" => $medicine[0]->reorder->medicine->name], 
                        $tempDates
                    ),
                    ["total" => $grandtotal]
                )
            );
        }

        echo json_encode($array);
    }

    public function getSales(Request $req){
        $temp = Data::select('data.*')
                    ->whereNotNull('bhc_id')
                    ->whereIn('transaction_types_id', [65])
                    ->whereBetween('transaction_date', [now()->parse($req->from)->startOfDay()->toDateTimeString(), now()->parse($req->to)->endOfDay()->toDateTimeString()]);

        if(auth()->user()->role == "RHU"){
            $temp = $temp->join('rhus as r', 'r.user_id', '=', 'data.user_id');
            $temp = $temp->where('r.user_id', '=', auth()->user()->id);
        }
        else{
            $temp = $temp->join('bhcs as b', 'b.id', '=', 'data.bhc_id');
            $temp = $temp->join('rhus as r', 'r.id', '=', 'b.rhu_id');
            $temp = $temp->where('r.admin_id', '=', auth()->user()->id);
        }

        $temp = $temp->get()->unique();

        $temp->load('bhc');
        $temp->load('transaction_type');
        $temp->load('reorder.medicine');
        $temp = $temp->groupBy($req->fby);

        $from = $req->from;
        $to = $req->to;

        $dates = $this->getDates($from, $to);
        $array = [];

        foreach($temp as $group){
            $grandTotal = 0;
            $tempDates = [];
            foreach($dates as $date){
                $total = 0;
                foreach($group as $data){
                    if(now()->parse($data->transaction_date)->startOfDay()->toDateTimeString() == $date){
                        if($data->transaction_type->operator == "-"){
                            $total += $data->{$req->view};
                            $grandTotal += $data->{$req->view};
                        }
                        else{
                            $total -= $data->{$req->view};
                            $grandTotal -= $data->{$req->view};
                        }
                    }
                }

                $tempDates[now()->parse($date)->format('M d')] = $total;
            }

            $title = $req->fby == "bhc_id" ? $group[0]->bhc->name : $group[0]->reorder->medicine->name;
            array_push($array, 
                array_merge(
                    array_merge(
                        ["item" => $title], 
                        $tempDates
                    ),
                    ["total" => $grandTotal]
                )
            );
        }

        echo json_encode($array);
    }

    public function getPurchaseOrder(Request $req){
        $temp = Data::where('bhc_id', 'like', $req->bhc_id)
                    ->where('transaction_types_id', 5)
                    ->whereBetween('transaction_date', [now()->parse($req->from)->startOfDay()->toDateTimeString(), now()->parse($req->to)->endOfDay()->toDateTimeString()]);;

        if(auth()->user()->role == "RHU"){
            $temp = $temp->join('rhus as r', 'r.user_id', '=', 'data.user_id');
            $temp = $temp->where('r.user_id', '=', auth()->user()->id);
        }
        else{
            $temp = $temp->join('bhcs as b', 'b.id', '=', 'data.bhc_id');
            $temp = $temp->join('rhus as r', 'r.id', '=', 'b.rhu_id');
            $temp = $temp->where('r.admin_id', '=', auth()->user()->id);
        }

        $temp = $temp->get();

        $temp->load('reorder.medicine');
        $temp = $temp->groupBy('medicine_id');

        $from = $req->from;
        $to = $req->to;

        $dates = $this->getDates($from, $to);
        $array = [];

        foreach($temp as $group){
            $grandTotal = 0;
            $tempDates = [];
            foreach($dates as $date){
                $total = 0;
                foreach($group as $data){
                    if(now()->parse($data->transaction_date)->toDateString() == $date){
                        $total += $data->{$req->view};
                        $grandTotal += $data->{$req->view};
                    }
                }

                $tempDates[now()->parse($date)->format('M d')] = $total;
            }

            array_push($array, 
                array_merge(
                    array_merge(
                        ["item" => $group[0]->reorder->medicine->name], 
                        $tempDates
                    ),
                    ["total" => $grandTotal]
                )
            );
        }

        echo json_encode($array);
    }

    public function getDailySheet(Request $req){
        $temp = Data::where('bhc_id', 'like', $req->bhc_id)
                    ->whereBetween('transaction_date', [now()->parse($req->from)->startOfDay()->toDateTimeString(), now()->parse($req->to)->endOfDay()->toDateTimeString()]);

        if(auth()->user()->role == "RHU"){
            $temp = $temp->join('rhus as r', 'r.user_id', '=', 'data.user_id');
            $temp = $temp->where('r.user_id', '=', auth()->user()->id);
        }
        else{
            $temp = $temp->join('bhcs as b', 'b.id', '=', 'data.bhc_id');
            $temp = $temp->join('rhus as r', 'r.id', '=', 'b.rhu_id');
            $temp = $temp->where('r.admin_id', '=', auth()->user()->id);
        }

        $temp = $temp->get();

        $temp->load('transaction_type');
        $temp->load('reorder.medicine');
        $temp = $temp->groupBy('medicine_id');

        $ttt = TransactionType::pluck('type');

        $array = [];
        foreach($temp as $group){
            $ei = 0;

            foreach($ttt as $tt){
                $tt = str_replace('.', '', $tt);
                $tTypeTransaction[$tt] = 0;
            }

            foreach($group as $data){
                $type = $data->transaction_type->type;
                $type = str_replace('.', '', $type);
                if(isset($tTypeTransaction[$type])){
                    if($data->transaction_type->operator == "+"){
                        $ei += $data->{$req->view};
                        $tTypeTransaction[$type] += $data->{$req->view};
                    }
                    elseif($data->transaction_type->operator == "-"){
                        $ei -= $data->{$req->view};
                        $tTypeTransaction[$type] -= $data->{$req->view};
                    }
                }
            }

            array_push($array, 
                array_merge(
                    array_merge(
                        ["item" => $group[0]->reorder->medicine->name], 
                        $tTypeTransaction
                    ),
                    ["Ending $req->view" => $ei]
                ),
            );
        }

        echo json_encode($array);
    }
    
    public function getBinCard(Request $req){
        $data = Data::where('user_id', 'like', $req->user_id);

        if(auth()->user()->role != "Admin"){
            $data = $data->where('user_id', auth()->user()->id);
        }

        $data = $data->orderBy('transaction_date', 'desc')->get();

        $data->load('transaction_type');
        $data->load('reorder.medicine.category');
        $data = $data->sortByDesc('transaction_date')->groupBy('medicine_id');

        $array = [];

        foreach($data as $group){
            $balance = $group[0]->reorder->stock;
            $name = $group[0]->reorder->medicine->name;

            foreach($group as $record){
                $receiving = 0;
                $issuance = 0;

                $temp = $balance;

                if($record->transaction_type->operator == "+"){
                    $balance -= $record->qty;
                    $receiving = $record->qty;
                }
                else{
                    $balance += $record->qty;
                    $issuance = $record->qty;
                }

                array_push($array, [
                    // "category" => $record->reorder->medicine->category->name,
                    "item" => $name . " (" . $record->reorder->medicine->category->name . ")",
                    "tx" => $record->transaction_type->type,
                    "rcv" => $receiving,
                    "issue" => $issuance,
                    "bal" => $balance,
                    "date" => $record->transaction_date->toDateString(),
                    'stock' => $record->reorder->stock
                ]);
            }
        }

        echo json_encode($array);
    }

    public function getAlert(){
        $data = Alert::orderBy('created_at', 'desc')->get();
        echo json_encode($data);
    }

    public function salesPerRhu(){
        $from = now()->subDays(6)->startOfDay()->toDateTimeString();
        $to = now()->endOfDay()->toDateTimeString();

        $dates = $this->getDates($from, $to);
        $data = Data::whereBetween('transaction_date', [$from, $to])
                        ->whereIn('transaction_types_id', [2,3]);
                        // ->where('user_id', '>', 1)

        if(auth()->user()->role == "RHU"){
            $data = $data->join('rhus as r', 'r.user_id', '=', 'data.user_id');
            $data = $data->where('r.user_id', '=', auth()->user()->id);
        }
        else{
            $data = $data->join('bhcs as b', 'b.id', '=', 'data.bhc_id');
            $data = $data->join('rhus as r', 'r.id', '=', 'b.rhu_id');
            $data = $data->where('r.admin_id', '=', auth()->user()->id);
        }

        $data = $data->get();
        
        $data->load('rhu');
        $data = $data->groupBy('user_id');
        $rhus = Rhu::whereIn('user_id', array_keys($data->toArray()))->pluck('company_name', 'user_id');

        $labels = [];
        $temp = [];
        foreach($data as $id => $a){
            foreach($dates as $date){
                $date = now()->parse($date)->toDateString();
                $temp[$id][$date] = 0;
            }
        }

        foreach($data as $id => $rhuTransactions){
            foreach($rhuTransactions as $transaction){
                if($transaction->transaction_types_id == 2){
                    $temp[$id][now()->parse($transaction->transaction_date)->toDateString()] += $transaction->amount;
                }
                else{
                    $temp[$id][now()->parse($transaction->transaction_date)->toDateString()] -= $transaction->amount;
                }
            }
        }

        $labels = [];
        foreach($dates as $date){
            array_push($labels, now()->parse($date)->format('M d'));
        }

        $dataset = [];
        foreach($temp as $id => $data){
            $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
            array_push($dataset, [
                'label' => $rhus[$id],
                'data' => array_values($data),
                'borderColor' => $color,
                'backgroundColor' => $color,
                'hoverRadius' => 10
            ]);
        }

        echo json_encode(['labels' => $labels, 'dataset' => $dataset]);
    }

    public function getToRhu(Request $req){
        $data = Req::select('requests.*', 'r.company_name', 'm.name as mname')
                    ->join('rhus as r', 'r.user_id', '=', 'requests.user_id')
                    ->join('medicines as m', 'm.id', '=', 'requests.medicine_id')
                    ->where('r.admin_id', '=', auth()->user()->id)
                    ->whereBetween('transaction_date', [now()->parse($req->from)->startOfDay()->toDateTimeString(), now()->parse($req->to)->endOfDay()->toDateTimeString()])
                    ->where('medicine_id', 'like', $req->sku)
                    ->where('requests.user_id', 'like', $req->rhu)
                    ->whereIn('status', ['Delivered', 'Incomplete Qty'])
                    ->get();

        echo json_encode($data);
    }

    public function getToBarangay(Request $req){
        $data = Data::select('data.*', 'b.name as bname', 'm.name as mname', "m.packaging")
                    ->join('bhcs as b', 'b.id', '=', 'data.bhc_id')
                    ->join('transaction_types as t', 't.id', '=', 'data.transaction_types_id')
                    ->join('reorders as rod', 'rod.id', '=', 'data.medicine_id')
                    ->join('medicines as m', 'm.id', '=', 'rod.medicine_id')
                    ->whereBetween('transaction_date', [now()->parse($req->from)->startOfDay()->toDateTimeString(), now()->parse($req->to)->endOfDay()->toDateTimeString()])
                    ->where('data.bhc_id', 'like', $req->bhc)
                    ->where('t.operator', '-');

        if($req->rhu != "%%"){
            $data = $data->join('rhus as r', 'r.id', '=', 'b.rhu_id');
            $data = $data->where('r.user_id', '=', $req->rhu);
        }
        else{
            $data = $data->join('rhus as r', 'r.id', '=', 'b.rhu_id');
            $data = $data->where('r.admin_id', '=', auth()->user()->id);
        }
        
        $data = $data->get();

        $temp = [];

        $temp2 = [];
        foreach($data->unique('mname') as $temp3){
            $temp2[$temp3->mname] = $temp3->packaging;
        }

        foreach($temp2 as $medicine => $packaging){
            $temp[$medicine]["packaging"] = $packaging;
            foreach($data->unique('bname')->pluck('bname') as $bhc){
                $temp[$medicine]["bhcs"][$bhc] = 0;
            }
        }

        foreach($data as $data2){
            $mname = $data2->mname;
            $bname = $data2->bname;

            $temp[$mname]["bhcs"][$bname] = $temp[$mname]["bhcs"][$bname] + $data2->qty;
        }

        $data = [];
        foreach($temp as $medicine => $temp2){
            array_push($data, array_merge([
                "medicine" => $medicine,
                "packaging" => $temp2['packaging'],
                "total" => array_sum($temp2["bhcs"])
            ], $temp2["bhcs"]));
        }

        echo json_encode($data);
    }

    public function getWastedMedicine(Request $req){
        // DB::enableQueryLog();
        $from = now()->parse($req->from)->startOfDay()->toDateTimeString();
        $to = now()->parse($req->to)->startOfDay()->toDateTimeString();

        $data = Stock::select('stocks.*', 'm.name as mname', 'm.code as mcode', 'm.brand as mbrand', 'm.packaging as mpack', 'u.name as rname')
                    ->join('reorders', 'reorders.id', '=', 'stocks.reorder_id')
                    ->join('users as u', 'u.id', '=', 'reorders.user_id')
                    ->join('medicines as m', 'm.id', '=', 'reorders.medicine_id')
                    ->whereBetween('expiry_date', [$from, $to])
                    ->where('reorders.medicine_id', 'like', $req->sku);

        if(auth()->user()->role == "RHU"){
            $data = $data->where('u.id', '=', auth()->user()->id);
            $data = $data->get();
        }
        else{
            // GET FROM CENTRAPHARMS RHU
            $temp = clone $data;
            $temp = $temp->join('rhus as r', 'r.user_id', '=', 'u.id');
            $temp = $temp->where('r.admin_id', '=', auth()->user()->id);
            $temp = $temp->where('r.user_id', 'like', $req->rhu);
            $temp = $temp->get();

            if($req->rhu == "%%"){
                // GET FROM CENTRAPHARM
                $data = $data->where('u.id', auth()->user()->id);
                $data = $data->get();
                $data = $temp->merge($data);
            }
            else{
                $data = $temp;
            }
        }

        // dd(DB::getQueryLog());
        echo json_encode($data);
    }

    public function deliveredRequests(){
        $from = now()->subDays(6)->startOfDay()->toDateTimeString();
        $to = now()->endOfDay()->toDateTimeString();

        $dates = $this->getDates($from, $to);
        $data = Req::whereIn('status', ['Delivered', 'Incomplete Qty'])
                    ->whereBetween('received_date', [$from, $to]);

        $data = $data->join('rhus as r', 'r.user_id', '=', 'requests.user_id');
        if(auth()->user()->role == "RHU"){
            $data = $data->where('r.user_id', '=', auth()->user()->id);
        }
        else{
            $data = $data->where('r.admin_id', '=', auth()->user()->id);
        }
        $data = $data->get();

        $temp = [];
        foreach($dates as $date){
            $date = now()->parse($date)->toDateString();
            $temp[$date] = 0;
        }

        foreach($data as $request){
            $temp[now()->parse($request->received_date)->toDateString()]++;
        }

        $labels = [];
        foreach($dates as $date){
            array_push($labels, now()->parse($date)->format('M d'));
        }

        $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        $dataset = [
            [
                'label' => "Delivered Request",
                'data' => array_values($temp),
                'borderColor' => $color,
                'backgroundColor' => $color,
                'hoverRadius' => 10
            ]
        ];

        echo json_encode(['labels' => $labels, 'dataset' => $dataset]);
    }

    private function getDates($from, $to){
        $dates = [];

        while($from <= $to){
            $tempDate = now()->parse($from);
            array_push($dates, $tempDate->toDateTimeString());
            $from = $tempDate->addDay()->toDateString();
        }

        return $dates;
    }

    private function _view($view, $data = array()){
        return view("reports" . "." . $view, $data);
    }
}
