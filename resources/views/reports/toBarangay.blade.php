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
                            Transferred to Branch
                        </h3>
                    </div>

                    <div class="card-body table-responsive">
                        
                        @include('reports.toolbars.toBarangay')

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
		var from = moment().subtract(7, 'days').format(dateFormat);
		var to = dateNow();
		var rhu = "{{ auth()->user()->role == "RHU" ? auth()->user()->id : "%%" }}";
		var bhc = "%%";
		var bhcss = [];
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
				reload();
			});

			$("[name='to']").on('change', e => {
				to = e.target.value;
				reload();
			});

			getBhc();

			$.ajax({
				url: "{{ route('rhu.get') }}",
				data: {
					select: '*',
					where: ['admin_id', {{ auth()->user()->id }}]
				},
				success: rhus => {
					rhus = JSON.parse(rhus);
					
					rhuString = "<option value='%%'>All</option>";
					rhus.forEach(rhu => {
						rhuString += `
							<option value="${rhu.user_id}">${rhu.company_name}</option>
						`;
					});

					$('#rhu').append(rhuString);
					$('#rhu').select2();
					$('#rhu').change(e => {
						rhu = e.target.value;
						bhc = "%%";
						getBhc();
						reload();
					});
				}
			});
		});

		function getBhc(){
			$('#bhc').html('');
			$.ajax({
				url: "{{ route('bhc.get') }}",
				data: {
					select: 'bhcs.*',
					@if(auth()->user()->role == "RHU")
						join: true,
						where: ['r.user_id', {{ auth()->user()->id }}]
					@else
						join: true,
						where: rhu == "%%" ? ['r.admin_id', {{ auth()->user()->id }}] : ['r.user_id', rhu]
					@endif
				},
				success: bhcs => {
					bhcs = JSON.parse(bhcs);
					bhcss = [];

					bhcString = "<option value='%%'>All</option>";
					bhcs.forEach(bhc2 => {
						if(bhc == "%%"){
							bhcss.push(bhc2);
							bhcString += `
								<option value="${bhc2.id}">${bhc2.name}</option>
							`;
						}
						else{
							if(bhc2.id == bhc){
								bhcss.push(bhc2.name);
								bhcString += `
									<option value="${bhc2.id}">${bhc2.name}</option>
								`;
							}
						}
					});
					$('#bhc').append(bhcString);
					$('#bhc').select2();
					$('#bhc').change(e => {
						bhc = e.target.value;
						getColumns();
						reload();
						filter();
					});

					// CREATE TABLE
					getColumns();
					if(table != null){
						filter();
					}
					if(table == null){
						createTable();
					}
				}
			});
		}

		function createTable(){
			table = $('#table').DataTable({
				ajax: {
					url: "{{ route('report.getToBarangay') }}",
                	dataType: "json",
                	dataSrc: "",
					data: f => {
						f.from = from;
						f.to = to;
						f.rhu = rhu;
						f.bhc = bhc;
					}
				},
				columns: columns,
        		scrollX: true,
        		pageLength: 25,
        		order: [0, 'desc']
			});
			
			$('#table_filter').remove();
			$('#table').css('width', '100%');
		}

		function getColumns(){
			columns = [];

			columns.push({
				data: 'medicine',
				title: 'Medicine'
			});

			columns.push({
				data: 'packaging',
				title: 'Packaging'
			});

			bhcss.forEach(bhc2 => {
				if(bhc == "%%"){
					columns.push({
						data: bhc2.name,
						title: bhc2.name,
						defaultContent: 0
					});
				}
				else{
					if(bhc2.id == bhc){
						columns.push({
							data: bhc2.name,
							title: bhc2.name
						});
					}
				}
			});

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

		// function exportReport(){
		// 	let data = {
		// 		outlet: outlet,
		// 		tType: tType,
		// 		from: from,
		// 		to: to,
		// 		view: view
		// 	};

		// 	window.open("/export/exportInventory?" + $.param(data), "_blank");
		// }
	</script>
@endpush