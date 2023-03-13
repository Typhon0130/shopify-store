
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Edit product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container">
                    <div class="row">                        
                        <div class="col-4" style="border-right: solid #ededed 1px;">
                            <h5>Image</h5>
                            <label for="imgupload" class="w-100 my-4">
                                <img id="product_image" class="w-100">
                            </label>
                            <input type="file" class="display-none" id="imgupload" onchange="uploadProductImage()" />
                            <input id="product_name" class="form-control mb-2">
                            <button class="btn btn-primary" id="save_name">Save name</button>
                        </div>
                        <div class="col-8 d-flex flex-column">
                            <h5 class="mb-2">Product info</h5>
                            <div class="sku-table mb-3">
                                <table class="w-100">
                                    <thead>
                                        <tr id="skuheader"></tr>
                                    </thead>
                                    <tbody id="skubody">
                                        <tr ></tr>
                                    </tbody>
                                </table>
                            </div>                            
                            <div class="row mt-auto">
                                <div class="col-12">
                                    <input id="sku_name" class="form-control form-control-xs mb-1" placeholder="New SKU">
                                </div>
                                <div class="col-6 pr-1">
                                    <input id="printfile_size" class="form-control form-control-xs mb-1" placeholder="Printfile size (px)"> 
                                </div>
                                <div class="col-6 pl-1">
                                    <input id="sku_price" class="form-control form-control-xs mb-1" placeholder="SKU price">
                                </div>
                                <div class="col-6 pr-1">
                                    <input id="sku_size" class="form-control form-control-xs mb-1" placeholder="Product size (ml)">
                                </div>
                                <div class="col-6 pl-1">
                                    <input id="sku_print_price" class="form-control form-control-xs mb-1" placeholder="SKU print price">
                                </div>
                                <div class="col-6 pr-1">
                                    <input id="sku_weight" class="form-control form-control-xs mb-1" placeholder="Product weight (gr)"> 
                                </div>
                                <div class="col-6 pl-1">
                                    <input id="sku_quantity" class="form-control form-control-xs mb-1" placeholder="Quantity in stock">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-xs" id="addeditsku" disabled>Add SKU</button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="hproductid" />
                        <input type="hidden" id="hsku" />
                    </div>
                </div>
            </div>               
        </div>
    </div>
</div>
