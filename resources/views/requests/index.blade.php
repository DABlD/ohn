@extends('layouts.app')
@section('content')

<section class="content">
    <div class="container-fluid">
        <div class="row">

	        <div class="col-lg-3 col-6">
	            <div class="small-box bg-info">
	                <div class="inner">
	                    <h3>
	                    	For Approval:
	                    	<span id="fa">0</span>
	                    </h3>
	                </div>

	                <div class="icon">
	                    <i class="fa-solid fa-clipboard-list-check"></i>
	                </div>
	            </div>
	        </div>
        	
	        <div class="col-lg-3 col-6">
	            <div class="small-box bg-warning">
	                <div class="inner">
	                    <h3>
	                    	Approved:
	                    	<span id="fi">0</span>
	                    </h3>
	                </div>

	                <div class="icon">
	                    <i class="fa-solid fa-check"></i>
	                </div>
	            </div>
	        </div>

	        <div class="col-lg-3 col-6">
	            <div class="small-box bg-success">
	                <div class="inner">
	                    <h3>
	                    	For Delivery:
	                    	<span id="fd">0</span>
	                    </h3>
	                </div>

	                <div class="icon">
	                    <i class="fa-solid fa-truck-arrow-right"></i>
	                </div>
	            </div>
	        </div>

        </div>

        <div class="row">
            <section class="col-lg-12 connectedSortable">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table mr-1"></i>
                            List
                        </h3>

                        @if(auth()->user()->role == "RHU")
                        	@include('requests.includes.toolbar')
                        @elseif(auth()->user()->role == "Admin")
                        	@include('requests.includes.toolbar2')
                        @endif
                    </div>

                    <div class="card-body table-responsive">
                    	<table id="table" class="table table-hover">
                    		<thead>
                    			<tr>
                    				<th>Franchise</th>
                    				<th>Ref No</th>
                    				<th>Particulars</th>
                    				<th>Request Date</th>
                    				<th>Status</th>
                    				<th>Action</th>
                    			</tr>
                    		</thead>

                    		<tbody>
                    		</tbody>
                    	</table>
                    </div>
                </div>
            </section>
        </div>
    </div>

</section>

@endsection

@push('styles')
	<link rel="stylesheet" href="{{ asset('css/datatables.min.css') }}">
	<link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">

	<style>
		.icon i{
			font-size: 50px !important;
		}

		.bg-warning .inner{
			color: white;
		}

		.reqRow{
			font-size: 25px;
			text-align: left;
		}

		.reqLabel{
			font-weight: bold;
		}

		.reqRow:nth-child(2){
			margin-top: 10px;
		}

		[name="approved_qty"]{
			text-align: center;
		}

		#table2 tbody td{
			vertical-align: middle;
		}
	</style>
@endpush

@push('scripts')
	<script src="{{ asset('js/datatables.min.js') }}"></script>
	<script src="{{ asset('js/select2.min.js') }}"></script>

	<script>
		var search = "%%";
		var reqByRef = [];
		var user_id = "{{ auth()->user()->role == "RHU" ? auth()->user()->id : "%%" }}"

		$(document).ready(()=> {
			var table = $('#table').DataTable({
				ajax: {
					url: "{{ route('datatable.requests') }}",
                	dataType: "json",
                	dataSrc: "",
					data: f => {
						f.table = 'requests';
						f.select = ['requests.*'];
						f.load = ['rhu', 'medicine.category', 'reorder'];
						f.order = ["created_at", "desc"];
						f.status = search;
						f.group = "reference";
						f.like = ['requests.user_id', "like", user_id];
					}
				},
				columns: [
					{data: 'rhu.company_name', visible: false},
					{data: 'reference'},
					{data: 'requested_by'},
					{
						data: 'transaction_date',
						render: date => {
							return moment(date).format('MMM DD, YYYY');
						}
					},
					{data: 'status', visible: false},
					{
						data: 'actions', 
						width: "10%",
						render: (a, d, r) => {
							let ref = r.reference;
							reqByRef[ref] = r.requests;

							return `
								<a class='btn btn-success' data-toggle='tooltip' title='View' onClick="viewRequest('${ref}', false)">
							        <i class='fas fa-search'></i>
							    </a>&nbsp;
							`;
						}
					},
				],
        		order: [],
        		pageLength: 25,
        		@if(auth()->user()->role == "Approver")
        		language: {
        			emptyTable: "No pending request for approval"
        		},
        		@endif
        		rowCallback: function( row, data, index ) {
				    if (data['id'] == null) {
				        $(row).hide();
				    }
				},
		        drawCallback: function (settings) {
		            let api = this.api();
		            let rows = api.rows({ page: 'current' }).nodes();
		            let last = null;
		 
		            api.column(0, { page: 'current' })
		                .data()
		                .each(function (company, i, row) {
		                    if (last !== company) {
		                        $(rows)
		                            .eq(i)
		                            .before(`
		                            	<tr class="group">
		                            		<td colspan="4">
		                            			${company}
		                            		</td>
		                            	</tr>
		                            `);
		 
		                        last = company;
		                    }
		                });

		        	let grps = $('[class="group"]');
		        	grps.each((index, group) => {
		        		if(!$(group).next().is(':visible')){
		        			$(group).remove();
		        		}
		        	});

		        	$('[name="approved_qty"]').css('text-align', 'center');
		        },
			});
			
			initCount();
        	$('#search').on('change', e => {
        		search = e.target.value;
        		reload();
        	});
        	initRhu();
        	$('#user_id').on('change', e => {
        		user_id = e.target.value;
        		reload();
        	});
		});

		function initRhu(){
			$.ajax({
				url: "{{ route('rhu.get') }}",
				data: {
					select: ['company_name', 'user_id'],
				},
				success: rhus => {
					rhus = JSON.parse(rhus);

					let rhuString = "";
					rhus.forEach(rhu => {
						rhuString += `
							<option value="${rhu.user_id}">${rhu.company_name}</option>
						`;
					});

					$('#user_id').append(rhuString);
					$('#user_id').on('change', e => {
						user_id = e.target.value;
						reload();
					});
				}
			});
		}

		function initCount(){
			$.ajax({
				url: '{{ route("request.getPendingRequests") }}',
				data: {
					select: ['status'],
				},
				success: requests => {
					requests = JSON.parse(requests);
					
					let fa = 0;
					let fd = 0;
					let fi = 0;

					requests.forEach(request => {
						if(request.status == "For Approval"){
							fa++;
						}
						else if(request.status == "Approved"){
							fi++;
						}
						else if(request.status == "For Delivery"){
							fd++;
						}
					});

					$('#fa').html(fa);
					$('#fd').html(fd);
					$('#fi').html(fi);
				}
			})
		}

		function updateStatus(id, action, status, ref = null){
			swal.showLoading();
			if(action == "Approve"){
				let qty = $(`#qty${id}`).find('input');
				let stock = $(`#stock${id}`).text().trim();

				if(qty.val() < qty.attr('min') || qty.val() > qty.attr('max')){
					Swal.fire({
						icon: "error",
						title: `Qty  must be ≥ ${qty.attr('min')} and ≤ ${qty.attr('max')}`,
					}).then(() => {
						viewRequest(ref, false);
					});
				}
				else if(parseInt(qty.val()) > parseInt(stock)){
					Swal.fire({
						icon: "error",
						title: `Qty is greater than stock`,
					}).then(() => {
						viewRequest(ref, false);
					});
				}
				else{
					doUpdate(id, status, qty.val(), dateTimeNow(), ref);
				}
			}
			else{
				doUpdate(id, status, 0, null, ref);
			}
		}

		function doUpdate(id, status, qty = 0, date_approved = null, ref){
			update({
				url: "{{ route('request.update') }}",
				data: {
					id: id,
					status: status,
					approved_qty: qty,
					date_approved: date_approved
				},
				message: "Success"
			}, () => {
				reload();
				viewRequest(ref);
			})
		}

		// FOR GROUP UPDATE. NO SUCCESS EACH UPDATE
		function doUpdate2(id, status, qty = 0, date_approved = null, ref, bool){
			update({
				url: "{{ route('request.update') }}",
				data: {
					id: id,
					status: status,
					approved_qty: qty,
					date_approved: date_approved
				}
			}, () => {
				console.log(bool);
				if(bool){
					ss('Successfully ' + status);
					reload();
					viewRequest(ref);
				}
			})
		}

		function inputInfo(ref){
			window.location.href = `{{ route('request.inputInfo') }}?ref=${ref}`;
		}

		function exportReport(){
			let data = {
				search: search,
				table: 'requests',
				select: ['requests.*'],
				load: ['rhu', 'medicine.category'],
				order: ["created_at", "desc"]
			};

			window.open("/export/exportRequests?" + $.param(data), "_blank");
		}

		function viewRequest(reference, bool = true){
			if(bool){
				setTimeout(() => {
					doViewRequest(reference);
				}, 800);
			}
			else{
				doViewRequest(reference);
			}
		}

		function doViewRequest(reference){
			let reqs = reqByRef[reference];
			let reqString = "";
			let reqName = null;
			let reqReference = null;
			let reqRequested_by = null;
			let reqDate = null;

			reqs.forEach(req => {
				reqName = !reqName ? req.rhu.company_name : reqName;
				reqReference = !reqReference ? req.reference : reqReference;
				reqRequested_by = !reqRequested_by ? req.requested_by : reqRequested_by;
				reqDate = !reqDate ? req.transaction_date : reqDate;

				let aQty = req.approved_qty ?? "-";
				@if(auth()->user()->role != "RHU")
				if(req.status == "For Approval"){
					aQty = `
						<div id=qty${req.id}>
							${input("approved_qty", "", req.request_qty, 0, 12, 'number', ` min=1 max="${req.request_qty}"`)}
						</div>
					`;
				}
				@endif

				let isChecked = req.status == "For Approval" ? "checked" : "disabled";

				reqString += `
					<tr>
						<td>
							<input type="checkbox" class="cb" data-id="${req.id}" ${isChecked}>
						</td>
						<td>${req.id}</td>
						<td>${req.medicine.name}</td>
						<td id="stock${req.id}">${req.reorder.stock}</td>
						<td>${req.request_qty}</td>
						<td id="qty${req.id}">${aQty}</td>
						<td>${req.date_approved ? moment(req.date_approved).format(dateFormat2) : "-"}</td>
						<td>${req.amount}</td>
						<td>${req.status}</td>
						<td>${req.status == "For Approval" || req.status == "Approved" ? req.actions : ""}</td>
					</tr>
				`;
			});

			let ids = [];
			let qtys = [];

			Swal.fire({
				html: `
					<div class="row reqRow">
						<div class="col-md-5">
							<div class="row">
								<div class="col-md-5 reqLabel">
									Franchise:
								</div>
								<div class="col-md-7">
									${reqName}
								</div>
							</div>
						</div>
						<div class="col-md-2"></div>
						<div class="col-md-5">
							<div class="row">
								<div class="col-md-5 reqLabel">
									Reference:
								</div>
								<div class="col-md-7">
									${reqReference}
								</div>
							</div>
						</div>
					</div>

					<div class="row reqRow">
						<div class="col-md-5">
							<div class="row">
								<div class="col-md-5 reqLabel">
									Particulars:
								</div>
								<div class="col-md-7">
									${reqRequested_by}
								</div>
							</div>
						</div>
						<div class="col-md-2"></div>
						<div class="col-md-5">
							<div class="row">
								<div class="col-md-5 reqLabel">
									Date:
								</div>
								<div class="col-md-7">
									${moment(reqDate).format(dateFormat2)}
								</div>
							</div>
						</div>
					</div>

					<br><br>
					<table id="table2" class="table table-hover">
						<thead>
							<tr>
								<th></th>
								<th>ID</th>
								<th>Item</th>
								<th>Stock</th>
								<th>Request Qty</th>
								<th>Approved Qty</th>
								<th>Date Approved</th>
								<th>Amount</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>

						<tbody>
							${reqString}
						</tbody>
					</table>
				`,
				didOpen: () => {
					$('#table2').DataTable();
				},
				showClass: {
					backdrop: 'swal2-noanimation',
					popup: '',
				},
				hideClass: {
					popup: '',
				},
				preConfirm: () => {
					swal.showLoading();
					return new Promise(resolve => {
						ids = [];

						$('.cb:checked').each((i, e) => {
							ids.push(e.dataset.id);
						});

						ids.forEach(id => {
							let stock = $(`#stock${id}`).text();
							let qty = $(`#qty${id}`).find('input');
							qtys[id] = qty.val();

							$(qty).removeClass('glow');

							if(qty.val() < qty.attr('min') || qty.val() > qty.attr('max')){
								Swal.showValidationMessage(`Qty  must be ≥ ${qty.attr('min')} and ≤ ${qty.attr('max')}`);
								$(qty).addClass('glow');
							}
							else if(parseInt(qty.val()) > parseInt(stock)){
								Swal.showValidationMessage("Qty is greater than stock");
								$(qty).addClass('glow');
							}
						});

						resolve();
					});
				},
				preDeny: () => {
					swal.showLoading();
					return new Promise(resolve => {
						ids = [];

						$('.cb:checked').each((i, e) => {
							ids.push(e.dataset.id);
						});

						resolve();
					});
				},
				width: '70%',
				@if(auth()->user()->role == "RHU")
					showDenyButton: true,
					denyButtonText: 'Cancel Selected',
					denyButtonColor: errorColor,
					showConfirmButton: false,
				@else
					confirmButtonText: "Approve Selected",
					confirmButtonColor: successColor,
					showDenyButton: true,
					denyButtonText: 'Decline Selected',
					denyButtonColor: errorColor,
				@endif
				showCancelButton: true,
				cancelButtonText: "Exit"
			}).then(result => {
				let ctr = 0;
				if(result.value){
					swal.showLoading();
					ids.forEach(id => {
						ctr++;
						doUpdate2(id, "Approved", qtys[id], dateTimeNow(), reference, ctr == ids.length);
					});
				}
				else if(result.isDenied){
					swal.showLoading();
					ids.forEach(id => {
						ctr++;
						doUpdate2(id, "{{ auth()->user()->role == "RHU" ? "Cancelled" : "Declined" }}", 0, null, reference, ctr == ids.length);
					});
				}
			});
		}

		// REFRESH EVERY 60 SECONDS
		function refresh(){
			setTimeout(() => {
				reload();
				initCount();
				refresh();
				console.log('Refreshed');
			}, 60000); //60 SECONDS
		}
		refresh();

		function seenNewAlerts(){
			$.ajax({
				url: '{{ route('request.seenNewAlerts') }}',
				success: () => {
					setTimeout(() => {
						seenNewAlerts();
					}, 7000)
				}
			});
		}
		seenNewAlerts();
	</script>
@endpush