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
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Segoe UI',system-ui,-apple-system,sans-serif;background:#f8f9fa;padding:16px;line-height:1.5}
    h1{font-size:clamp(1.25rem,4vw,1.75rem);margin-bottom:16px;color:#333}
    
    /* Table wrapper for horizontal scroll on mobile */
    .table-wrapper{overflow-x:auto;-webkit-overflow-scrolling:touch;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);background:#fff}
    
    table{border-collapse:collapse;width:100%;min-width:700px}
    th,td{border:none;border-bottom:1px solid #e9ecef;padding:12px 10px;text-align:left;font-size:0.9rem}
    th{background:#f8f9fa;font-weight:600;color:#495057;position:sticky;top:0;z-index:1}
    tr:hover{background:#f8f9fa}
    tr:last-child td{border-bottom:none}
    
    .status{padding:4px 10px;border-radius:20px;font-weight:600;font-size:0.8rem;display:inline-block}
    .pending{background:#fff3cd;color:#856404}
    .completed{background:#d4edda;color:#155724}
    
    #loading{padding:20px;text-align:center;color:#666}
    
    /* Responsive adjustments */
    @media (max-width:768px){
      body{padding:12px}
      th,td{padding:10px 8px;font-size:0.85rem}
    }
    @media (max-width:480px){
      body{padding:8px}
      th,td{padding:8px 6px;font-size:0.8rem}
      .status{padding:3px 8px;font-size:0.75rem}
    }
  </style>
</head>
<body>
  <h1>Orders</h1>
  <div id="loading">Loading orders…</div>
  <div class="table-wrapper">
    <table id="ordersTable" style="display:none">
      <thead>
        <tr><th>ID</th><th>Order Key</th><th>Product</th><th>Customer</th><th>Phone</th><th>Qty</th><th>Total</th><th>Status</th><th>Created</th></tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

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