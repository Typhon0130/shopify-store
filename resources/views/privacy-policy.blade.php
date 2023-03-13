@extends('layouts.app')

@section('content')

    @include('includes.menu')

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="container-fluid mt-4 mb-2 ps-0">
            <h2>Privacy policy</h2>
        </div>

        {{-- <form action="{{ route('search') }}" method="post" class="w-100">
            @csrf
            <div class="position-relative container-fluid date-search px-0 mt-4 d-flex">
                <input type="text" class="form-control form-control-lg border-0" placeholder="Date From">
                <input type="text" class="form-control form-control-lg border-0 ms-4" placeholder="Date To">
                <input type="hidden" name="type" value="date">
            </div>
        </form> --}}

        <div class="w-100 py-3 px-4 mt-4 bg-white">
            Your privacy is important to us. It is DigitalGoodiesArts Apps’ policy to respect your privacy regarding any information we may collect from you across our apps, and other sites we own and operate.
            We only ask for personal information when we truly need it to provide a service to you. We collect it by fair and lawful means, with your knowledge and consent. We also let you know why we’re collecting it and how it will be used.
            We only retain collected information for as long as necessary to provide you with your requested service. What data we store, we’ll protect within commercially acceptable means to prevent loss and theft, as well as unauthorized access, disclosure, copying, use or modification.
            We don’t share any personally identifying information publicly or with third-parties, except when required to by law.
            You are free to refuse our request for your personal information, with the understanding that we may be unable to provide you with some of your desired services.
            Your continued use of our apps will be regarded as acceptance of our practices around privacy and personal information. If you have any questions about how we handle user data and personal information, feel free to contact us.
            This policy is effective as of 1 August 2021.
        </div>
    </main>
</div>
@endsection