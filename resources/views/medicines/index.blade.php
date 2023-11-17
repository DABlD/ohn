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
                        	@include('medicines.includes.toolbar')
                        @endif
                    </div>

                    <div class="card-body table-responsive">
                    	<table id="table" class="table table-hover">
                    		<thead>
                    			<tr>
                    				<th>#</th>
                    				<th>Category</th>
                    				<th>Image</th>
                    				<th>Code</th>
                    				<th>Brand</th>
                    				<th>Name</th>
                    				<th>Packaging</th>
                    				<th>Reorder Point</th>
                    				<th>Stock</th>
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
		var user_id = {{ auth()->user()->id }};

		$(document).ready(()=> {
			var table = $('#table').DataTable({
				ajax: {
					url: "{{ route('datatable.medicine') }}",
                	dataType: "json",
                	dataSrc:'',
					data: f => {
						f.select = ["medicines.*", "r.stock as rs", "r.point"];
						f.load = ['category', 'reorder'];
						f.where = ['r.user_id', user_id];
					}
				},
				columns: [
					{data: 'id'},
					{data: 'category.name', visible: false},
					{data: 'image'},
					{data: 'code'},
					{data: 'brand'},
					{data: 'name'},
					{data: 'packaging'},
					{data: 'reorder.point'},
					{data: 'rs'},
					{data: 'actions'},
				],
        		order: [[1, 'asc']],
        		pageLength: 25,
        		rowCallback: function( row, data, index ) {
				    if (data['id'] == null) {
				        $(row).hide();
				    }
				},
				columnDefs: [
					{
						targets: 2,
						render: img =>{;
							if(img){
								return `
									<img src="{{ asset("/") }}${img}" alt="Product Image" width="115px" height="60px">
								`;
							}
							else{
								return img;
							}
						}
					}
				],
		        drawCallback: function (settings) {
		            let api = this.api();
		            let rows = api.rows({ page: 'current' }).nodes();
		            let last = null;
		 
		            api.column(1, { page: 'current' })
		                .data()
		                .each(function (medicine, i) {
		                    if (last !== medicine) {
		                        $(rows)
		                            .eq(i)
		                            .before(`
		                            	<tr class="group">
		                            		<td colspan="8">
		                            			${medicine}
		                            		</td>
		                            	</tr>
		                            `);
		 
		                        last = medicine;
		                    }
		                });


					let groups = $('tr.group td');

					if(groups.length){
						groups.each((index, row) => {
							let category = row.innerText;
							$(row).after(`
								<td>
									@if(auth()->user()->role == "Admin")
									<a class='btn btn-primary btn-sm' data-toggle='tooltip' title='Add Item' onclick='create("${category}")'>
										<i class='fas fa-plus fa-2xl'></i>
									</a>
									<a class='btn btn-warning btn-sm' data-toggle='tooltip' title='Edit Category' onclick='editCategory("${category}")'>
										<i class='fas fa-pencil'></i>
									</a>
									@endif
								</td>
							`);
						});
					}
				}
			});

			$.ajax({
				url: '{{ route('rhu.get') }}',
				data: {
					select: ['user_id', 'company_name'],
					where: ['admin_id', {{ auth()->user()->id }}]
				},
				success: rhus => {
					rhus = JSON.parse(rhus);

					rhuString = "";
					rhus.forEach(rhu => {
						rhuString += `
							<option value="${rhu.user_id}">${rhu.company_name}</option>
						`;
					});

					$('#user_id').append(rhuString);
					$('#user_id').select2();

					$('#user_id').on('change', e => {
						user_id = e.target.value;
						reload();
					});
				}
			})
		});

		function create(selectedCategory = null){
			Swal.fire({
				html: `
					<div class="row iRow">
					    <div class="col-md-3 iLabel">
							Image
						</div>
					    <div class="col-md-9 iLabel center">
					    	<img src="{{ asset('images/default_medicine_avatar.png') }}" alt="Default Image" width="200px;" height="120px;" id="preview">
					    	<br>
					    	<input type="file" id="file" class="d-none" placeholder="Upload Image" accept="image/*" name="image">
					    	<br>
							<label for="file">Upload Photo</label>
							<br>
					    	<br>
					    </div>
					</div>

					<div class="row iRow">
					    <div class="col-md-3 iLabel">
					        Category
					    </div>
					    <div class="col-md-9 iInput">
					        <select name="category_id" class="form-control">
					        	<option value=""></option>
					        </select>
					    </div>
					</div>
	                ${input("code", "Code", null, 3, 9)}
	                ${input("brand", "Brand", null, 3, 9)}
	                ${input("name", "Generic Name", null, 3, 9)}
	                ${input("packaging", "Packaging", null, 3, 9)}
	                ${input("reorder_point", "Reorder Point", null, 3, 9)}
				`,
				width: '800px',
				confirmButtonText: 'Add',
				showCancelButton: true,
				cancelButtonColor: errorColor,
				cancelButtonText: 'Cancel',
				didOpen: () => {
					$.ajax({
						url: "{{ route('medicine.getCategories') }}",
						data: {
							select: "*",
							@if(auth()->user()->role == "Admin")
								where: ['admin_id', {{ auth()->user()->id }}]
							@else(auth()->user()->role == "RHU")
								join: true,
								where: ['r.user_id', {{ auth()->user()->id }}]
							@endif
						},
						success: categories => {
							categories = JSON.parse(categories);
							categoryString = "";

							categories.forEach(category => {
								categoryString += `
									<option value="${category.id}">${category.name}</option>
								`;
							});

							$("[name='category_id']").append(categoryString);
							$("[name='category_id']").select2({
								placeholder: "Select category"
							});

							if(selectedCategory){
								$("[name='category_id']").select2("val", $(`[name='category_id'] option:contains('${selectedCategory}')`).val());
							}
						}
					})

					$('[name="image"]').on('change', e => {
					    var reader = new FileReader();
					    reader.onload = function (e) {
					        $('#preview').attr('src', e.target.result);
					    }

					    reader.readAsDataURL(e.target.files[0]);
					});
				},
				preConfirm: () => {
				    swal.showLoading();
				    return new Promise(resolve => {
				    	let bool = true;

			            if($('.swal2-container input:placeholder-shown').length || $("[name='rhu_id']").val() == ""){
			                Swal.showValidationMessage('Fill all fields');
			            }
			            else if($('[name="reorder_point"]').val() < 0){
			                Swal.showValidationMessage('Cost Price must not be less than 0');
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
					uploadSKU({
						image: $("[name='image']").prop('files')[0],
						category_id: $("[name='category_id']").val(),
						code: $("[name='code']").val(),
						brand: $("[name='brand']").val(),
						name: $("[name='name']").val(),
						packaging: $("[name='packaging']").val(),
						reorder_point: $("[name='reorder_point']").val(),
						_token: $('meta[name="csrf-token"]').attr('content')
					});
				}
			});
		}

		async function uploadSKU(data) {
		    let formData = new FormData();
		    formData.append('image', data.image);
		    formData.append('category_id', data.category_id);
		    formData.append('code', data.code);
		    formData.append('brand', data.brand);
		    formData.append('name', data.name);
		    formData.append('packaging', data.packaging);
		    formData.append('reorder_point', data.reorder_point);
		    formData.append('_token', data._token);

		    await fetch('{{ route('medicine.store') }}', {
				method: "POST", 
				body: formData
		    });

		    ss('Success');
		    reload();
		}

		function createCategory(){
			Swal.fire({
				html: `
	                ${input("name", "Name", null, 3, 9)}
				`,
				width: '400px',
				confirmButtonText: 'Add',
				showCancelButton: true,
				cancelButtonColor: errorColor,
				cancelButtonText: 'Cancel',
				preConfirm: () => {
				    swal.showLoading();
				    return new Promise(resolve => {
				    	let bool = true;
			            if($('.swal2-container input:placeholder-shown').length || $("[name='rhu_id']").val() == ""){
			                Swal.showValidationMessage('Fill all fields');
			            }
			            else{
			            	let bool = false;
			            	// Insert ajax validation
            				$.ajax({
            					url: "{{ route('medicine.getCategories') }}",
            					data: {
            						select: "id",
            						where: ["name", $("[name='name']").val()]
            					},
            					success: result => {
            						result = JSON.parse(result);
            						if(result.length && result[0].admin_id == {{ auth()->user()->id }}){
            			    			Swal.showValidationMessage('Category already exists');
	            						setTimeout(() => {resolve()}, 500);
            						}
            					}
            				});

				            setTimeout(() => {resolve()}, 500);
			            }

			            bool ? setTimeout(() => {resolve()}, 500) : "";
				    });
				},
			}).then(result => {
				if(result.value){
					$.ajax({
						url: "{{ route('medicine.storeCategory') }}",
						type: "POST",
						data: {
							name: $("[name='name']").val(),
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

		function editCategory(category){
			Swal.fire({
				html: `
	                ${input("name", "Name", category, 3, 9)}
				`,
				width: '400px',
				confirmButtonText: 'Update',
				showCancelButton: true,
				cancelButtonColor: errorColor,
				cancelButtonText: 'Cancel',
				preConfirm: () => {
				    swal.showLoading();
				    return new Promise(resolve => {
				    	let bool = true;
			            if($('.swal2-container input:placeholder-shown').length || $("[name='rhu_id']").val() == ""){
			                Swal.showValidationMessage('Fill all fields');
			            }
			            else{
			            	let bool = false;
			            	// Insert ajax validation
            				$.ajax({
            					url: "{{ route('medicine.getCategories') }}",
            					data: {
            						select: "id",
            						where: ["name", $("[name='name']").val()]
            					},
            					success: result => {
            						result = JSON.parse(result);
            						if(result.length && result[0].admin_id == {{ auth()->user()->id }}){
            			    			Swal.showValidationMessage('Category already exists');
	            						setTimeout(() => {resolve()}, 500);
            						}
            					}
            				});

				            setTimeout(() => {resolve()}, 500);
			            }

			            bool ? setTimeout(() => {resolve()}, 500) : "";
				    });
				},
			}).then(result => {
				if(result.value){
					$.ajax({
						url: "{{ route('medicine.updateCategory') }}",
						type: "POST",
						data: {
							where: ["name", category],
							name: $("[name='name']").val(),
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
				url: "{{ route('medicine.get') }}",
				data: {
					select: '*',
					where: ['id', id],
					load: ["category", 'reorder']
				},
				success: medicine => {
					medicine = JSON.parse(medicine)[0];
					showDetails(medicine);
				}
			})
		}

		function inv(id){
			$.ajax({
				url: '{{ route('stock.get') }}',
				data: {
					select: 'stocks.*',
					join: true,
					where: ['r.user_id', {{ auth()->user()->id }}],
					where2: ['r.medicine_id', id],
					order: ['expiry_date', 'desc']
				},
				success: stocks => {
					stocks = JSON.parse(stocks);
					
					stockString = "";

					let total = 0;
					stocks.forEach(stock => {
						total += stock.qty;
						stockString += `
							<tr>
								<td>${stock.lot_number}</td>
								<td>${moment(stock.expiry_date).format(dateFormat2)}</td>
								<td>${stock.unit_price}</td>
								<td>${stock.qty}</td>
								<td>
									<a class='btn btn-success' data-toggle='tooltip' title='Edit Price' onClick='editPrice(${stock.id}, ${id})'>
								        <i class='fas fa-pencil'></i>
								    </a>
								</td>
							</tr>
						`;
					});

					if(stockString == ""){
						stockString = `
							<tr>
								<td colspan="5">No Stocks</td>
							</tr>
						`;
					}

					Swal.fire({
						html: `
							<table class="table table-hover">
								<thead>
									<tr>
										<th>Lot Number</th>
										<th>Expiry Date</th>
										<th>Unit Price</th>
										<th>Qty</th>
										<th>Edit Price</th>
									</tr>
								</thead>
								<tbody>
									${stockString}
									<tr>
										<td colspan="2"></td>
										<td>TOTAL</td>
										<td>${total}</td>
										<td></td>
									</tr>
								</tbody>
							</table>
						`
					})
				}
			})
		}

		function editPrice(id, rid){
			Swal.fire({
				title: "Enter Price",
				input: "number",
				confirmButtonText: 'Update',
				showCancelButton: true,
				cancelButtonColor: errorColor,
				cancelButtonText: 'Cancel',
				preConfirm: e => {
				    swal.showLoading();
				    return new Promise(resolve => {
				    	let bool = true;

			            if(e.length == 0){
			                Swal.showValidationMessage('Enter Price');
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
						url: "{{ route('stock.update') }}",
						data: {
							id: id,
							unit_price: result.value,
						},
						message: "Success"
					}, () => {
						reload();
						setTimeout(() => {
							inv(rid);
						}, 800);
					})
				}
				else{
					inv(rid);
				}
			})
		}

		function showDetails(medicine){
			Swal.fire({
				html: `
					@if(auth()->user()->role == "Admin")
					<div class="row iRow">
					    <div class="col-md-3 iLabel">
							Image
						</div>
					    <div class="col-md-9 iLabel center">
					    	<img src="{{ asset("/") }}${medicine.image}" alt="Default Image" width="200px;" height="120px;" id="preview">
					    	<br>
					    	<input type="file" id="file" class="d-none" placeholder="Upload Image" accept="image/*" name="image">
					    	<br>
							<label for="file">Update Photo</label>
					    	<br>
					    	<br>
					    </div>
					</div>

					<div class="row iRow">
					    <div class="col-md-3 iLabel">
					        Category
					    </div>
					    <div class="col-md-9 iInput">
					        <select name="category_id" class="form-control">
					        	<option value=""></option>
					        </select>
					    </div>
					</div>
	                ${input("code", "Code", medicine.code, 3, 9)}
	                ${input("brand", "Brand", medicine.brand, 3, 9)}
	                ${input("name", "Generic Name", medicine.name, 3, 9)}
	                ${input("packaging", "Packaging", medicine.packaging, 3, 9)}
	                @endif
	                ${input("reorder_point", "Reorder Point", medicine.reorder.point, 3, 9)}
				`,
				width: '800px',
				confirmButtonText: 'Update',
				showCancelButton: true,
				cancelButtonColor: errorColor,
				cancelButtonText: 'Cancel',
				@if(auth()->user()->role == "Admin")
				didOpen: () => {
					$.ajax({
						url: "{{ route('medicine.getCategories') }}",
						data: {
							select: "*",
							@if(auth()->user()->role == "Admin")
								where: ['admin_id', {{ auth()->user()->id }}]
							@else(auth()->user()->role == "RHU")
								join: true,
								where: ['r.admin_id', {{ auth()->user()->id }}]
							@endif

						},
						success: categories => {
							categories = JSON.parse(categories);
							categoryString = "";

							categories.forEach(category => {
								categoryString += `
									<option value="${category.id}">${category.name}</option>
								`;
							});

							$("[name='category_id']").append(categoryString);
							$("[name='category_id']").select2({
								placeholder: "Select category"
							});

							$("[name='category_id']").val(medicine.category_id).trigger('change');
						}
					})

					$('[name="image"]').on('change', e => {
					    var reader = new FileReader();
					    reader.onload = function (e) {
					        $('#preview').attr('src', e.target.result);
					    }

					    reader.readAsDataURL(e.target.files[0]);
					});
				},
				@endif
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
					@if(auth()->user()->role == "Admin")
						updateSKU({
							id: medicine.id,
							image: $("[name='image']").prop('files')[0],
							category_id: $("[name='category_id']").val(),
							code: $("[name='code']").val(),
							brand: $("[name='brand']").val(),
							name: $("[name='name']").val(),
							packaging: $("[name='packaging']").val(),
							_token: $('meta[name="csrf-token"]').attr('content')
						});
					@endif

					update({
						url: "{{ route('medicine.updateReorder') }}",
						data: {
							where: [
								"medicine_id", medicine.id,
								"user_id", medicine.user_id
							],
							point: $("[name='reorder_point']").val(),
						},
						message: "Success"
					}, () => {
						reload();
					});
				}
			});
		}

		async function updateSKU(data) {
		    let formData = new FormData();
		    formData.append('id', data.id);
		    formData.append('category_id', data.category_id);
		    formData.append('code', data.code);
		    formData.append('brand', data.brand);
		    formData.append('name', data.name);
		    formData.append('packaging', data.packaging);
		    formData.append('_token', data._token);

		    if(data.image != undefined){
		    	formData.append('images', data.image);
		    }

		    await fetch('{{ route('medicine.update') }}', {
				method: "POST", 	
				body: formData
		    });

		    setTimeout(() => {
			    ss('Success');
			    reload();
		    }, 1000);
		}

		function exportSku(){
			let data = {
				user_id: user_id
			};

			window.open("/export/exportSku?" + $.param(data), "_blank");
		}

		function del(id){
			sc("Confirmation", "Are you sure you want to delete?", result => {
				if(result.value){
					swal.showLoading();
					update({
						url: "{{ route('medicine.delete') }}",
						data: {id: id},
					}, () => {
						update({
							url: "{{ route('medicine.deleteReorder') }}",
							data: {id: id},
							message: "Success"
						}, () => {
							reload();
						})
					})
				}
			});
		}
	</script>
@endpush