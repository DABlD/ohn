@extends('layouts.app')
@section('content')

<section class="content">
    <div class="container-fluid">

        <div class="row">
            <section class="col-lg-12">
        	@if(auth()->user()->role == "RHU")
        		@include('requests.includes.toolbar')
        	@endif
        </div>

        <div class="row">
            <section class="col-lg-4 connectedSortable">
                <div class="card">


                    <div class="card-header center">
                        <h3 class="card-title">
                            SELECTION
                        </h3>
                    </div>

                    <div class="card-body table-responsive selection">
                    	<div class="row">
	        			    <div class="col-md-4 iLabel">
	        			        Category
	        			    </div>
				            <div class="col-md-8 iInput">
				                <select name="category" class="form-control">
				                	<option value=""></option>
				                </select>
				            </div>
                    	</div>
                    	<br>
                    	<div class="row">
                    		<table id="table" class="table table-hover">
                    			<thead>
                    				<tr>
                    					<th>CATEGORY</th>
                    				</tr>
                    			</thead>

                    			<tbody>
                    			</tbody>
                    		</table>
                    	</div>
                    </div>
                </div>
            </section>

            <section class="col-lg-8 connectedSortable">
                <div class="card">
                    <div class="card-header center">
                        <h3 class="card-title">
                            DETAILS
                        </h3>
                    </div>

                    <div class="card-body table-responsive selection">

                    	<div class="row">
                    		
                    		<div class="col-md-7">
                    			
                    			<div class="row">
                    			    <div class="col-md-3 iLabel">
                    			    	Reference No
                    			    </div>
            			            <div class="col-md-9 iInput">
            			                <input type="text" name="reference" placeholder="Enter Reference No." class="form-control">
            			            </div>
                    			</div>

                    		</div>

                    		<div class="col-md-5">
                    			
                    			<div class="row">
                    			    <div class="col-md-3 iLabel">
                    			        Date
                    			    </div>
            			            <div class="col-md-9 iInput">
            			                <input type="text" name="transaction_date" placeholder="Select Transaction Date" class="form-control">
            			            </div>
                    			</div>

                    		</div>
                    	</div>

                    	<br>
                    	<div class="row">
                    		<div class="col-md-7">
                    			
                    			<div class="row">
                    			    <div class="col-md-3 iLabel">
                    			        Particulars
                    			    </div>
            			            <div class="col-md-9 iInput">
            			                <textarea name="requested_by" class="form-control" cols="10" rows="2"></textarea>
            			            </div>
                    			</div>

                    		</div>

                    		<div class="col-md-5"></div>
                    	</div>

                    	<br>
                    	<div class="row">
                    		<div class="col-md-12">
                    			<table id="table2" class="table table-hover">
                    				<thead>
                    					<tr>
                    						<th>Item</th>
                    						<th>Qty</th>
                    						<th>Price</th>
                    						<th>Amount</th>
                    						<th>Actions</th>
                    					</tr>
                    				</thead>

                    				<tbody>
                    				</tbody>
                    			</table>
                    		</div>
                    	</div>

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
	<link rel="stylesheet" href="{{ asset('css/flatpickr.min.css') }}">
	<style type="text/css">
		.card-header{
			background-color: #66b966;
		}
		.card-title{
			font-weight: bold;
			color: white;
			margin: auto;
			float: none;
		}
		.row .col-md-6, .iLabel, .iInput{
			margin: auto;
		}
		#table_wrapper, #table_filter, #table_filter label, [type|="search"] {
			width: 100%;
		}
		[type|="search"]{
			margin-left: 0 !important;
		}
		#table2 tbody td{
			vertical-align: middle;
		}
		#table2 thead th{
			text-align: center;
		}
	</style>
	{{-- <link rel="stylesheet" href="{{ asset('css/datatables-jquery.min.css') }}"> --}}
@endpush

@push('scripts')
	<script src="{{ asset('js/datatables.min.js') }}"></script>
	<script src="{{ asset('js/select2.min.js') }}"></script>
	<script src="{{ asset('js/flatpickr.min.js') }}"></script>
	{{-- <script src="{{ asset('js/datatables-jquery.min.js') }}"></script> --}}

	<script>
		$('#statusFilter').remove();
		
		var category = 0;
		var medicines = [];

		$(document).ready(()=> {

			var table = $('#table').DataTable({
				ajax: {
					url: "{{ route('datatable.medicine2') }}",
                	dataType: "json",
                	dataSrc: "",
					data: d => {
					   d.table = 'medicines';
					   d.select = ['medicines.*'];
					   d.where = ["category_id", category];
					   d.load = ["reorder.stocks"]
					}
				},
				columns: [
					{
						render: (id, display, row) => {
							let price = 0;

							row.reorder.stocks.forEach(stock => {
								price += stock.unit_price;
							});

							price = (price / row.reorder.stocks.length);
							return `
								<div class="row">
									<div class="col-md-8" style="font-weight: bold;">
										${row.code}
									</div>
									<div class="col-md-4" style="text-align: right;">
										<a class="btn btn-success btn-sm" data-toggle="tooltip" title="Add" onclick="add(${row.id})">
										    <i class="fas fa-plus fa-2xl"></i>
										</a>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										${row.name} ${row.packaging}
									</div>
								</div>
								<div class="row">
									<div class="col-md-8">
										${row.brand}
									</div>
									<div class="col-md-4" style="text-align: right;">
										₱${toFloat(price)}
									</div>
								</div>
							`;
						}
					},
				],
        		pageLength: 1000,
        		lengthChange: false,
        		paging: false,
        		info: false,
        		language: {
        			search: "",
        			emptyTable: "No Selected Category"
        		},
				drawCallback: () => {
					//150 = height elements above it
					$('.selection ').css("height", window.outerHeight - 150);
					$('[type="search"]input').attr("placeholder", "Search");
				}
			});

			initStyles();
			initCategories();
			initTransactionDate();
			addFooter();
			computeTotal();
		});

		function initStyles(){
			$('.iInput').css('margin', 'auto');
		}

		function initCategories(){
			$.ajax({
				url: "{{ route('medicine.getCategories') }}",
				data: {
					select: "categories.*",
					join: true,
					where: ['r.user_id', {{ auth()->user()->id }}]
				},
				success: categories => {
					categories = JSON.parse(categories);
					let categoryString = "";

					categories.forEach(temp => {
						categoryString += `
							<option value="${temp.id}">${temp.name}</option>
						`;
					});

					$('[name="category"]').append(categoryString);
					$('[name="category"]').select2({
						placeholder: "Select Category"
					});
					$('[name="category"]').on('change', temp => {
						category = temp.target.value;
						reload();
					});
				}
			});
		}

		function initTransactionDate(){
			$("[name='transaction_date']").flatpickr({
				altInput: true,
				altFormat: "F j, Y",
				dateFormat: "Y-m-d",
				defaultDate: moment().format("YYYY-MM-DD"),
			});
		}

		let footer = `
			<tr style="text-align: right; font-weight: bold;" class="footer">
				<td colspan="3">
					Total
				</td>
				<td id="total" class="center">
					0
				</td>
				<td></td>
			</tr>
			<tr style="text-align: right;" class="footer">
				<td colspan="5">
					<a class="btn btn-success" data-toggle="tooltip" onclick="submit()">
					    SUBMIT
					</a>
				</td>
			</tr>
		`;

		function addFooter(){
			$("#table2 tbody").append(footer);
		};

		function computeTotal(){
			let items = $('.total');
			let total = 0;

			items.each((index, item) => {
				let parent = $(item).parent().parent();
				let price = parent.find(".price")[0].innerText;
				let request_qty = parent.find(".request_qty")[0].value;

				item.value = toFloat(price * request_qty);
				total += price * request_qty;
			});
			
			$('#total').html(toFloat(total));
		}

		// ACTIONS
		// ACTIONS
		// ACTIONS
		// ACTIONS

		function add(id){
			if(medicines[id]){
				medicines[id]++;
				$(`[name="request_qty${id}"]`).val(medicines[id]);
				computeTotal();
			}
			else{
				$.ajax({
					url: "{{ route('medicine.get') }}",
					data: {
						select: "*",
						where: ["id", id],
						load: ["reorder.stocks"]
					},
					success: medicine => {
						medicine = JSON.parse(medicine)[0];
						medicines[id] = 1;

						let price = 0;

						medicine.reorder.stocks.forEach(stock => {
							price += stock.unit_price;
						});

						price = (price / medicine.reorder.stocks.length);

						$('.footer').remove();
						$("#table2 tbody").append(`
							<tr class="item" data-id="${id}">
								<td>${medicine.name}</td>
								<td>
									<input type="number" name="request_qty${id}" class="form-control request_qty" value="1" data-id=${id}>
								</td>
								<td class="price">
									${toFloat(price)}
								</td>
								<td>
									<input type="number" class="form-control total" readonly>
								</td>
								<td>
									<a class="btn btn-danger btn-sm" data-toggle="tooltip" title="Remove" onclick="remove(${id})">
									    <i class="fas fa-trash"></i>
									</a>
								</td>
							</tr>
						`);
						addFooter();
						initListener();
						computeTotal();
					}
				})
			}
		}

		function initListener(){
			$('.request_qty').unbind('change');
			$('.request_qty').on("change", request_qty => {
				request_qty = $(request_qty.target);
				medicines[request_qty.data("id")] = request_qty.val();

				if(request_qty.val() == 0){
					$(request_qty).parent().parent().remove();
					delete medicines[request_qty.data("id")];
				}
				computeTotal();
			});
		}

		function remove(id){
			$(`[name="request_qty${id}"]`).val(0);
			$(`[name="request_qty${id}"]`).trigger('change');
		}

		function submit(){
			swal.showLoading();
			let items = $('.item');

			if(items.length){
				let data = [];
				let bool = false;

				items.each((index, item) => {
					let id = $(item).data('id');

					let temp = {
						medicine_id: id,
						reference: $("[name='reference']").val(),
						requested_by: $("[name='requested_by']").val(),
						request_qty: $(item).find(".request_qty").val(),
						unit_price: $(item).find(".price")[0].innerText,
						amount: $(item).find(".total").val(),
						transaction_date: $("[name='transaction_date']").val()
					};

					Object.keys(temp).forEach(key => {
						console.log(key, temp[key]);
					    if(temp[key] == null || temp[key] == ""){
					    	bool = true;
					    	return true;
					    }
					});

					data.push(temp);
				});

				if(bool){
					se("Fill all fields");
				}
				else{
					$.ajax({
						url: "{{ route('request.store') }}",
						data: {
							data: data,
							_token: $('meta[name="csrf-token"]').attr('content')
						},
						type: "POST",
						success: () => {
							ss("Success");
							medicines = [];
							$('#table2 tbody').html("");
							$('#table2 tbody').append(footer);
							$("[name='type']").val(null).trigger('change');
							$("[name='reference']").val(null).trigger('change');
							$("[name='particulars']").val(null).trigger('change');
							$("[name='transaction_date']").val(null).trigger('change');
							reload();
						}
					});
				}
			}
			else{
				se("No item selected");
			}
		}
	</script>
@endpush