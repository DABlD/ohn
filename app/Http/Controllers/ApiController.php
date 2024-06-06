<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\{Category, Medicine, Reorder, Stock};

class ApiController extends Controller
{
    public function allSku(){
        $temp = Medicine::all();
        $temp->load('category');
        $temp->load('reorder.stocks');

        $arr = [];
        foreach($temp as $sku){
            $stock = 0;
            $price = 0;
            $lot_number = null;
            $ref_id = null;

            if(sizeof($sku->reorder->stocks)){
                foreach($sku->reorder->stocks as $temp2){
                    array_push($arr, [
                        "image" => $sku->image,
                        "code" => $sku->code,
                        "brand" => $sku->brand,
                        "name" => $sku->name,
                        "packaging" => $sku->packaging,
                        "category" => $sku->category->name,
                        "stock" => $temp2->qty,
                        "price" => $temp2->unit_price,
                        "lot_number" => $temp2->lot_number,
                        "ref_id" => $temp2->id
                    ]);
                }
            }
        }

        return $arr;
    }

    public function getByCategories(Request $req){
        $temp = Medicine::where('category_id', $req->id)->get();
        $temp->load('category');
        $temp->load('reorder.stocks');

        $arr = [];
        foreach($temp as $sku){
            $stock = 0;
            $price = 0;
            $lot_number = null;
            $ref_id = null;

            if(sizeof($sku->reorder->stocks)){
                foreach($sku->reorder->stocks as $temp2){
                    array_push($arr, [
                        "image" => $sku->image,
                        "code" => $sku->code,
                        "brand" => $sku->brand,
                        "name" => $sku->name,
                        "packaging" => $sku->packaging,
                        "category" => $sku->category->name,
                        "stock" => $temp2->qty,
                        "price" => $temp2->unit_price,
                        "lot_number" => $temp2->lot_number,
                        "ref_id" => $temp2->id
                    ]);
                }
            }
        }

        return $arr;
    }

    public function getSku(Request $req){
        $temp = Stock::where("id", $req->ref_id)->first();

        if($temp){
            $temp->load('reorder.medicine.category');

            $arr = [
                "image" => $temp->reorder->medicine->image,
                "code" => $temp->reorder->medicine->code,
                "brand" => $temp->reorder->medicine->brand,
                "name" => $temp->reorder->medicine->name,
                "packaging" => $temp->reorder->medicine->packaging,
                "category" => $temp->reorder->medicine->category->name,
                "stock" => $temp->qty,
                "price" => $temp->unit_price,
                "lot_number" => $temp->lot_number,
                "ref_id" => $temp->id
            ];
        }
        else{
            $arr = [
                "status" => "error",
                "message" => "Does not exist"
            ];
        }

        return $arr;
    }

    public function getCategories(){
        return Category::pluck('name', 'id');
    }
}
