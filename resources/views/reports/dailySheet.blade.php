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
                            Daily Sheet
                        </h3>
                    </div>

                    <div class="card-body table-responsive">
                        
                        @include('reports.toolbars.dailySheet')

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

	<style>
		table tbody td{
			text-align: center;
		}
	</style>
@endpush

@push('scripts')
	<script src="{{ asset('js/datatables.min.js') }}"></script>
	<script src="{{ asset('js/select2.min.js') }}"></script>
	{{-- <script src="{{ asset('js/datatables-jquery.min.js') }}"></script> --}}

	<script>
		var columns = [];
		var from = moment().subtract(10, 'days').format(dateFormat);
		var to = dateNow();

		var bhc_id = "%%";
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

			$.ajax({
				url: "{{ route('bhc.get') }}",
				data: {
					select: '*',
				},
				success: bhcs => {
					bhcs = JSON.parse(bhcs);
					
					bhcString = "<option value='%%'>All</option>";
					bhcs.forEach(bhc => {
						bhcString += `
							<option value="${bhc.id}">${bhc.name}</option>
						`;
					});

					$('#bhc_id').append(bhcString);
					$('#bhc_id').select2();
					$('#bhc_id').change(e => {
						outlet = e.target.value;
					});
				}
			});

			$("[name='from']").on('change', e => {
				from = e.target.value;
			});

			$("[name='to']").on('change', e => {
				to = e.target.value;
			});

			$("#view").on('change', e => {
				view = e.target.value;
			});

			$("#bhc_id").on('change', e => {
				bhc_id = e.target.value;
			});

			// CREATE TABLE
			createTable();
		});

		function createTable(){
			getColumns();
		}

		function getColumns(){
			columns = [];
			columns.push({
				data: 'item',
				title: 'Item'
			});

			$.ajax({
				url: "{{ route('transactionType.get') }}",
				data: {
					select: ["id", "type", "operator"],
					whereNotNull: 'operator'
				},
				success: types => {
					types = JSON.parse(types);

					types.forEach(type => {
						columns.push({
							data: type.type.replace('.', ''),
							title: type.type + ` (${type.operator})`
						});
					});

					columns.push({
						data: "Ending " + view,
						title: "Ending " + view
					});

					table = $('#table').DataTable({
						ajax: {
							url: "{{ route('report.getDailySheet') }}",
		                	dataType: "json",
		                	dataSrc: "",
							data: f => {
								f.bhc_id = bhc_id;
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
			})
		}

		function filter(){
			$('#table').DataTable().clear().destroy();
			$('#table thead').html('');
			createTable();
		}

		function exportReport(){
			let data = {
				bhc_id: bhc_id,
				from: from,
				to: to,
				view: view
			};

			window.open("/export/exportDailySheet?" + $.param(data), "_blank");
		}
	</script>
@endpush