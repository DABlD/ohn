<?php

namespace App\Http\Controllers;

use Illuminate\Http\{Request, JsonResponse};
use App\Models\Rx;
use DB;
use Exception;

class RxController extends Controller
{
    public function __construct(){
        $this->table = "rxes";
    }

    public function index(){
        return $this->_view('index', [
            'title' => 'RX'
        ]);
    }

    public function get(Request $req){
        $array = Rx::select($req->select);

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
        $data = new Rx();
        $data->doctor_id = $req->doctor_id;
        $data->ticket_number = $req->ticket_number;
        $data->patient_id = $req->patient_id;
        $data->patient_name = $req->patient_name;
        $data->contact = $req->contact;
        $data->address = $req->address;
        $data->amount = $req->amount;
        $data->date = $req->date;

        $data->save();
    }

    public function update(Request $req){
        $query = DB::table($this->table);

        if($req->where){
            $query = $query->where($req->where[0], $req->where[1])->update($req->except(['id', '_token', 'where']));
        }
        else{
            $query = $query->where('id', $req->id)->update($req->except(['id', '_token']));
        }
    }

    public function delete(Request $req){
        Rx::find($req->id)->delete();
    }

    public function receive(Request $req){
        try{
            $data = new Rx();
            $data->doctor_id = $req->doctor_id;
            $data->ticket_number = $req->ticket_number;
            $data->patient_id = $req->patient_id;
            $data->patient_name = $req->patient_name;
            $data->contact = $req->contact;
            $data->address = $req->address;
            $data->amount = $req->amount;
            $data->date = $req->date;

            $data->save();
        } catch (Exception $e) {
            return response()->json([
                'data' => [],
                'message' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        return response()->json([
            'data' => $data,
            'message' => 'Success'
        ], JsonResponse::HTTP_OK);
    }

    private function _view($view, $data = array()){
        return view("rx" . "." . $view, $data);
    }
}
