{{-- Open with buttons: data-app-delete data-app-delete-url="..." data-app-delete-message="..." --}}
<div class="modal fade" id="appConfirmDeleteModal" tabindex="-1" aria-labelledby="appConfirmDeleteTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <form id="appConfirmDeleteForm" method="post" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="appConfirmDeleteTitle">Confirm delete</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close dialog"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="appConfirmDeleteBody"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger" id="appConfirmDeleteSubmit">Delete</button>
            </div>
        </form>
    </div>
</div>
