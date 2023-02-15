@extends('layouts.app')
@section('content')

<section class="content">
    <div class="container-fluid">

        <div class="row">
            <section class="col-lg-12">
        </div>

        <div class="row">
        	<section class="col-lg-1 connectedSortable"></section>
            <section class="col-lg-10 connectedSortable">
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
                    			    <div class="col-md-4 iLabel">
                    			    	Reference No
                    			    </div>
            			            <div class="col-md-8 iInput">
            			                {{ $data[0]->reference }}
            			            </div>
                    			</div>

                    		</div>

                    		<div class="col-md-5">
                    			
                    			<div class="row">
                    			    <div class="col-md-4 iLabel">
                    			        Date
                    			    </div>
            			            <div class="col-md-8 iInput">
            			                <input type="text" name="date_dispatched" placeholder="Transaction Date" class="form-control">
            			            </div>
                    			</div>

                    		</div>
                    	</div>

                    	<br>
                    	<div class="row">
                    		<div class="col-md-7">
                    			
                    			<div class="row">
                    			    <div class="col-md-4 iLabel">
                    			        Particulars
                    			    </div>
            			            <div class="col-md-8 iInput">
            			                {{ $data[0]->requested_by }}
            			            </div>
                    			</div>

                    		</div>

                    		<div class="col-md-5"></div>
                    	</div>

                    	<br>
                    	<div class="row">
                    		<div class="col-md-12">
                    			<table id="table" class="table table-hover">
                    				<thead>
                    					<tr>
                    						<th>ID</th>
                    						<th>Item</th>
                    						<th>Lot Number</th>
                    						<th>Expiry Date</th>
                    						<th>Qty</th>
                    						<th>Unit Price</th>
                    						<th>Amount</th>
                    					</tr>
                    				</thead>

                    				<tbody>

                    					@php
                    						$total = 0;
                    					@endphp
                    					@foreach($data as $request)
                    						<tr id="{{ $request->id }}" class="request">
                    							<td class="center">{{ $request->id }}</td>
                    							<td class="center">{{ $request->medicine->name }}</td>
                    							<td class="center lot" data-id="{{ $request->id }}"></td>
                    							<td class="center expiry_date" data-id="{{ $request->id }}"></td>
                    							<td class="center">{{ $request->approved_qty }}</td>
                    							<td class="center unit_price" data-id="{{ $request->id }}"></td>
                    							<td class="center">{{ $request->amount }}</td>
                    						</tr>
	                    					@php
	                    						$total += $request->amount;
	                    					@endphp
                    					@endforeach

                    				</tbody>
                    			</table>
                    		</div>
                    	</div>

                    </div>
                </div>

            </section>
            <section class="col-lg-1 connectedSortable"></section>
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
		#table tbody td{
			vertical-align: middle;
		}
		#table thead th{
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
		var category = 0;
		var medicines = [];

		$(document).ready(()=> {

			var table = $('#table').DataTable({
        		pageLength: 20,
        		lengthChange: false,
        		paging: false,
        		info: false,
        		language: {
        			search: "",
        			emptyTable: "No Selected Category"
        		},
				drawCallback: () => {
					$('[type="search"]').remove();
				}
			});

			setTimeout(() => {
				init();
			}, 500);
			computeTotal();
		});

		function init(){
			$("[name='date_dispatched']").flatpickr({
				altInput: true,
				altFormat: "F j, Y",
				dateFormat: "Y-m-d",
				defaultDate: moment().format("YYYY-MM-DD"),
			});

			$('#table tbody').append(`
				<tr style="text-align: right; font-weight: bold;">
					<td></td><td></td><td></td><td></td><td></td>
					<td>
						Total
					</td>
					<td id="total" class="center">
						{{ $total }}
					</td>
				</tr>
				<tr style="text-align: right;">
					<td></td><td></td><td></td><td></td><td></td><td></td>
					<td>
						<a class="btn btn-success" data-toggle="tooltip" onclick="submit()">
						    SUBMIT
						</a>
					</td>
				</tr>
			`);

			// $('.lot').each((index, lot) => {
			// 	let id = $(lot).data('id');
			// 	let inp = input("lot", "", null, 0, 12, 'text', ` data-id="${id}"`);
			// 	$(lot).append(inp);
			// });

			$('.expiry_date').each((i, expiry_date) => {
				let id = expiry_date.dataset.id;
				$.ajax({
					url: "{{ route('request.get') }}",
					data: {
						select: 'medicine_id',
						where: ['id', id]
					},
					success: request => {
						request = JSON.parse(request)[0];
						
						$.ajax({
							url: '{{ route('stock.get') }}',
							data: {
								select: 'stocks.*',
								join: true,
								where: ['r.user_id', {{ auth()->user()->id }}],
								where2: ['r.medicine_id', request.medicine_id],
								order: ['expiry_date', 'desc']

							},
							success: stocks => {
								stocks = JSON.parse(stocks);
								
								$(expiry_date).append(`<select data-id='${id}' class='form-control'></select>`);

								stockString = '<option value="" data-expiry_date="" data-unit_price=""></option>';
								stocks.forEach(stock => {
									stockString += `
										<option value="${stock.expiry_date}" data-lot_number="${stock.lot_number}" data-unit_price="${stock.unit_price}">${moment(stock.expiry_date).format(dateFormat2)} (#${stock.qty})</option>
									`;
								});

								$(expiry_date).find('select').append(stockString);
								$(expiry_date).find('select').select2({
									placeholder: 'Select Expiry Date'
								});

								$(expiry_date).find('select').on('change', e => {
									let lot_number = $(expiry_date).find(":selected").data("lot_number");
									let unit_price = $(expiry_date).find(":selected").data("unit_price");

									$(expiry_date).parent().find('.lot').text(lot_number);
									$(expiry_date).parent().find('.unit_price').text(toFloat(unit_price));
								});
							}
						})
					}
				})
			});

			// $('.exp').each((index, exp) => {
			// 	let id = $(exp).data('id');
			// 	let inp = input("exp", "", null, 0, 12, 'text', ` data-id="${id}"`);
			// 	$(exp).append(inp);
			// });



			$('[name="exp"]').flatpickr({
				dateFormat: "Y-m-d"
			});
		}

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

		function submit(){
			let toSave = [];

			$('.request').each((index, request) => {
				request = $(request);
				let temp = {};

				let lot = request.find('.lot').text();
				let exp = request.find('select').val();
				let unit_price = request.find('.unit_price').text();
				let id = request.attr('id');

				if(lot == "" || exp == ""){
					se('Fill all fields!');
				}
				else{
					temp.lot = lot;
					temp.exp = moment(exp).format(dateFormat);
					temp.id = id;
					temp.unit_price = unit_price;

					toSave.push(temp);
				}
			});

			let date_dispatched = $("[name='date_dispatched']").val();
			swal.showLoading();
			toSave.forEach((request, index) => {
				update({
					url: "{{ route('request.update') }}",
					data: {
						id: request.id,
						lot_number: request.lot,
						expiry_date: request.exp,
						unit_price: request.unit_price,
						date_dispatched: date_dispatched,
						status: 'For Delivery'
					},
				}, () => {
					if(index+1 == toSave.length){
						ss("Success");
						setTimeout(() => {
							window.location.href = "{{ route('request.request') }}";
						}, 1000);
					}
				});
			});
		}
	</script>
@endpush