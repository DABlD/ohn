@extends('layouts.app')
@section('content')

<section class="content">
    <div class="container-fluid">

        <div class="row">
            <section class="col-lg-12 connectedSortable">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table mr-1"></i>
                            Inventory Report
                        </h3>
                    </div>

                    <div class="card-body table-responsive">
                        
                        @include('reports.toolbars.inventory')

                        <br>
                    	<table id="table" class="table table-hover">
                    		<thead>
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
	{{-- <link rel="stylesheet" href="{{ asset('css/datatables-jquery.min.css') }}"> --}}
@endpush

@push('scripts')
	<script src="{{ asset('js/datatables.min.js') }}"></script>
	<script src="{{ asset('js/select2.min.js') }}"></script>
	{{-- <script src="{{ asset('js/datatables-jquery.min.js') }}"></script> --}}

	<script>
		var columns = [];
		var outlet = "%%";
		var tType = 1;
		var from = moment().subtract(7, 'days').format(dateFormat);
		var to = dateNow();
		var view = "qty";
		var table = null;

		$(document).ready(()=> {
			$('#from').append(input('from', 'From', from, 4, 8));
			$('#to').append(input('to', 'To', to, 4, 8));

			let settings = {
				altInput: true,
				altFormat: "F j, Y",
				dateFormat: "Y-m-d",
			};

			$("[name='from']").flatpickr(settings);
			$("[name='to']").flatpickr(settings);

			$("[name='from']").on('change', e => {
				from = e.target.value;
			});

			$("[name='to']").on('change', e => {
				to = e.target.value;
			});

			$("#view").on('change', e => {
				view = e.target.value;
			});


			$.ajax({
				url: "{{ route('transactionType.get') }}",
				data: {
					select: '*',
					@if(auth()->user()->role == "Admin")
						where: ['admin_id', {{ auth()->user()->id }}]
					@else(auth()->user()->role == "RHU")
						join: true,
						select: ['*', 'transaction_types.id as tid'],
						where: ['r.user_id', {{ auth()->user()->id }}]
					@endif
				},
				success: types => {
					types = JSON.parse(types);
					typeString = "";
					types.forEach((type, key) => {
						if(key == 0){
							tType = type.tid;
						}
						typeString += `
							<option value="${type.tid}">${type.type}</option>
						`;
					});

					$('#trx').append(typeString);
					$('#trx').select2();
					$('#trx').change(e => {
						tType = e.target.value;
					});

					// CREATE TABLE
					createTable();
				}
			});

			$.ajax({
				url: "{{ route('bhc.get') }}",
				data: {
					select: 'bhcs.*',
					@if(auth()->user()->role == "RHU")
						join: true,
						where: ['r.user_id', {{ auth()->user()->id }}]
					@else
						join: true,
						where: ['r.admin_id', {{ auth()->user()->id }}]
					@endif
				},
				success: bhcs => {
					bhcs = JSON.parse(bhcs);
					
					bhcString = "<option value='%%'>All</option>";
					bhcs.forEach(bhc => {
						bhcString += `
							<option value="${bhc.id}">${bhc.name}</option>
						`;
					});

					$('#outlet').append(bhcString);
					$('#outlet').select2();
					$('#outlet').change(e => {
						outlet = e.target.value;
					});
				}
			});
		});

		function createTable(){
			getColumns();
			table = $('#table').DataTable({
				ajax: {
					url: "{{ route('report.getInventory') }}",
                	dataType: "json",
                	dataSrc: "",
					data: f => {
						f.outlet = outlet;
						f.tType = tType;
						f.from = from;
						f.to = to;
						f.view = view;
					}
				},
        		scrollX: true,
				columns: columns,
        		pageLength: 25,
        		order: []
			});
			
			$('#table_filter').remove();
			$('#table').css('width', '100%');
		}

		function getColumns(){
			columns = [];
			columns.push({
				data: 'item',
				title: 'Item'
			});

			let temp = from;
			while(temp <= to){
				let temp2 = moment(temp).format("MMM DD");
				let temp3 = moment(temp).format("MMM DD (ddd)");

				columns.push({
					data: temp2,
					title: temp3
				});

				temp = moment(temp).add('1', 'day').format(dateFormat);
			}

			columns.push({
				data: 'total',
				title: 'Total'
			});
		}

		function filter(){
			$('#table').DataTable().clear().destroy();
			$('#table thead').html('');
			createTable();
		}

		function exportReport(){
			let data = {
				outlet: outlet,
				tType: tType,
				from: from,
				to: to,
				view: view
			};

			window.open("/export/exportInventory?" + $.param(data), "_blank");
		}
	</script>
@endpush