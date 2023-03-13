</div>
</div>

		
		{{-- @if(Auth::CHeck() && Auth::User()->administrator == 1)
			<div class="tools container-fluid d-flex align-items-center justify-content-end py-2 px-4 bg-dark position-fixed">
				@if(isset($type))
	                <div class="btn-group">
	                    <button class="btn btn-sm dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">Move to</button>
	                    <ul class="dropdown-menu p-0">
	                        @if($type == 0)
	                            <li><a href="#" class="d-block move-home py-2 px-3" onclick="moveTo(event, 1)">Pending</a></li>
	                            <li><a href="#" class="d-block move-pending py-2 px-3" onclick="moveTo(event, 2)">Pending DHL</a></li>
	                        @elseif($type == 1)
	                            <li><a href="#" class="d-block move-home py-2 px-3" onclick="moveTo(event, 0)">Home</a></li>
	                            <li><a href="#" class="d-block move-pending py-2 px-3" onclick="moveTo(event, 2)">Pending DHL</a></li>
	                        @elseif($type == 2)
	                            <li><a href="#" class="d-block move-home py-2 px-3" onclick="moveTo(event, 0)">Home</a></li>
	                            <li><a href="#" class="d-block move-pending py-2 px-3" onclick="moveTo(event, 1)">Pending</a></li>
	                        @elseif($type == 3)
	                            <li><a href="#" class="d-block move-home py-2 px-3" onclick="moveTo(event, 0)">Home</a></li>
	                            <li><a href="#" class="d-block move-pending py-2 px-3" onclick="moveTo(event, 1)">Pending</a></li>
	                            <li><a href="#" class="d-block move-pending py-2 px-3" onclick="moveTo(event, 2)">Pending DHL</a></li>
	                        @endif
	                    </ul>
	                </div>
				@endif

				<a href="" class="text-danger ms-5" onclick="removeIten(event)"><i class="fa fa-trash-alt"></i></a>
			</div>
		@endif --}}

		<!-- Page loader -->
		<div class="loader"></div>

		<!-- Message box -->
		<div class="message d-none position-fixed bg-white p-3 pr-4 rounded	border shadow-sm">
			<div class="position-relative">
				<div class="d-flex align-items-center">
					<div class="message-icon mr-3">
						<img src="{{ asset('assets/img/success.svg') }}" width="32">
					</div>
					<b></b>
				</div>
				<i class="fa fa-times text-secondary position-absolute pointer mt-1" onclick="closeMessage()"></i>
			</div>
		</div>

		<!-- Message box -->
		<div class="fail-message d-none position-fixed bg-white p-3 pr-4 rounded	border shadow-sm">
			<div class="position-relative">
				<div class="d-flex align-items-center">
					<div class="message-icon mr-3">
						<img src="{{ asset('assets/img/fail.svg') }}" width="32">
					</div>
					<b></b>
				</div>
				<i class="fa fa-times text-secondary position-absolute pointer mt-1" onclick="closeFailMessage()"></i>
			</div>
		</div>

	   <!-- Edit modal -->
	   @include('includes.editModal')

		<!-- Replace image modal -->
		@include('includes.changeImageModal')

		<!-- Upload hold image -->
		@include('includes.uploadHoldImage')

		@if(Request::segment(1) == 'shipment-archive')
			@include('includes.trackpackage')
			@include('includes.orderdetails')
		@endif

		@if(Request::segment(2) == 'home' || Request::segment(2) == 'pending' || Request::segment(1) == 'search')
			@include('includes.replaceImage')
			@include('includes.delOrderModal')
		@endif

		@if(Request::segment(1) == 'users')
			@include('includes.editCustomSku')
			@include('includes.editShopName')
		@endif

		<!-- jQuery -->
		<script src="{{ asset('assets/js/jquery.min.js') }}"></script>

		<!-- Bootstrap -->
		<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

		<!-- Bootstrap Dropdown Calendar -->
		<script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>

		<!-- Calendar -->
      <script src="{{ asset('assets/js/calendar.min.js') }}"></script>

      <!-- Custom scripts -->
		<script src="{{ asset('assets/js/scripts.js') }}"></script>

		<!-- Font awesome -->
		<script src="{{ asset('assets/js/fontawesome.min.js') }}"></script>
	</body>
</html>