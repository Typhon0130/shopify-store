
<div class="modal fade" id="uploadHoldImage" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container">
                    <div class="row">
                        
                        <div class="col-12">                            
                            <div class="py-3">                                                                 
                                <label for="imgupload">
                                    <span>Click here to upload image</span>  
                                </label>
                                <input type="file" id="imgupload" class="d-none" onchange="processupload()" />
                            </div>
                            <input type="hidden" id="itemid" />
                            {{-- <input type="text" id="hordername" /> --}}
                        </div>
                       
                    </div>
                </div>
            </div>  
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>             
        </div>
    </div>
</div>
