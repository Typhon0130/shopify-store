@extends('layouts.app')

@section('content')

    @include('includes.menu')

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="container-fluid mt-4 mb-2 ps-0">
            <h2>Simple upload</h2>
        </div>

        <form action="{{ route('processupload') }}" method="post" enctype="multipart/form-data" class="w-100">
			@csrf
			<div class="form-group row">
				<label for="ordername" class="col-sm-2 col-form-label">Order number</label>
				<div class="col-sm-10">
					<input type="text" id="ordername" name="ordername" class="form-control" placeholder="Order number">
				</div>
			</div>
		
			<div class="form-group row">
			  <label for="imagenumber" class="col-sm-2 col-form-label">Image number</label>
			  <div class="col-sm-10">
				 <input type="text" id="imagenumber" name="imagenumber" class="form-control " placeholder="Image number">
			  </div>
			  </div>
		
			<div class="form-group row">
				<label for="orderimage" class="col-sm-2 col-form-label">Order image</label>
				<div class="col-sm-10">
					<input type="file" id="orderimage" name="orderimage" class="form-control">
				</div>
			</div>
		
			<div class="form-group row mt-4">
				<button type="submit" class="offset-2 btn btn-primary">Upload image</button>
			</div>
		</form>
    </main>

@endsection





