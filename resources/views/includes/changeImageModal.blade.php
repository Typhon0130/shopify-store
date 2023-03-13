
<div class="modal fade" id="changeImageModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Change image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container">
                    <div class="row">
                        
                        <div class="col-7">
                            <h5>Product</h5>
                            <img id="image" class="py-4 w-100" >
                            <table class="table table-borderless">
                                <thead>
                                  <tr>
                                    <th scope="col">Qty</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Sku</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr>
                                    <td id="hqty"></td>
                                    <td>on hold</td>
                                    <td id="hsku"></td>
                                  </tr>
                                </tbody>
                              </table>
                        </div>
                        <div class="col-5">
                            <h5>Shipping address</h5>
                            <div class="py-4">
                                <div class="mb-2"><span id="hname"></span></div>
                                <div class="mb-2"><span id="haddress1"></span></div>
                                <div class="mb-2"><span id="haddress2"></span></div>
                                <div class="mb-2"><span id="hcity"></span>, <span id="hzip"></span></div>
                                <div class="mb-2"><span id="hcountry"></span></div>
                                <div class="mb-2"><span id="hphone"></span></div>
                            </div>
                            <div class="py-4">
                                <label class="btn btn-secondary" for="imgupload">Replace</label>
                                <input type="file" id="imgupload" onchange="processupload()" />
                            </div>
                        </div>
                        <input type="hidden" id="hid" /> <input type="hidden" id="hordername" />
                    </div>
                </div>
            </div>               
        </div>
    </div>
</div>
