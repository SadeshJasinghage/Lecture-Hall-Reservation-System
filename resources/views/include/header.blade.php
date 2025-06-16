
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-lg">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">Welcome</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <ul class="navbar-nav me-auto">
        @auth
            @if(auth()->user()->role == 'admin')
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('admin.dashboard') }}">Home</a>
                </li>
            @elseif(auth()->user()->role == 'user')
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('user.dashboard') }}">Home</a>
                </li>
            @elseif(auth()->user()->role == 'guest')
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('guest.dashboard') }}">Home</a>
                </li>
            @endif

            <li class="nav-item">
              <a class="nav-link" href="{{ route('contact') }}">Contact Us</a>
            </li>
            <li class="nav-item">
              <a class="nav-link btn btn-danger text-white px-3 ms-lg-2" href="{{ route('logout') }}">Logout</a>
            </li>
            
        @else
            <li class="nav-item">
              <a class="nav-link" href="{{ route('login') }}">Login</a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Register
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('userRegister') }}">Register as User</a></li>
                <li><a class="dropdown-item" href="{{ route('guestRegister') }}">Register as Guest</a></li>
                <li><a class="dropdown-item" href="{{ route('adminRegister') }}">Register as Admin</a></li>
              </ul>
            </li>
        @endauth
      </ul>

      <!-- Greeting Message -->
      @auth
      <div class="navbar-text text-light ms-auto" id="greeting">
          <strong>{{ auth()->user()->name }}</strong><br>
          <small id="role">({{ ucfirst(auth()->user()->role) }})</small>
      </div>
      @endauth
    </div>
  </div>
</nav>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let now = new Date();
    let hours = now.getHours();
    let greetingText = "";

    if (hours < 12) {
        greetingText = "Good morning, ";
    } else if (hours < 18) {
        greetingText = "Good afternoon, ";
    } else {
        greetingText = "Good evening, ";
    }

    let greetingSpan = document.getElementById("greeting");
    if (greetingSpan) {
        let name = greetingSpan.querySelector("strong").innerText;
        let role = document.getElementById("role").innerText; 
        greetingSpan.innerHTML = `<strong>${greetingText} ${name}</strong><br><small>${role}</small>`;
    }
});
</script>



