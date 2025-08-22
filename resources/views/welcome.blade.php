<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Real-Time Orders Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://js.pusher.com/8.3/pusher.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h2 { margin-top: 30px; }
        #orders-list li { margin-bottom: 5px; }
        .analytics { margin-top: 20px; }
    </style>
</head>
<body>

<h1>Real-Time Orders Dashboard</h1>

<h2>Latest Orders</h2>
<ul id="orders-list"></ul>

<div class="analytics">
    <h2>Analytics (Last Minute)</h2>
    <p>Total Orders: <span id="total-orders">0</span></p>
    <p>Total Revenue: $<span id="total-revenue">0</span></p>
</div>
<script>
    // Enable Pusher logging
    Pusher.logToConsole = true;

    const pusher = new Pusher('6f5b105dad597b6991c8', {
        cluster: 'eu',
        forceTLS: true
    });

    const channel = pusher.subscribe('orders');

    channel.bind('new-order', function(e) {
        console.log('New Order Event:', e); 
        
        const orderData = e;
        
        if (!orderData || !orderData.order_id) {
            console.error('Invalid order data:', orderData);
            return;
        }

        console.log('Processing Order:', orderData);

        // Add new order to the list
        $('#orders-list').prepend(
            `<li>Order #${orderData.order_id} | ${orderData.product_name} | Qty: ${orderData.quantity} | $${orderData.final_amount}</li>`
        );

        // Update analytics
        $.get("/api/v1/analytics/realtime", function(res) {
            if(res.success) {
                $('#total-orders').text(res.data.orders_last_minute);
                $('#total-revenue').text(res.data.revenue_change_last_minute);
            }
        });
    });

    // Initial load
    $.get("/api/v1/analytics/realtime", function(res) {
        if(res.success) {
            $('#total-orders').text(res.data.orders_last_minute);
            $('#total-revenue').text(res.data.revenue_change_last_minute);

            if (res.data.recent_orders && Array.isArray(res.data.recent_orders)) {
                res.data.recent_orders.forEach(order => {
                    $('#orders-list').append(
                        `<li>Order #${order.id} | ${order.product?.name || 'N/A'} | Qty: ${order.quantity} | $${order.final_amount}</li>`
                    );
                });
            }
        }
    });
</script>

</body>
</html>
