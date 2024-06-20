<?php

namespace App\Traits;

trait RxAttribute{
	public function getActionsAttribute(){
		$id = $this->id;

		// return "";
		return 
		"<a class='btn btn-success' data-toggle='tooltip' title='View Prescriptions' onClick='prescriptions($id)'>" .
	        "<i class='fas fa-file-prescription'></i>" .
	    "</a>&nbsp;";
		// "<a class='btn btn-danger' data-toggle='tooltip' title='Delete' onClick='del($id)'>" .
	 //        "<i class='fas fa-trash'></i>" .
	 //    "</a>&nbsp;";
	}
}