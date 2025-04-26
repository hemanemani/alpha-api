<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Invoice</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
  <style>
    body {
      font-size: 12px;
      padding: 20px;
    }
    .table th, .table td {
      vertical-align: middle;
    }
  </style>
</head>
<body>

<div class="container border border-1 p-3">
  <!-- Header -->
  <div class="text-center mb-3">
    <h5 class="fw-bold">Orgenik E-commerce Private Limited</h5>
    <hr class="border border-dark border-1">
    <p class="mb-0">
      A – 401, Panchdhara Complex, Nr. Grand Bhagwati, S.G. Highway, Bodakdev, Ahmedabad - 380054<br>
      Mobile No- 9328819369, email: business@orgenikbulk.com
    </p>
    <p class="fw-semibold">PAN: AADCO2634G</p>
    <hr class="border border-dark border-1 mt-3">
  </div>

  <!-- Title -->
  <h6 class="text-center text-decoration-underline fw-bold mb-3">INVOICE</h6>

  <!-- Invoice Info -->
  <div class="row mb-3">
    <div class="col-6"><strong>Date:</strong> {{$data['invoicing_invoice_generate_date']}}</div>
    <div class="col-6"><strong>Bill No:</strong> {{ $data['invoicing_invoice_number'] }}</div>
  </div>

  <!-- Billing Address -->
  <div class="row mb-4">
    <div class="col-6 border border-1 p-2">
      <p class="fw-bold text-uppercase mb-1">Bill To</p>
      <p class="mb-0 fw-bold">{{$data['invoice_to']}}</p>
      <p>{{ $data['invoice_address'] }}</p>
      <p>GSTIN: {{$data['invoice_gstin'] }}</p>
    </div>
  </div>

  <!-- Table -->
  <table class="table table-bordered text-center mb-4">
    <thead class="table-light">
      <tr>
        <th class="text-start w-50">Particulars</th>
        <th>Rate per Kg</th>
        <th>Total Kg</th>
        <th>HSN</th>
        <th class="text-end">Amount</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="text-start">{{ $data['product_name'] }}</td>
        <td>{{ $data['rate_per_kg'] }}</td>
        <td>{{ $data['total_kg']  }}</td>
        <td>{{ $data['hsn'] }}</td>
        <td class="text-end">{{ $data['invoicing_amount'] }}</td>
      </tr>
      <tr>
        <td class="text-start fw-semibold" colspan="4">Amount</td>
        <td class="text-end">{{ $data['invoicing_total_amount'] }}</td>
      </tr>
      <tr>
        <td class="text-start" colspan="4">Other Expenses: Packaging Charges</td>
        <td class="text-end">{{ $data['packaging_expenses'] }}</td>
      </tr>
      <tr>
        <td class="text-start fw-bold" colspan="4">Total Amount</td>
        <td class="text-end fw-bold">{{ $data['invoicing_total_amount'] }}</td>
      </tr>
    </tbody>
  </table>

  <!-- Amount in words -->
  <p class="fw-semibold">Amount in Words: {{$data['total_amount_in_words']}} only</p>

  <!-- Footer -->
  <div class="row mt-4">
    <div class="col-6">
      <p class="fw-semibold text-decoration-underline">Bank Details</p>
      <p>Name: Orgenik E-commerce Pvt. Ltd.</p>
      <p>Account No: 50200053047210</p>
      <p>Branch: Ahmedabad - Ambawadi</p>
      <p>UPI ID: 8238820675@hdfcbank</p>
    </div>
    <div class="col-6 text-end d-block mt-auto">
      <p>Orgenik E-commerce Pvt. Ltd.</p>
      <p class="fw-semibold">P.K. Das</p>
      <p class="text-muted" style="font-size: 11px;">Authorized Person</p>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
