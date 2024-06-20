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
                            List
                        </h3>

                        @include('rx.includes.toolbar')
                    </div>

                    <div class="card-body table-responsive">
                    	<table id="table" class="table table-hover">
                    		<thead>
                    			<tr>
                    				<th>Ticket #</th>
                    				<th>Doctor</th>
                    				<th>Patient ID</th>
                    				<th>Name</th>
                    				<th>Contact</th>
                    				<th>Address</th>
                    				<th>Amount</th>
                    				<th>Date</th>
                    				<th>Actions</th>
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
	{{-- <link rel="stylesheet" href="{{ asset('css/datatables-jquery.min.css') }}"> --}}
@endpush

@push('scripts')
	<script src="{{ asset('js/datatables.min.js') }}"></script>
	{{-- <script src="{{ asset('js/datatables-jquery.min.js') }}"></script> --}}

	<script>
		$(document).ready(()=> {
			var table = $('#table').DataTable({
				ajax: {
					url: "{{ route('datatable.rx') }}",
                	dataType: "json",
                	dataSrc: "",
					data: {
						table: 'rxes',
						select: "*",
					}
				},
				columns: [
					{data: 'ticket_number'},
					{data: 'doctor_id'},
					{data: 'patient_id'},
					{data: 'patient_name'},
					{data: 'contact'},
					{data: 'address'},
					{data: 'amount'},
					{data: 'date'},
					{data: 'actions'}
				],
				columnDefs: [
					{
						targets: 6,
						render: amount =>{
							return toFloat(amount);
						}
					},
					{
						targets: 7,
						render: date =>{
							return moment(date).format(dateFormat2);
						}
					}
				],
        		pageLength: 25,
				// drawCallback: function(){
				// 	init();
				// }
			});
		});

		function prescriptions(id){
			$.ajax({
				url: '{{ route('rx.get') }}',
				data: {
					where: ['id', id],
					select: 'prescription',
				},
				success: result => {
					result = JSON.parse(result)[0];

					let prescription = result.prescription;
					if(prescription){
						console.log(prescription);
						prescription = JSON.parse(prescription);
					}
					else{
						se('No Prescription Data');
					}
				}
			})
		}
	</script>
@endpush