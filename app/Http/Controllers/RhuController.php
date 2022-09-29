<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User, Rhu};
use DB;

class RhuController extends Controller
{
    public function __construct(){
        $this->table = "rhus";
    }

    public function index(){
        return $this->_view('index', [
            'title' => 'Franchise'
        ]);
    }

    public function get(Request $req){
        $array = Rhu::select($req->select);

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
        $user = new User();
        $user->name = $req->name;
        $user->contact = $req->contact;
        $user->email = $req->email;
        $user->address = $req->address;
        $user->username = $req->username;
        $user->password = $req->password;
        $user->role = "RHU";
        $user->save();

        $rhu = new RHU();
        $rhu->user_id = $user->id;
        $rhu->admin_id = auth()->user()->id;
        $rhu->company_name = $req->company_name;
        $rhu->contact_personnel = $req->contact_personnel;
        $rhu->company_code = ""; //LEAVE TO MUTATORS
        $rhu->save();
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
        Rhu::find($req->id)->delete();
    }

    private function _view($view, $data = array()){
        return view($this->table . "." . $view, $data);
    }
}
