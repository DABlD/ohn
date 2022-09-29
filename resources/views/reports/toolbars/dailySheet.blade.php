<div class="row">
	<div class="col-md-3">

	    <div class="row iRow">
	        <div class="col-md-4 iLabel" style="margin: auto;">
	            Select Branch
	        </div>
	        <div class="col-md-8 iInput">
	        	<select id="bhc_id" class="form-control">
	        	</select>
	        </div>
	    </div>

	</div>

	<div class="col-md-3">

	    <div class="row iRow">
	        <div class="col-md-4 iLabel" style="margin: auto;">
	            View
	        </div>
	        <div class="col-md-8 iInput">
	        	<select id="view" class="form-control">
	        		<option value="qty">Quantity</option>
	        		<option value="amount">Amount</option>
	        	</select>
	        </div>
	    </div>

	</div>

	<div class="col-md-3"></div>
	<div class="col-md-3">

	    <div class="row iRow float-right">
	    	<a class="btn btn-success btn-sm" data-toggle="tooltip" title="Filter" onclick="filter()">
	    	    <i class="fas fa-filter"></i>
	    	</a>&nbsp;
	    	
    		<a class="btn btn-info btn-sm" data-toggle="tooltip" title="Export" onclick="exportReport()">
    		    <i class="fa-solid fa-file-excel"></i>
    		</a>
	    </div>

	</div>
</div>

<br>

<div class="row">
	<div class="col-md-3" id="from"></div>
	<div class="col-md-3" id="to"></div>
</div>