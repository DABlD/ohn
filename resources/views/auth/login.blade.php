<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com/"></script>
	<link rel="stylesheet" href="{{ asset('css/sweetalert2.min.css') }}">
</head>
<body>
<section class="min-h-screen flex items-stretch text-white ">
    <div class="lg:flex w-1/2 hidden bg-gray-500 bg-no-repeat bg-cover relative items-center"
         style="background-image: url({{ asset('images/ohn/piddig/clinic.jpg') }});">
        <div class="absolute bg-black opacity-30 inset-0 z-0"></div>
        <div class="w-full px-24 z-10">
            <h1 class="text-5xl font-bold text-left tracking-wide">Pharmacy Inventory System</h1>
            <p class="text-3xl my-4">PREVENTIVE HEALTHCARE. NOW.</p>
        </div>
    </div>
    <div class="lg:w-1/2 w-full flex items-center justify-center text-center md:px-16 px-0 z-0 bg-slate-200">
        <div class="absolute lg:hidden z-10 inset-0 bg-gray-500 bg-no-repeat bg-cover items-center"
             style="background-image: url({{ asset('images/ohn/piddig/clinic.jpg') }});">
            <div class="absolute bg-black opacity-60 inset-0 z-0"></div>
        </div>
        <div class="w-full py-6 z-20">
            <h1 class="my-6">
                <img class="w-auto h-40 inline-flex" src="{{ asset('images/ohn/OneHealthPharmacy2.png') }}">
            </h1>
            <form method="POST" action="{{ route('login') }}" class="sm:w-2/3 w-full px-4 lg:px-0 mx-auto">
				@csrf
                <div class="pb-2 pt-4">
                    <input type="text" name="username" id="username" placeholder="Username"
                           class=" text-gray-700 rounded-md block w-full p-4 text-lg rounded-sm bg-white">
                </div>
                <div class="pb-2 pt-4">
                    <input class="text-gray-700 rounded-md block w-full p-4 text-lg rounded-sm bg-white" type="password"
                           name="password" id="password" placeholder="Password">
                </div>
                <div class="text-right text-gray-400 hover:text-gray-100">
                    <a onclick="forgotPassword()">Forgot your password?</a>
                </div>
                <div class="px-4 pb-2 pt-4">
                    <button id="loginBtn" type="submit" class="uppercase block w-full p-4 text-lg rounded-full bg-blue-500 hover:bg-indigo-600 focus:outline-none">
                        sign in
                    </button>
                </div>
                {{-- <div class="text-center text-black">
                    <p>Doesn't have an account?</p><a href="http://clinica.onehealthnetwork.com.ph/register" class="text-blue-400 hover:underline hover:text-black-200"">Register Here</a>
                </div> --}}

            </form>
        </div>
    </div>
</section>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-bundle.min.js') }}"></script>
<script src="{{ asset('js/ohn/jquery.easing.min.js') }}"></script>
<script src="{{ asset('js/ohn/validate.js') }}"></script>
<script src="{{ asset('js/ohn/jquery.waypoints.min.js') }}"></script>
<script src="{{ asset('js/ohn/isotope.pkgd.min.js') }}"></script>
<script src="{{ asset('js/ohn/venobox.min.js') }}"></script>
<script src="{{ asset('js/ohn/owl.carousel.min.js') }}"></script>
<script src="{{ asset('js/ohn/aos.js') }}"></script>
<script src="{{ asset('js/ohn/baguetteBox.min.js') }}"></script>
<script src="{{ asset('js/ohn/vanilla-zoom.js') }}"></script>
<script src="{{ asset('js/ohn/theme.js') }}"></script>
<script src="{{ asset('js/ohn/mainbc19.js?1664408744') }}"></script>
<script src="{{ asset('js/sweetalert2.min.js') }}"></script>

<script>
	@if($errors->all())
		Swal.fire({
			icon: 'error',
            html: `
                @foreach ($errors->all() as $error)
                    {{ $error }}<br/>
                @endforeach
            `,
		});
	@endif

    function forgotPassword(){
        Swal.fire({
            title: 'Enter your email address',
            input: 'email',
        }).then(result => {
            if(result.value){
                swal.showLoading();
                $.ajax({
                    url: '{{ route('forgotPassword') }}',
                    data: {email: result.value},
                    success: result => {
                        Swal.fire({
                            title: result,
                        })
                    }
                })
            }
        })
    }
</script>
</body>
</html>