<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Bhc, Rhu};
use DB;

class BhcController extends Controller
{
    public function __construct(){
        $this->table = "bhcs";
    }

    public function index(){
        return $this->_view('index', [
            'title' => 'Branch'
        ]);
    }

    public function get(Request $req){
        $array = Bhc::select($req->select);

        // IF HAS JOIN
        if($req->join){
            $array = $array->join('rhus as r', 'r.id', '=', 'bhcs.rhu_id');
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

    public function get2(Request $req){
        $array = Bhc::select($req->select);
        $rhu = Rhu::where('user_id', auth()->user()->id)->first();
        $array = $array->where('rhu_id', $rhu->id);

        // IF HAS SORT PARAMETER $ORDER
        if($req->order){
            $array = $array->orderBy($req->order[0], $rhu->id);
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
        $entry = new Bhc();
        $entry->rhu_id = $req->rhu_id;
        $entry->name = $req->name;
        $entry->code = $req->code;
        $entry->region = $req->region;
        $entry->municipality= $req->municipality;
        $entry->save();
    }

    public function update(Request $req){
        DB::table($this->table)->where('id', $req->id)->update($req->except(['id', '_token']));
    }

    public function delete(Request $req){
        Bhc::find($req->id)->delete();
    }

    private function _view($view, $data = array()){
        return view($this->table . "." . $view, $data);
    }
}