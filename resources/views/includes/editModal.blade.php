<div class="edit-modal bg-white shadow-sm p-3">
    <p class="pointer" onclick="closeEditModal()">X</p>
    <h4 class="w-100 text-center mt-2 mb-4"><i class="fa fa-pencil-alt mr-3 text-warning"></i>Edit order data</h4>
    <div class="edit-form-container">
        <form id="orderData">
            <label>Address 1</label>
            <input name="address1" type="text" class="edit-modal-input form-control form-control-sm" placeholder="Address 1">
            <label class="mt-2">Address 2</label>
            <input name="address2" type="text" class="edit-modal-input form-control form-control-sm" placeholder="Address 2">
            <div class="row">
                <div class="col-6">
                    <label class="mt-2">City</label>
                    <input name="city" type="text" class="edit-modal-input form-control form-control-sm" placeholder="City">
                </div>
                <div class="col-6">
                    <label class="mt-2">Zip</label>
                    <input name="zip" type="text" class="edit-modal-input form-control form-control-sm" placeholder="Zip">
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <label class="mt-2">Country</label>
                    <input name="country" type="text" class="edit-modal-input form-control form-control-sm" placeholder="Country">
                </div>
                <div class="col-6">
                    <label class="mt-2">Country code</label>
                    <input name="country_code" type="text" class="edit-modal-input form-control form-control-sm" placeholder="Country code">
                </div>
                <input name="id" type="hidden">
            </div>
            <div class="row mt-2">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <span id="validationResult" class="d-flex align-items-center mr-1"></span>
                    <button type="button" class="btn btn-success btn-sm float-right ml-1" onclick="validateAddress()">Validate address</button>
                </div>
            </div>
            <div class="mt-3">
                <label class="mb-1">Weight bu SKUs</label>
                <select class="form-control form-control-sm mb-2" id="sku"></select>
                <div id="images"></div>
            </div>

            <div class="row dhl24">
                <div class="col-12">
                    <label class="mt-3">Parcel type</label>
                    <select class="form-control form-control-sm" name="packagetype">
                        <option value="1">Package</option>
                        <option value="2">Envelope</option>
                        <option value="3">Pallet</option>
                    </select>
                </div>
            </div>
            <div class="row dhl24">
                <div class="col-6">
                    <label class="mt-2">Width (cm)</label>
                    <input name="width" type="number" min="1" class="edit-modal-input form-control form-control-sm" placeholder="Width (cm)">
                </div>
                <div class="col-6">
                    <label class="mt-2">Height (cm)</label>
                    <input name="height" type="number" min="1" class="edit-modal-input form-control form-control-sm" placeholder="Height (cm)">
                </div>
            </div>
            <div class="row dhl24">
                <div class="col-6">
                    <label class="mt-2">Length (cm)</label>
                    <input name="length" type="number" min="1" class="edit-modal-input form-control form-control-sm" placeholder="Length (cm)">
                </div>
                <div class="col-6">
                    <label class="mt-2">Weight (kg)</label>
                    <input type="number" min="1" step="1" name="weight" class="edit-modal-input form-control form-control-sm" placeholder="Weight (kg)">
                </div>
            </div>
            <div class="row dhl24">
                <div class="col-12">
                    <label class="mt-2">Content</label>
                    <input name="content" type="text" class="edit-modal-input form-control form-control-sm" maxlength="30" placeholder="Content of the parcel (max 30 chars)">
                </div>
            </div>
            <div class="d-grid mt-2">
                <button type="button" class="btn btn-primary" onclick="saveEditModalData()">Save</button>
            </div>
        </form>
    </div>
</div>
