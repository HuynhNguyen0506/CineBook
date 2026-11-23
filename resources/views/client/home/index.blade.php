<h3>HOME</h3>

<a href="{{route('profile')}}">PROFILE</a>

<form action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="submit">LOGOUT</button>
</form>