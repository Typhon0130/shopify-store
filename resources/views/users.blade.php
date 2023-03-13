@extends('layouts.app')

@section('content')
    @include('includes.menu')

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="container-fluid mt-4 mb-2 ps-0">
            <h2>{{ $title }}</h2>
        </div>

        <div class="w-100 py-3 mt-4 bg-white rounded table-responsive">

            <div class="alert alert-info mx-2 pt-3 pb-2" role="alert">
                <i class="fa fa-exclamation-circle rem-12 mb-2 mr-2"></i>Make sure you have set shop name before activating merchant!
            </div>

            <table class="table table-condensed listing-table" id="accor">
                <thead>
                    <tr>
                        <th><b>N</b></th>
                        <th><b>Name</b></th>
                        <th><b>Company name</b></th>
                        <th><b>Email</b></th>
                        <th><b>Shop name</b></th>
                        <th><b>Status</b></th>
                        <th><b>Manage SKUs</b></th>
                        <th><b>Edit shop name</b></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($users as $key => $user)
                        <tr class="user-{{ $user->id }}">

                            <td class="v-align-middle">{{ $key + 1 }}</td>
                            <td class="v-align-middle">{{ $user->name }}</td>
                            <td class="v-align-middle">{{ $user->company_name }}</td>
                            <td class="v-align-middle">{{ $user->email }}</td>
                            <td id="shopname-{{ $user->id }}">{{ $user->shop_name }}</td>
                            <td class="switchToggle v-align-middle">
								<input type="checkbox" id="switch{{ $user->id }}" {{ $user->active ? 'checked' : '' }}>
    							<label for="switch{{ $user->id }}" class="user-status" data-toggleid="{{ $user->id }}">Toggle</label>
							</td>
                            <td class="v-align-middle">
                                <span class="btn btn-xs btn-pod py-1" data-toggle="modal" data-target="#editCustomSku" data-userid="{{ $user->id }}" >
                                    <i class="fa fa-pencil-alt text-white mr-1"></i></i>Manage
                                </span>
                            </td>
                            <td class="v-align-middle">
                                <span class="btn btn-xs btn-pod py-1" data-toggle="modal" data-target="#editShopName" data-userid="{{ $user->id }}" >
                                    <i class="fa fa-pencil-alt text-white mr-1"></i>Edit
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    </main>
@endsection
