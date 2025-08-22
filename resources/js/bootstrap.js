import axios from 'axios';
window.axios = axios;

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true
});

window.Echo.channel('orders')
    .listen('new-order', (orderData) => {
        console.log('New Order Received:', orderData);
        
        if (!orderData || !orderData.order_id) {
            console.error('Invalid order data:', orderData);
            return;
        }
        $('#orders-list').prepend(
            `<li>Order #${orderData.order_id} | ${orderData.product_name} | Qty: ${orderData.quantity} | $${orderData.final_amount}</li>`
        );
        $.get("/api/v1/analytics/realtime", function(res) {
            if(res.success) {
                $('#total-orders').text(res.data.orders_last_minute);
                $('#total-revenue').text(res.data.revenue_change_last_minute);
            }
        });
    });

