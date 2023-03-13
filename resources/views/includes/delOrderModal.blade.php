<div class="modal fade" id="delOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered dialog-sm">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h6 class="modal-title">Confirm delete order</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body py-5">
				<span class="h6">Do you really want to delete order <span class="h6" id="ordername" ></span>?</span>
				<input type="hidden" id="order-id" />
            </div>
            <div class="modal-footer border-top-0">
        		<button type="button" class="btn btn-danger" id="delete-order">Delete order</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
