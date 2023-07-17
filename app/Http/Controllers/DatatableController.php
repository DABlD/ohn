<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User, Rhu, Bhc, Medicine, Category, TransactionType, Request as Req, Rx, Location, Data};
use DB;

class DatatableController extends Controller
{
    public function admin(Request $req){
        $array = User::select($req->select)->withTrashed();

        // IF HAS SORT PARAMETER $ORDER
        if($req->order){
            $array = $array->orderBy($req->order[0], $req->order[1]);
        }

        // IF HAS WHERE
        if($req->where){
            $array = $array->where($req->where[0], $req->where[1]);
        }

        $array = $array->get();

        // IF HAS GROUP
        if($req->group){
            $array = $array->groupBy($req->group);
        }

        // IF HAS LOAD
        if($array->count() && $req->load){
            foreach($req->load as $table){
                $array->load($table);
            }
        }

        foreach($array as $item){
            $item->actions = $item->actions;
        }
        echo json_encode($array->toArray());
    }

    public function rhu(Request $req){
        $array = Rhu::select($req->select);
        $array->where('admin_id', auth()->user()->id);

        // IF HAS SORT PARAMETER $ORDER
        if($req->order){
            $array = $array->orderBy($req->order[0], $req->order[1]);
        }

        // IF HAS WHERE
        if($req->where){
            $array = $array->where($req->where[0], $req->where[1]);
        }

        $array = $array->get();

        // IF HAS GROUP
        if($req->group){
            $array = $array->groupBy($req->group);
        }

        // IF HAS LOAD
        if($array->count() && $req->load){
            foreach($req->load as $table){
                $array->load($table);
            }
        }

        foreach($array as $item){
            $item->actions = $item->actions;
        }
        echo json_encode($array->toArray());
    }

    public function bhc(Request $req){
        $array = Bhc::select($req->select ?? "*");

        // IF HAS JOIN //CUSTOM
        if($req->join){
            $array = $array->join('rhus as r', 'r.id', '=', "bhcs.rhu_id");
        }

        // IF HAS SORT PARAMETER $ORDER
        if($req->order){
            $array = $array->orderBy($req->order[0], $req->order[1]);
        }

        // IF HAS WHERE
        if(auth()->user()->role == "Admin"){
            $array = $array->where('r.admin_id', auth()->user()->id);
        }
        else{
            $array = $array->where('r.user_id', auth()->user()->id);
        }

        $array = $array->get();
        
        // IF HAS GROUP
        if($req->group){
            $array = $array->groupBy($req->group);
        }

        // IF HAS LOAD
        if($array->count() && $req->load){
            foreach($req->load as $table){
                $array->load($table);
            }
        }

        foreach($array as $item){
            $item->actions = $item->actions;
        }
        $array = $this->addRhus($array);
        echo json_encode($array->toArray());
    }

    private function addRhus($array){
        $rhus = Rhu::all();

        foreach($rhus as $rhu){
            $temp = new Rhu();
            $temp->id = null;
            $temp->rhu = $rhu;
            $temp->name = null;
            $temp->code = null;
            $temp->region = null;
            $temp->municipality = null;
            $temp->actions = null;

            $array->push($temp);
        }

        return $array;
    }

    public function medicine(Request $req){
        $array = Medicine::select($req->select)
                    ->join("reorders as r", "r.medicine_id", '=', 'medicines.id');

        // IF HAS SORT PARAMETER $ORDER
        if($req->order){
            $array = $array->orderBy($req->order[0], $req->order[1]);
        }
        
        // IF HAS WHERE
        if($req->where){
            $array = $array->where($req->where[0], $req->where[1]);
            $array = $array->where('r.deleted_at', null);
        }

        $array = $array->get();
        // IF HAS GROUP
        if($req->group){
            $array = $array->groupBy($req->group);
        }

        // IF HAS LOAD
        if($array->count() && $req->load){
            foreach($req->load as $key => $table){
                $array->load($table);
            }
        }

        foreach($array as $key => $item){
            $item->actions = $item->actions;
        }

        $array = $this->addCategories($array)->values();
        echo json_encode($array->toArray());
    }

    public function medicine2(Request $req){
        $array = Medicine::select($req->select)
                    ->join("reorders as r", "r.medicine_id", '=', 'medicines.id');

        // IF HAS SORT PARAMETER $ORDER
        if($req->order){
            $array = $array->orderBy($req->order[0], $req->order[1]);
        }
        
        // IF HAS WHERE
        if($req->where){
            $array = $array->where($req->where[0], $req->where[1]);
            $array = $array->where('r.user_id', auth()->user()->id);
            $array = $array->where('r.deleted_at', null);
        }

        if(auth()->user()->role != "Admin"){
            $array = $array->where('r.user_id', auth()->user()->id);
        }

        $array = $array->get();
        // IF HAS GROUP
        if($req->group){
            $array = $array->groupBy($req->group);
        }

        // IF HAS LOAD
        if($array->count() && $req->load){
            foreach($req->load as $table){
                $array->load($table);
            }
        }

        foreach($array as $item){
            $item->actions = $item->actions;
        }

        echo json_encode($array->toArray());
    }

    private function addCategories($array){
        $categories = Category::select('categories.*');

        if(auth()->user()->role == "Admin"){
            $categories = $categories->where('admin_id', auth()->user()->id);
        }
        elseif(auth()->user()->role == "RHU"){
            $categories = $categories->join('rhus as r', 'r.admin_id', '=', 'categories.admin_id');
            $categories = $categories->where('r.user_id', auth()->user()->id);
        }

        $categories = $categories->get();

        foreach($categories as $category){

            $temp = new Medicine();
            $temp->id = null;
            $temp->category = $category;
            $temp->image = null;
            $temp->code = null;
            $temp->brand = null;
            $temp->name = null;
            $temp->packaging = null;
            $temp->unit_price = null;
            $temp->cost_price = null;
            $temp->reorder = (object)["point" => null, "stock" => null];
            $temp->point = null;
            $temp->rs = null;
            $temp->actions = null;

            $array->push($temp);
        }

        return $array;
    }

    public function transactionType(Request $req){
        $array = TransactionType::select($req->select);

        if(auth()->user()->role == "Admin"){
            $array = $array->where('admin_id', auth()->user()->id);
        }
        elseif(auth()->user()->role == "RHU"){
            $array = $array->join('rhus as r', 'r.admin_id', '=', 'transaction_types.admin_id');
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

        // IF HAS GROUP
        if($req->group){
            $array = $array->groupBy($req->group);
        }

        // IF HAS LOAD
        if($array->count() && $req->load){
            foreach($req->load as $table){
                $array->load($table);
            }
        }

        foreach($array as $item){
            $item->actions = $item->actions;
        }
        echo json_encode($array->toArray());
    }

    public function approver(Request $req){
        $array = User::select($req->select);
        $array = $array->where('admin_id', auth()->user()->id);

        // IF HAS SORT PARAMETER $ORDER
        if($req->order){
            $array = $array->orderBy($req->order[0], $req->order[1]);
        }

        // IF HAS WHERE
        if($req->where){
            $array = $array->where($req->where[0], $req->where[1]);
        }

        $array = $array->get();

        // IF HAS GROUP
        if($req->group){
            $array = $array->groupBy($req->group);
        }

        // IF HAS LOAD
        if($array->count() && $req->load){
            foreach($req->load as $table){
                $array->load($table);
            }
        }

        foreach($array as $item){
            $item->actions = $item->actions;
        }
        echo json_encode($array->toArray());
    }

    public function requests(Request $req){
        $array = Req::select($req->select);
        $array = $array->where('status', 'like', $req->status);

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

        if($req->like){
            $array = $array->where($req->like[0], $req->like[1], $req->like[2]);
        }

        $array = $array->get();

        // IF HAS LOAD
        if($array->count() && $req->load){
            foreach($req->load as $table){
                $array->load($table);
            }
        }

        foreach($array as $item){
            $item->actions = $item->actions;
        }

        // IF HAS GROUP
        if($req->group){
            $array = $array->groupBy($req->group);
        }

        // NEW FORMAT
        // NEW FORMAT
        // NEW FORMAT
        $temp = [];
        foreach($array as $requests){
            $req = $requests[0];
            $arr = [
                "id" => $req->id,
                "reference" => $req->reference,
                "rhu" => ["company_name" => $req->rhu->company_name],
                "requested_by" => $req->requested_by,
                "transaction_date" => $req->transaction_date->toDateString(),
                "status" => '',
                "actions" => '',
                "requests" => $requests
            ];

            array_push($temp, $arr);
        }
        echo json_encode($temp);
    }

    public function receive(Request $req){
        $array = Req::select($req->select);

        // IF HAS SORT PARAMETER $ORDER
        if($req->order){
            $array = $array->orderBy($req->order[0], $req->order[1]);
        }

        // IF HAS WHERE
        if($req->where){
            $array = $array->where($req->where[0], $req->where[1]);
            $array = $array->where($req->where[2], $req->where[3], $req->where[4]);
        }

        // IF HAS WHEREIN
        if($req->whereIn){
            $array = $array->whereIn($req->whereIn[0], $req->whereIn[1]);
        }

        $array = $array->get();

        // IF HAS GROUP
        if($req->group){
            $array = $array->groupBy($req->group);
        }

        // IF HAS LOAD
        if($array->count() && $req->load){
            foreach($req->load as $table){
                $array->load($table);
            }
        }

        foreach($array as $item){
            $item->actions = $item->actions;
        }

        echo json_encode($array->toArray());
    }

    public function rx(Request $req){
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

        // IF HAS GROUP
        if($req->group){
            $array = $array->groupBy($req->group);
        }

        // IF HAS LOAD
        if($array->count() && $req->load){
            foreach($req->load as $table){
                $array->load($table);
            }
        }

        foreach($array as $item){
            $item->actions = $item->actions;
        }
        echo json_encode($array->toArray());
    }

    public function location(Request $req){
        $array = Location::select($req->select);
        $array = $array->where('admin_id', auth()->user()->id);

        // IF HAS SORT PARAMETER $ORDER
        if($req->order){
            $array = $array->orderBy($req->order[0], $req->order[1]);
        }

        // IF HAS WHERE
        if($req->where){
            $array = $array->where($req->where[0], $req->where[1]);
        }

        $array = $array->get();

        // IF HAS GROUP
        if($req->group){
            $array = $array->groupBy($req->group);
        }

        // IF HAS LOAD
        if($array->count() && $req->load){
            foreach($req->load as $table){
                $array->load($table);
            }
        }

        foreach($array as $item){
            $item->actions = $item->actions;
        }
        echo json_encode($array->toArray());
    }

    public function data(Request $req){
        $array = Data::select($req->select);

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

        // foreach($array as $item){
        //     $item->actions = $item->actions;
        // }
        echo json_encode($array);
    }
}