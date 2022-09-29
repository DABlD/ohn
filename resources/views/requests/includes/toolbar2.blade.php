<h3 class="float-right">    
    <a class="btn btn-info btn-sm" data-toggle="tooltip" title="Export" onclick="exportReport()">
        <i class="fa-solid fa-file-excel"></i>
    </a>
</h3>
<br><br>

<div class="row">
    <div class="col-md-3">
        <div class="row iRow">
            <div class="col-md-4 iLabel" style="margin: auto;">
                Status
            </div>
            <div class="col-md-8 iInput">
                <select id="search" class="form-control">
                    <option value="%%">All</option>
                    <option value="For Approval">For Approval</option>
                    <option value="Approved">Approved</option>
                    <option value="For Delivery">For Delivery</option>
                    <option value="Incomplete Qty">Incomplete Qty</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Cancelled">Cancelled</option>
                    <option value="Declined">Declined</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">

        <div class="row iRow">
            <div class="col-md-4 iLabel" style="margin: auto;">
                Franchise
            </div>
            <div class="col-md-8 iInput">
                <select id="user_id" class="form-control">
                    <option value="%%">All</option>
                </select>
            </div>
        </div>

    </div>
</div>
