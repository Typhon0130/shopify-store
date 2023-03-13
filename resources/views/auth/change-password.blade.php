@extends('layouts.app')

@section('content')
    @include('includes.menu')

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="container-fluid mt-4 mb-2 ps-0">
            {{-- <h2>{{ $title }}</h2> --}}
        </div>
        
        @if(auth()->user()->company_id > 2)
            {{-- Plan --}}
            <form action="{{route('set.plan')}}" method="POST">
                @csrf
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="my-0 font-weight-normal">Choose plan</h5>
                    </div>
                    <div class="card-body">
                        <div class="card-deck text-center">
                            <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        @if($user->current_plan == 0 || Session::has('msg')) 
                                            <div class="alert alert-warning rounded-0 mb-3">
                                                @if($user->current_plan == 0) 
                                                    <p><b>You have not selected any plan.</b> Please select a plan to proceed with the application.</p>
                                                @endif
                                                @if(Session::has('msg'))
                                                    <p>{!!Session::get('msg')!!}</p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="card mb-4 box-shadow mx-xl-3 plan-card @if($user->current_plan==1) selected-card @endif">
                                        <div class="card-header">
                                            <h5 class="my-0 font-weight-normal">Plan S</h5>
                                        </div>
                                        <div class="card-body">
                                            <h1 class="card-title pricing-card-title">€19.99 <small class="text-muted">/ mo</small></h1>
                                            <ul class="list-unstyled mt-3 mb-5">
                                                <li>1.5% per order</li>
                                            </ul>
                                            <button type="submit" class="btn btn-block btn-pod" name="plan" value="1">Select plan</button>
                                        </div>
                                    </div>
                                    <div class="card mb-4 box-shadow mx-xl-3 plan-card @if($user->current_plan==2) selected-card @endif">
                                        <div class="card-header">
                                            <h5 class="my-0 font-weight-normal">Plan M</h5>
                                        </div>
                                        <div class="card-body">
                                            <h1 class="card-title pricing-card-title">€24.99 <small class="text-muted">/ mo</small></h1>
                                            <ul class="list-unstyled mt-3 mb-5">
                                                <li>1.0% per order</li>
                                            </ul>
                                            <button type="submit" class="btn btn-block btn-pod" name="plan" value="2">Select plan</button>
                                        </div>
                                    </div>
                                    <div class="card mb-4 box-shadow mx-xl-3 plan-card @if($user->current_plan==3) selected-card @endif">
                                        <div class="card-header">
                                            <h5 class="my-0 font-weight-normal">Plan XL</h5>
                                        </div>
                                        <div class="card-body">
                                            <h1 class="card-title pricing-card-title">€29.99 <small class="text-muted">/ mo</small></h1>
                                            <ul class="list-unstyled mt-3 mb-5">
                                                <li>0.7% per order</li>
                                            </ul>
                                            <button type="submit" class="btn btn-block btn-pod" name="plan" value="3">Select plan</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Balance --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="my-0 font-weight-normal">Balance</h5>
                </div>
                <div class="card-body">
                    <div class="card-deck text-center">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-lg-4 mb-3 mb-lg-0 d-flex align-items-center">
                                    <span>Your balance: <b>{{$user->balance}}€</b></span>
                                </div>
                                @if($user->last_pay_month > 0)
                                    <div class="col-lg-4 mb-2 mb-lg-0 d-flex align-items-center">
                                        <span>Activated by: <b> {{date('F', mktime(0, 0, 0, mnthSer2YrMnth($user->last_pay_month)['month'], 10))}}, {{mnthSer2YrMnth($user->last_pay_month)['year']}}</b></span>
                                    </div>
                                @endif
                                <div class="col-lg-4 mb-1 mb-lg-0 d-flex align-items-center">
                                    <!-- Replace "test" with your own sandbox Business account app client ID -->
                                    <script src="https://www.paypal.com/sdk/js?client-id={{env('PAYPAL_CLIENT_ID_FR', '')}}&currency=EUR"></script>
                                    <!-- Set up a container element for the button -->
                                    <span class="mr-1">Add to balance:</span>
                                    <input id="amount" class="paypal" size="10"><span class="europlaceholder">€</span>
                                    <div id="paypal-button-container" class="mt-1"></div>
                                    <script>
                                        paypal.Buttons({
                                            // Sets up the transaction when a payment button is clicked
                                            createOrder: (data, actions) => {
                                                return actions.order.create({
                                                    purchase_units: [{
                                                        amount: {
                                                            currency_code: "EUR",
                                                            value: parseInt(document.getElementById('amount').value),
                                                            breakdown: {
                                                                item_total: {
                                                                    /* Required when including the `items` array */
                                                                    currency_code: "EUR",
                                                                    value: parseInt(document.getElementById('amount').value)
                                                                }
                                                            }
                                                        },
                                                        items: [{
                                                            name: "App usage fee",
                                                            /* Shows within upper-right dropdown during payment approval */
                                                            description: "{{Auth::user()->id}}",
                                                            /* Item details will also be in the completed paypal.com transaction view */
                                                            unit_amount: {
                                                                currency_code: "EUR",
                                                                value: parseInt(document.getElementById('amount').value)
                                                            },
                                                            quantity: "1"
                                                        }, ]
                                                    }]
                                                });
                                            },
                                            // Finalize the transaction after payer approval
                                            onApprove: (data, actions) => {
                                                return actions.order.capture().then(function(orderData) {
                                                    // Successful capture! For dev/demo purposes:
                                                    console.log('Capture result', orderData, JSON.stringify(orderData, null, 2),
                                                        orderData.status);
                                                    // const transaction = orderData.purchase_units[0].payments.captures[0].id;
                                                    const orderId = orderData.id;
                                                    //console.log(orderId);
                                                    window.location.href = "validate-transaction/"+orderId;
                                                    //alert(`Transaction ${transaction.status}: ${transaction.id}\n\nSee console for all available details`);
                                                    // When ready to go live, remove the alert and show a success message within this page. For example:
                                                    // const element = document.getElementById('paypal-button-container');
                                                    // element.innerHTML = '<h3>Thank you for your payment!</h3>';
                                                    // Or go to another URL:  actions.redirect('thank_you.html');
                                                });
                                            },
                                            style: {
                                                layout: 'horizontal',
                                                size: 'small',
                                                tagline: 'false',
                                                height:30
                                            }
                                        }).render('#paypal-button-container');
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Password change --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="my-0 font-weight-normal">Change password</h5>
            </div>
            <div class="card-body">
                <div class="card-deck">
                    <div class="container">
                        
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{-- <h5>Success!</h5> --}}
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="post" action="{{ route('update.password') }}">
                            @csrf
                            <div class="form-group row mt-4">
                                <label for="inputPassword" class="col-sm-3 col-form-label text-left text-md-right">Current Password</label>
                                <div class="col-sm-9 col-lg-8">
                                    <input type="password" class="form-control" name="currentPassword" placeholder="Password">
                                    @error('currentPassword')
                                        <small class="text-danger"><strong>{{ $message }}</strong></small>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="newPassword" class="col-sm-3 col-form-label text-left text-md-right">New Password</label>
                                <div class="col-sm-9 col-lg-8">
                                    <input type="password" class="form-control" name="newPassword" placeholder="New password">
                                    @error('newPassword')
                                        <small class="text-danger"><strong>{{ $message }}</strong></small>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="confirmPassword" class="col-sm-3 col-form-label text-left text-md-right">Confirm Password</label>
                                <div class="col-sm-9 col-lg-8">
                                    <input type="password" class="form-control" name="confirmPassword" placeholder="Confirm password">
                                    @error('confirmPassword')
                                        <small class="text-danger"><strong>{{ $message }}</strong></small>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-9 col-lg-8 offset-md-3 text-left">
                                    <button class="btn btn-pod">Save password</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>        

    </main>
@endsection
