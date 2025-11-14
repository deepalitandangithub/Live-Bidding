<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $auction->title }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background-color: #f8f9fa; }
        .highlight { animation: flash 0.8s; }
        @keyframes flash { 0% { background: #ffeb3b; } 100% { background: transparent; } }
        .notice-bar {
            background: #ffc107;
            color: #000;
            text-align: center;
            font-weight: bold;
            padding: 10px;
        }
    </style>
</head>
<body class="container py-5">

    <div class="card shadow-lg">
        <div class="card-header text-center bg-primary text-white">
            <h3 class="mb-0">{{ $auction->title }}</h3>
        </div>

        <div class="card-body">
            <p class="lead text-center">
                Current Highest Bid:
                <strong id="highestBid" class="text-success fs-4">₹{{ $auction->highest_bid }}</strong>
                by <strong id="highestBidder" class="text-primary">{{ $auction->highest_bidder ?? '—' }}</strong>
            </p>

            <div class="row justify-content-center mb-3">
                <div class="col-md-6">
                    <form id="bidForm" class="p-3 border rounded bg-light">
                        <div class="mb-3">
                            <label class="form-label">Your Name</label>
                            <input type="text" id="bidderName" class="form-control" placeholder="Enter your name">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bid Amount (₹)</label>
                            <input type="number" id="bidAmount" class="form-control" step="0.01" placeholder="Enter amount">
                        </div>

                        <button type="button" id="placeBidBtn" class="btn btn-success w-100">₹ Place Bid</button>
                        <p id="status" class="text-danger text-center mt-2"></p>
                    </form>
                </div>
            </div>

            <div class="text-center mb-3">
                <p><strong>Action Ends In:</strong> <span id="timer" class="text-danger">--:--</span></p>
                <p><strong>Participants:</strong> <span id="participantsCount" class="badge bg-secondary">0</span></p>
            </div>

            <h5 class="mb-3">Recent Bids</h5>
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 5px;">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark position-sticky top-0">
                        <tr>
                            <th>#</th>
                            <th>Bidder Name</th>
                            <th>Amount (₹)</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody id="recentBids"></tbody>

                    <!-- <tbody id="recentBids">
                        @foreach($auction->bids()->latest()->take(10)->get() as $index => $bid)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $bid->bidder_name }}</td>
                                <td>₹{{ $bid->amount }}</td>
                                <td>{{ $bid->created_at->format('H:i:s') }}</td>
                            </tr>
                        @endforeach
                    </tbody> -->
                </table>
            </div>

        </div>
    </div>

    <audio id="newBidSound" src="https://actions.google.com/sounds/v1/cartoon/clang_and_wobble.ogg" preload="auto"></audio>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
    <script src="https://unpkg.com/laravel-echo/dist/echo.iife.js"></script>

    <script>
        axios.defaults.headers.common['X-CSRF-TOKEN'] =
        document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const auctionId = {{ $auction->id }};
        let isOpen = {{ $auction->is_open ? 'true' : 'false' }};
        const endsAt = new Date("{{ $auction->ends_at }}");

        // Keep track of unique participants
        let participants = new Set();

        // Setup Pusher + Echo
        window.Pusher = Pusher;
        window.Echo = new window.Echo.default({
            broadcaster: 'pusher',
            key: '{{ env('PUSHER_APP_KEY') }}',
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            forceTLS: true,
        });

        // Add bid row
        function addBidRow(bid) {
            const tbody = document.getElementById('recentBids');
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>0</td>
                <td>${bid.bidder_name}</td>
                <td>₹${parseFloat(bid.amount).toFixed(2)}</td>
                <td>${new Date(bid.created_at).toLocaleTimeString()}</td>
            `;
            tbody.prepend(row);

            if (tbody.children.length > 10) tbody.removeChild(tbody.lastChild);

            Array.from(tbody.children).forEach((tr, index) => {
                tr.children[0].textContent = index + 1;
            });

            // Add to participants set
            participants.add(bid.bidder_name);
            document.getElementById('participantsCount').textContent = participants.size;
        }

        // Initialize: load latest bids
        function loadInitialBids() {
            axios.get(`/auction/${auctionId}/latest-data`).then(res => {
                const data = res.data;
                document.getElementById('highestBid').textContent = '₹' + parseFloat(data.highest_bid).toFixed(2);
                document.getElementById('highestBidder').textContent = data.highest_bidder || '—';
                
                const tbody = document.getElementById('recentBids');
                tbody.innerHTML = '';

                participants = new Set(); // reset participants
                data.bids.forEach(bid => addBidRow(bid));
            });
        }
        loadInitialBids();

        // Pusher listener for new bids
        window.Echo.channel(`auction.${auctionId}`)
            .listen('.NewBidPlaced', e => {
                document.getElementById('highestBid').textContent = '₹' + parseFloat(e.amount).toFixed(2);
                document.getElementById('highestBidder').textContent = e.bidder_name || '—';
                addBidRow(e);

                // Highlight highest bid
                const bidEl = document.getElementById('highestBid');
                bidEl.classList.add('highlight');
                setTimeout(() => bidEl.classList.remove('highlight'), 800);
                document.getElementById('newBidSound').play().catch(() => {});
            })
            .listen('.ActionClosed', e => {
                isOpen = false;
                document.getElementById('timer').textContent = '00:00';
                document.getElementById('placeBidBtn').disabled = true;
                document.getElementById('status').textContent = '⏰ Action closed!';
                document.getElementById('status').classList.add('text-danger');
            });

        // Place bid
        document.getElementById('placeBidBtn').addEventListener('click', function () {
            if (!isOpen) return alert('Auction closed');

            const name = document.getElementById('bidderName').value.trim();
            const amount = parseFloat(document.getElementById('bidAmount').value);

            if (!name || !amount) {
                document.getElementById('status').textContent = 'Please enter both name and bid amount.';
                return;
            }

            axios.post(`/auction/${auctionId}/bid`, { bidder_name: name, amount: amount })
                .then(() => {
                    document.getElementById('status').textContent = '';
                    document.getElementById('bidAmount').value = '';
                    document.getElementById('bidderName').value = '';
                })
                .catch(err => {
                    const msg = err.response?.data?.message || 'Error placing bid';
                    document.getElementById('status').textContent = msg;
                });
        });

        // Countdown Timer
        function tick() {
            const diff = endsAt - new Date();
            if (diff <= 0 && isOpen) {
                isOpen = false;
                document.getElementById('timer').textContent = '00:00';
                document.getElementById('placeBidBtn').disabled = true;
                document.getElementById('status').textContent = '⏰ Action closed!';
                axios.post(`/auction/${auctionId}/close`).catch(() => {});
               
                setTimeout(() => {
                    location.reload();
                }, 1500);
                return;
            }
            const s = Math.floor(diff / 1000);
            const m = Math.floor(s / 60);
            const sec = s % 60;
            document.getElementById('timer').textContent = `${String(m).padStart(2,'0')}:${String(sec).padStart(2,'0')}`;
            setTimeout(tick, 1000);
        }
        tick();

        // Debug Pusher connection
        Echo.connector.pusher.connection.bind('connected', () => console.log('✅ Pusher connected'));
        Echo.connector.pusher.connection.bind('error', err => console.log('❌ Pusher error', err));
    </script>

</body>
</html>
