<?php

namespace App\Traits;

trait BhcAttribute{
	public function getActionsAttribute(){
		$id = $this->id;

		$str = 	"<a class='btn btn-success' data-toggle='tooltip' title='View' onClick='view($id)'>" .
			        "<i class='fas fa-search'></i>" .
			    "</a>&nbsp;";

		if(auth()->user()->role == "RHU"){
			$str .=	"<a class='btn btn-danger' data-toggle='tooltip' title='Delete' onClick='del($id)'>" .
			        	"<i class='fas fa-trash'></i>" .
			    	"</a>&nbsp;";
		}

		return $str;
	}
}