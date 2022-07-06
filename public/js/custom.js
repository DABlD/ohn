let errorColor = "#f76c6b";
let successColor = "#28a745";

function ss(title = "", text = ""){
	Swal.fire({
		icon: "success",
		title: title,
		text: text,
		timer: 1000,
		showConfirmButton: false
	});
};

function se(title = "", text = ""){
	Swal.fire({
		icon: "danger",
		title: title,
		text: text,
		timer: 1000,
		showConfirmButton: false
	});
};

function sc(title = "", text = "", callback = null){
	Swal.fire({
		icon: "question",
		title: title,
		text: text,
		showCancelButton: true,
		cancelButtonColor: errorColor
	}).then(result => {
		if(typeof callback == "function"){
			callback(result);
		}
	});
};

function input(name, placeholder, value, c1, c2, type = "text", autocomplete=""){
    return `
        <div class="row iRow">
            <div class="col-md-${c1} iLabel">
                ${placeholder}
            </div>
            <div class="col-md-${c2} iInput">
                <input type="${type}" name="${name}" placeholder="Enter ${placeholder}" class="form-control" value="${value ?? ""}" ${autocomplete}>
            </div>
        </div>
    `;
};

function reload(){
	$('#table').DataTable().ajax.reload();
};

function update(data, callback = null){
	$.ajax({
		url: data.url,
		type: "POST",
		data: {
			...data.data,
			_token: $('meta[name="csrf-token"]').attr('content')
		},
		success: () => {
			if(data.message){
				ss(data.message);
			}

			if(typeof callback == "function"){
				callback();
			}
		}
	});
}