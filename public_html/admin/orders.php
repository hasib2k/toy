<?php
// Simple admin orders page (front-end fetches the API)
// TODO: Protect this page with session-based authentication (auth_check.php)
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin — Orders</title>
  <style>
    body{font-family:Segoe UI,Arial;background:#fff;padding:16px}
    table{border-collapse:collapse;width:100%}
    th,td{border:1px solid #ddd;padding:8px;text-align:left}
    th{background:#f5f5f5}
    .status{padding:4px 8px;border-radius:4px;font-weight:600}
    .pending{background:#fff3cd;color:#856404}
    .completed{background:#d4edda;color:#155724}
  </style>
</head>
<body>
  <h1>Orders</h1>
  <div id="loading">Loading orders…</div>
  <table id="ordersTable" style="display:none">
    <thead>
      <tr><th>ID</th><th>Order Key</th><th>Product</th><th>Customer</th><th>Phone</th><th>Quantity</th><th>Total</th><th>Status</th><th>Created</th></tr>
    </thead>
    <tbody></tbody>
  </table>

  <script>
    fetch('/api/orders.php').then(function(res){return res.json()}).then(function(json){
      document.getElementById('loading').style.display='none';
      if(!json.success){ document.body.insertAdjacentHTML('beforeend','<p>Error loading orders</p>'); return; }
      var t=document.getElementById('ordersTable'); t.style.display='table';
      var body=t.querySelector('tbody');
      json.orders.forEach(function(o){
        var tr=document.createElement('tr');
        tr.innerHTML = '<td>'+o.id+'</td>' +
                       '<td>'+o.order_key+'</td>' +
                       '<td>'+o.product_name+'</td>' +
                       '<td>'+o.name+'</td>' +
                       '<td>'+o.phone+'</td>' +
                       '<td>'+o.quantity+'</td>' +
                       '<td>'+o.total_amount+'</td>' +
                       '<td><span class="status '+(o.status||'pending')+'">'+(o.status||'pending')+'</span></td>' +
                       '<td>'+o.created_at+'</td>';
        body.appendChild(tr);
      });
    }).catch(function(){ document.getElementById('loading').textContent='Failed to load orders.' });
  </script>
</body>
</html>