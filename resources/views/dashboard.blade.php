@extends('layouts.app')
@section('content')

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <section class="col-lg-12 connectedSortable">

                <div class="card" id="card1">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-1"></i>
                            Sales Per Franchise
                        </h3>
                    </div>

                    <div class="card-body">
                        <canvas id="sales" width="100%"></canvas>
                    </div>
                </div>

                <div class="card" id="card2">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-1"></i>
                            Delivered Requests
                        </h3>
                    </div>

                    <div class="card-body">
                        <canvas id="deliveredRequests" width="100%"></canvas>
                    </div>
                </div>

            </section>

            @foreach($widgets as $data)
                <section class="col-lg-6 connectedSortable">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-table mr-1"></i>
                                {{ $data->first()->transaction_type->type }}
                            </h3>
                        </div>

                        <div class="card-body">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <td>#</td>
                                        <td>Medicine</td>
                                        <td>Ref</td>
                                        <td>Lot Number</td>
                                        <td>Total</td>
                                        <td>Trx Date</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $trx)
                                        <tr>
                                            <td>{{ $loop->index+1 }}</td>
                                            <td>{{ $trx->reorder->medicine->name }}</td>
                                            <td>{{ $trx->reference }}</td>
                                            <td>{{ $trx->lot_number }}</td>
                                            <td>{{ number_format($trx->amount, 2, '.', '') }}</td>
                                            <td>{{ $trx->created_at->format('F j, Y H:i:s') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @endforeach

        </div>
    </div>
</section>

@endsection

@push('styles')
    <style>
        .col-lg-6 .card-body{
            max-height: 500px;
            overflow: scroll;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('js/chart.min.js') }}"></script>

    <script>
        $(document).ready(() => {
            var ctx, myChart, ctx2, myChart2;

            Swal.fire('Loading Data');
            swal.showLoading();

            $.ajax({
                url: '{{ route("report.salesPerRhu") }}',
                success: result =>{
                    result = JSON.parse(result);
                    
                    if(result.dataset.length){
                        ctx = document.getElementById('sales').getContext('2d');
                        myChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: result.labels,
                                datasets: result.dataset
                            }
                        });

                    }
                    else{
                        $('#card1').hide();
                    }
                    swal.close();
                }
            })

            $.ajax({
                url: '{{ route("report.deliveredRequests") }}',
                success: result =>{
                    result = JSON.parse(result);
                    
                    if(result.dataset.length){
                        ctx = document.getElementById('deliveredRequests').getContext('2d');
                        myChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: result.labels,
                                datasets: result.dataset
                            }
                        });
                    }
                    else{
                        $('#card2').hide();
                    }

                    swal.close();
                }
            })
        });
    </script>
@endpush