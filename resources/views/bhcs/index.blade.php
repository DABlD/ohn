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

                        @if(auth()->user()->role == "Admin")
                        	@include('bhcs.includes.toolbar')
                        @endif
                    </div>

                    <div class="card-body table-responsive">
                    	<table id="table" class="table table-hover">
                    		<thead>
                    			<tr>
                    				<th>ID</th>
                    				<th>Franchise</th>
                    				<th>Code</th>
                    				<th>Name</th>
                    				<th>Region</th>
                    				<th>Municipality</th>
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
	<link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
@endpush

@push('scripts')
	<script src="{{ asset('js/datatables.min.js') }}"></script>
	<script src="{{ asset('js/select2.min.js') }}"></script>

	<script>
		$(document).ready(()=> {
			var table = $('#table').DataTable({
				ajax: {
					url: "{{ route('datatable.bhc') }}",
                	dataType: "json",
                	dataSrc: "",
					data: {
						select: ['bhcs.*', 'r.id as rid'],
						load: ['rhu'],
						join: true,
					}
				},
				columns: [
					{data: 'id'},
					{data: 'rhu.company_name', visible: false},
					{data: 'code'},
					{data: 'name'},
					{data: 'region'},
					{data: 'municipality'},
					{data: 'actions'},
				],
        		order: [[1, 'asc']],
        		pageLength: 25,
        		rowCallback: function( row, data, index ) {
				    if (data['id'] == null) {
				        $(row).hide();
				    }
				},
		        drawCallback: function (settings) {
		            let api = this.api();
		            let rows = api.rows({ page: 'current' }).nodes();
		            let last = null;
		 
		            api.column(1, { page: 'current' })
		                .data()
		                .each(function (company, i, row) {
		                    if (last !== company) {
		                        $(rows)
		                            .eq(i)
		                            .before(`
		                            	<tr class="group">
		                            		<td colspan="5">
		                            			${company}
		                            		</td>
		                            	</tr>
		                            `);
		 
		                        last = company;
		                    }
		                });

		            
		        	let groups = $('tr.group td');

		        	if(groups.length){
		        		groups.each((index, row) => {
		        			let rhu = row.innerText;
		        			$(row).after(`
		        				<td>
                        			@if(auth()->user()->role == "Rhu")
		        					<a class='btn btn-primary btn-sm' data-toggle='tooltip' title='Add Branch' onclick='create("${rhu}")'>
		        					    <i class='fas fa-plus fa-2xl'></i>
		        					</a>
		        					@endif
		        				</td>
		        			`);
		        		});
		        	}

		        	let grps = $('[class="group"]');
		        	grps.each((index, group) => {
		        		if(!$(group).next().is(':visible')){
		        			$(group).remove();
		        		}
		        	});
		        },
			});
		});

		function create(selectedRhu = null){
			Swal.fire({
				html: `
	                ${input("code", "Code", null, 3, 9)}
	                ${input("name", "Name", null, 3, 9)}
	                ${input("region", "Region", null, 3, 9)}
	                ${input("municipality", "Municipality", null, 3, 9)}
				`,
				width: '800px',
				confirmButtonText: 'Add',
				showCancelButton: true,
				cancelButtonColor: errorColor,
				cancelButtonText: 'Cancel',
				preConfirm: () => {
				    swal.showLoading();
				    return new Promise(resolve => {
				    	let bool = true;

			            if($('.swal2-container input:placeholder-shown').length){
			                Swal.showValidationMessage('Fill all fields');
			            }
			            else{
			            	let bool = false;
			            	// Insert ajax validation
				            setTimeout(() => {resolve()}, 500);
			            }

			            bool ? setTimeout(() => {resolve()}, 500) : "";
				    });
				},
			}).then(result => {
				if(result.value){
					swal.showLoading();
					$.ajax({
						url: "{{ route('bhc.store') }}",
						type: "POST",
						data: {
							code: $("[name='code']").val(),
							name: $("[name='name']").val(),
							region: $("[name='region']").val(),
							municipality: $("[name='municipality']").val(),
							_token: $('meta[name="csrf-token"]').attr('content')
						},
						success: () => {
							ss("Success");
							reload();
						}
					})
				}
			});
		}

		function create2(){
			Swal.fire({
				html: `
	                ${input("code", "Code", null, 3, 9)}
	                ${input("name", "Name", null, 3, 9)}
	                ${input("region", "Region", null, 3, 9)}
	                ${input("municipality", "Municipality", null, 3, 9)}
				`,
				width: '800px',
				confirmButtonText: 'Add',
				showCancelButton: true,
				cancelButtonColor: errorColor,
				cancelButtonText: 'Cancel',
				preConfirm: () => {
				    swal.showLoading();
				    return new Promise(resolve => {
				    	let bool = true;

			            if($('.swal2-container input:placeholder-shown').length){
			                Swal.showValidationMessage('Fill all fields');
			            }
			            else{
			            	let bool = false;
			            	// Insert ajax validation
				            setTimeout(() => {resolve()}, 500);
			            }

			            bool ? setTimeout(() => {resolve()}, 500) : "";
				    });
				},
			}).then(result => {
				if(result.value){
					swal.showLoading();
					$.ajax({
						url: "{{ route('bhc.store') }}",
						type: "POST",
						data: {
							code: $("[name='code']").val(),
							name: $("[name='name']").val(),
							region: $("[name='region']").val(),
							municipality: $("[name='municipality']").val(),
							_token: $('meta[name="csrf-token"]').attr('content')
						},
						success: () => {
							ss("Success");
							reload();
						}
					})
				}
			});
		}

		function view(id){
			$.ajax({
				url: "{{ route('bhc.get') }}",
				data: {
					select: '*',
					where: ['id', id],
				},
				success: bhc => {
					bhc = JSON.parse(bhc)[0];
					showDetails(bhc);
				}
			})
		}

		function showDetails(bhc){
			Swal.fire({
				html: `
	                ${input("id", "", bhc.id, 3, 9, 'hidden')}
	                ${input("code", "Code", bhc.code, 3, 9)}
	                ${input("name", "Name", bhc.name, 3, 9)}
	                ${input("region", "Region", bhc.region, 3, 9)}
	                ${input("municipality", "Municipality", bhc.municipality, 3, 9)}
				`,
				width: '800px',
				confirmButtonText: 'Update',
				showCancelButton: true,
				cancelButtonColor: errorColor,
				cancelButtonText: 'Cancel',
				preConfirm: () => {
				    swal.showLoading();
				    return new Promise(resolve => {
				    	let bool = true;

			            if($('.swal2-container input:placeholder-shown').length){
			                Swal.showValidationMessage('Fill all fields');
			            }
			            else{
			            	let bool = false;
			            	// Insert ajax validation
				            setTimeout(() => {resolve()}, 500);
			            }

			            bool ? setTimeout(() => {resolve()}, 500) : "";
				    });
				},
			}).then(result => {
				if(result.value){
					swal.showLoading();
					update({
						url: "{{ route('bhc.update') }}",
						data: {
							id: $("[name='id']").val(),
							code: $("[name='code']").val(),
							name: $("[name='name']").val(),
							region: $("[name='region']").val(),
							municipality: $("[name='municipality']").val(),
						},
						message: "Success"
					}, () => {
						reload();
					})
				}
			});
		}

		function del(id){
			sc("Confirmation", "Are you sure you want to delete?", result => {
				if(result.value){
					swal.showLoading();
					update({
						url: "{{ route('bhc.delete') }}",
						data: {id: id},
						message: "Success"
					}, () => {
						reload();
					})
				}
			});
		}
	</script>
@endpush