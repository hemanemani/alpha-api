@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="my-4">Approved International Inquiries</h3>

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
                @foreach ($approved_offers as $approved_offer)
                    <tr>
                        <td>{{ $approved_offer->inquiry_number }}</td>
                        <td>{{ $approved_offer->mobile_number }}</td>
                        <td>{{ $approved_offer->inquiry_date }}</td>
                        <td>{{ $approved_offer->specific_product }}</td>
                        <td>
                            @if ($approved_offer->status == 1)
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-danger">Cancelled</span>
                            @endif
                        </td>



                        <td>
                            <a href="{{ route('international_inquiries.edit', $approved_offer->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <a href="{{ route('international_inquiries.show', $approved_offer->id) }}" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
