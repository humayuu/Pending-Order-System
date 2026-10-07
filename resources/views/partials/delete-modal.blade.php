{{-- Open with buttons: data-app-delete data-app-delete-url="..." data-app-delete-message="..." --}}
<div class="modal fade" id="appConfirmDeleteModal" tabindex="-1" aria-labelledby="appConfirmDeleteTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <form id="appConfirmDeleteForm" method="post" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="appConfirmDeleteTitle"><i class="fa-solid fa-triangle-exclamation text-danger me-2 me-1"></i>Confirm delete</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close dialog"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="appConfirmDeleteBody"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger" id="appConfirmDeleteSubmit"><i class="fa-solid fa-trash me-1" aria-hidden="true"></i>Delete</button>
            </div>
        </form>
    </div>
</div>
