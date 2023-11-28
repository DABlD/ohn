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
                            Sales Report
                        </h3>
                    </div>

                    <div class="card-body table-responsive">
                        
                        @include('reports.toolbars.sales')

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
		var from = moment().subtract(10, 'days').format(dateFormat);
		var to = dateNow();

		var fby = "bhc_id";
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


			$("#fby").on('change', e => {
				fby = e.target.value;
			});

			// CREATE TABLE
			createTable();
		});

		function createTable(){
			getColumns();
			let temp = moment(to).diff(moment(from), 'days') + 3;
			let cols = [];

			for(i = 1; i < temp; i++){
				cols.push(i);
			}

			table = $('#table').DataTable({
				ajax: {
					url: "{{ route('report.getSales') }}",
                	dataType: "json",
                	dataSrc: "",
					data: f => {
						f.fby = fby;
						f.from = from;
						f.to = to;
						f.view = view;
					}
				},
        		scrollX: true,
				columns: columns,
        		pageLength: 25,
				columnDefs: [
					{
						targets: cols,
						render: value => {
							if(value != 0 && view == "amount"){
								return value.toLocaleString('en-US');
							}
							else{
								return value;
							}
						}
					}
				],
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
				fby: fby,
				from: from,
				to: to,
				view: view
			};

			window.open("/export/exportSales?" + $.param(data), "_blank");
		}
	</script>
@endpush