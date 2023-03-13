<div class="modal fade" id="editCustomSku" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="productModalLabel">Customize SKU</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container">
                    <div class="row">                        
                        <div class="col-12 d-flex flex-column">
                            <div class="sku-table mb-3">
                                <table class="table table-sm w-100">
                                    <thead>
                                        <tr>
                                            <td>#</td>
                                            <td>Name</td>
                                            <td>Price</td>
                                            <td>Print Price</td>
                                            <td>Sum</td>
                                            <td>Action</td>
                                        </tr>
                                    </thead>
                                    <tbody id="cskubody"></tbody>
                                </table>
                            </div>                            
                            <div class="row mt-auto d-none" id="edit-sku-form">
                                <div class="col-5 pr-1">
                                    <input id="sku_price" class="form-control form-control-xs mb-1" autocomplete="off" placeholder="SKU Price">
                                </div>
                                <div class="col-5 pr-1">
                                    <input id="sku_printprice" class="form-control form-control-xs mb-1" placeholder="SKU Print Price"> 
                                </div>
                                <input type="hidden" id="skuid" />
                                <input type="hidden" id="userid" />
                                <div class="col-2">
                                    <button type="submit" class="btn btn-primary btn-xs" id="customizesku">Update</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>               
        </div>
    </div>
</div>
