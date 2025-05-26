<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: Inter, sans-serif;
      padding: 20px;
    }
    p{
    	font-size: 13px;
    	padding: 2px;
    	margin: 0;
    }
    h2{
      font-size: 25px;
      margin: 0;
      padding: 0;
    }
    h6{
      font-size: 15px;
      margin: 0;
      padding: 0;
    }
    .container {
      border: 1px solid #000;
      padding: 15px;
    }
    .text-center {
      text-align: center;
    }
    .fw-bold { font-weight: bold; }
    .fw-semibold { font-weight: 600; }
    .border {
      border: 1px solid #000;
    }
    .table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 1rem;
    }
    .table th, .table td {
      border: 1px solid #000;
      padding: 6px;
      vertical-align: middle;
      text-align: center;
    }
    .table th.text-start, .table td.text-start {
      text-align: left;
    }
    .table td.text-end {
      text-align: right;
    }
    .row {
      display: flex;
      flex-wrap: wrap;
      flex-direction: row;
      justify-content: space-between;
    }
    .col-6 {
      width: 50%;
      box-sizing: border-box;
    }
    .p-2 { padding: 10px; }
    .mt-0 { margin-top: 0; }
    .mb-0 { margin-bottom: 0; }
    .mb-1 { margin-bottom: 4px; }
    .mb-3 { margin-bottom: 12px; }
    .mb-4 { margin-bottom: 16px; }
    .mt-4 { margin-top: 16px; }
    .text-end {
      text-align: right;
    }
    .text-decoration-underline {
      text-decoration: underline;
    }
    .text-uppercase{
    	text-transform: uppercase;
    }
  </style>
</head>
<body>

<div class="container">
  <!-- Header -->
  <div class="text-center mb-3">
    <h2 class="mb-3">Orgenik E-commerce Private Limited</h2>
    <hr>
    <p style="line-height: 15px;">
      A – 401, Panchdhara Complex, Nr. Grand Bhagwati, S.G. Highway, Bodakdev, Ahmedabad - 380054
      Mobile No- 9328819369, email: business@orgenikbulk.com
    </p>
    <p class="fw-semibold mt-0">PAN: AADCO2634G</p>
    <hr class="mt-3">
  </div>

  @php
    $currentYear = date('Y');
    $financialYear = $currentYear . '-' . substr($currentYear + 1, -2);
  @endphp


  <!-- Title -->
  <h6 class="text-center text-decoration-underline fw-bold mb-4">INVOICE</h6>


  <table style="width: 100%; margin-bottom: 12px;">
    <tr>
      

      <td style="width: 50%;"><strong>Date:</strong> {{ \Carbon\Carbon::parse($data['invoicing_invoice_generate_date'])->format('d/m/Y') }}</td>
      <td style="width: 50%; margin-left:150px"><strong>Bill No:</strong> {{ $data['invoicing_invoice_number'] }} ({{ $financialYear }})
     </td>
    </tr>
  </table>


  <!-- Billing Address -->

  <div class="row mb-4">
    <div class="border p-2" style="width:45%">
      <span class="fw-bold text-uppercase mb-1">Bill To</span>
      <p class="mb-0 fw-bold">{{ $data['invoice_to'] }}</p>
      <p>{{ $data['invoice_address'] }}</p>
      <p>GSTIN: {{ $data['invoice_gstin'] }}</p>
    </div>
  </div>

  @php $products = $data['products'] ?? []; @endphp

  <!-- Product Table -->
  <table class="table mb-4">
    <thead>
      <tr>
        <th class="text-start">Particulars</th>
        <th>Rate per Kg</th>
        <th>Total Kg</th>
        <th class="text-end">Amount</th>
        <th>HSN</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($products as $product)
        <tr>
          <td class="text-start">{{ $product['product_name'] }}</td>
          <td>{{ $product['rate_per_kg'] }}</td>
          <td>{{ $product['total_kg'] }}</td>
          <td class="text-end">{{ number_format($product['product_total_amount'],2) }}</td>
          <td>{{ $product['hsn'] }}</td>
        </tr>
      @endforeach
      <tr>
        <td class="text-start fw-semibold" colspan="4">Amount</td>
        <td class="text-end">{{ number_format($data['invoicing_amount'], 2) }}</td>
      </tr>
      <tr>
        <td class="text-start" colspan="4">Other Exps:-</td>
        <td class="text-end">{{ number_format($data['expenses'],2) }}</td>
      </tr>
      <tr>
        <td class="text-start" colspan="4">Packaging Charges</td>
        <td class="text-end">{{ number_format($data['packaging_expenses'],2) }}</td>
      </tr>

      <tr>
        <td class="text-start fw-bold" colspan="4">Total Amount</td>
        <td class="text-end fw-bold">{{ number_format($data['invoicing_total_amount'],2) }}</td>
      </tr>
    </tbody>
  </table>

  <!-- Amount in words -->
  <p class="fw-bold mb-4">Amount in Words: {{ $data['total_amount_in_words'] }} only</p>

  <!-- Footer -->

  <table style="width: 100%;">
  <tr>
    <td style="width: 50%;"><strong>Bank Details</strong>
      <p>Name: Orgenik E-commerce Pvt. Ltd.</p>
      <p>Account No: 50200053047210</p>
      <p>Branch: Ahmedabad - Ambawadi</p>
      <p>UPI ID: 8238820675@hdfcbank</p>
    </td>
    <td style="width: 50%; text-align: right;">
      <p>Orgenik E-commerce Pvt. Ltd.</p>
      <p class="fw-semibold">P.K. Das</p>
      <p >Authorized Person</p>
    </td>
  </tr>
  </table>
</div>

</body>
</html>
