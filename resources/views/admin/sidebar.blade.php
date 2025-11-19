@auth('admin')
<nav class="sidebar d-flex flex-column p-3">
    <h4 class="text-center text-white mb-4">Action Admin</h4>

    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link" href="{{ route('index') }}">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="{{ route('action.create') }}">New Action</a>
        </li>
        <li class="nav-item mb-2 position-relative">
            <a id="notif-link" class="nav-link d-flex justify-content-between align-items-center" href="#">
                Notifications
                <span id="notif-count" class="badge bg-danger ms-2">
                    {{ auth('admin')->user()->unreadNotifications->count() }}
                </span>
            </a>
        </li>
        <li class="nav-item mt-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger w-100">Logout</button>
            </form>
        </li>
    </ul>
</nav>
@endauth

<script>
document.getElementById('notif-link').addEventListener('click', function(e){
    e.preventDefault();

    fetch('{{ route('notifications.read') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            document.getElementById('notif-count').textContent = '0';
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>
