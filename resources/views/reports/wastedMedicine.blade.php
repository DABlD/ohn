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
                            Transferred to Franchise
                        </h3>
                    </div>

                    <div class="card-body table-responsive">
                        
                        @include('reports.toolbars.toRhu')

                        <br>
                    	<table id="table" class="table table-hover">
                    		<thead>
                    			<tr>
                    				<th>User</th>
                    				<th>Medicine</th>
                    				<th>Category</th>
                    				<th>Code</th>
                    				<th>Brand</th>
                    				<th>Packaging</th>
                    				<th>Qty</th>
                    				<th>Date Wasted</th>
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
	{{-- <link rel="stylesheet" href="{{ asset('css/datatables-jquery.min.css') }}"> --}}
@endpush

@push('scripts')
	<script src="{{ asset('js/datatables.min.js') }}"></script>
	<script src="{{ asset('js/select2.min.js') }}"></script>
	{{-- <script src="{{ asset('js/datatables-jquery.min.js') }}"></script> --}}

	<script>
		var from = moment().subtract(7, 'days').format(dateFormat);
		var to = dateNow();
		var rhu = "%%";
		var sku = "%%";
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

			$.ajax({
				url: "{{ route('medicine.get') }}",
				data: {
					select: '*',
					where: ['user_id', {{ auth()->user()->id }}]
				},
				success: medicines => {
					medicines = JSON.parse(medicines);

					medicineString = "<option value='%%'>All</option>";
					medicines.forEach(medicine => {
						medicineString += `
							<option value="${medicine.id}">${medicine.name}</option>
						`;
					});

					$('#sku').append(medicineString);
					$('#sku').select2();
					$('#sku').change(e => {
						sku = e.target.value;
						reload();
					});
				}
			});

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
						reload();
					});
				}
			});

			// CREATE TABLE
			createTable();
		});

		function createTable(){
			table = $('#table').DataTable({
				ajax: {
					url: "{{ route('report.getWastedMedicine') }}",
                	dataType: "json",
                	dataSrc: "",
					data: f => {
						f.from = from;
						f.to = to;
						f.rhu = rhu;
						f.sku = sku;
					}
				},
				columns: [
					{data: 'rname'},
					{data: 'mname'},
					{data: 'mcode'},
					{data: 'mbrand'},
					{data: 'mpack'},
					{data: 'lot_number'},
					{data: 'qty'},
					{data: 'expiry_date'},
				],
				columnDefs: [
					{
						targets: 6,
						render: date =>{;
							return moment(date).format('MMM DD, YYYY');
						}
					},
				],
        		scrollX: true,
        		pageLength: 25,
        		order: []
			});
			
			$('#table_filter').remove();
			$('#table').css('width', '100%');
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