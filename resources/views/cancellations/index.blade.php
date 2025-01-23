@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="my-4">Cancellation Inquiries</h3>

        <table id="example" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>Inquiry Number</th>
                    <th>Mobile Number</th>
                    <th>Inquiry Date</th>
                    <th>Specific Product</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cancelled_offers as $cancelled_offer)
                    <tr>
                        <td>{{ $cancelled_offer->inquiry_number }}</td>
                        <td>{{ $cancelled_offer->mobile_number }}</td>
                        <td>{{ $cancelled_offer->inquiry_date }}</td>
                        <td>{{ $cancelled_offer->specific_product }}</td>
                        <td>
                            @if ($cancelled_offer->status == 1)
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-danger">Cancelled</span>
                            @endif
                        </td>



                        <td>
                            <a href="{{ route('inquiries.show', $cancelled_offer->id) }}" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
