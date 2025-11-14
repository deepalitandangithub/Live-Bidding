import './bootstrap';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Setup Laravel Echo (for public channels — no auth)
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || process.env.MIX_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true,

    // Disable auth for public channels to fix 403 error
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                callback(false, {}); // public channel → no /broadcasting/auth call
            }
        };
    },
});

    // Listen for notifications on the private admin channel
    window.Echo.private(`App.Models.Admin.${adminId}`)
    .notification((notification) => {
        console.log('New Bid Notification:', notification);

        alert(`New bid by ${notification.bidder_name}: ₹${notification.amount}`);
    });

// log when connected
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('Connected to Pusher!');
});



