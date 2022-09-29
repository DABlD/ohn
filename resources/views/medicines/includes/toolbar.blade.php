<h3 class="float-right">
    <a class="btn btn-success btn-sm" data-toggle="tooltip" title="Add Category" onclick="createCategory()">
        <i class="fas fa-plus fa-2xl"></i>
    </a>&nbsp;
    <a class="btn btn-info btn-sm" data-toggle="tooltip" title="Export" onclick="exportSku()">
        <i class="fa-solid fa-file-excel"></i>
    </a>
</h3>
<br><br>

<div class="row">
    <div class="col-md-3">
        <div class="row iRow">
            <div class="col-md-5 iLabel" style="margin: auto;">
                View Franchise's Stock
            </div>
            <div class="col-md-7 iInput">
                <select id="user_id" class="form-control">
                    <option value="{{ auth()->user()->id }}">Select Franchise / All</option>
                </select>
            </div>
        </div>
    </div>
</div>