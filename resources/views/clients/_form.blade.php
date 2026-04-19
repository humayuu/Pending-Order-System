<div class="mb-3">
    <label class="form-label" for="name">Name</label>
    <input type="text" name="name" id="name" class="form-control" required
           value="{{ old('name', $client?->name) }}">
</div>
<div class="mb-3">
    <label class="form-label" for="phone">Phone <span class="text-muted fw-normal">(optional)</span></label>
    <input type="text" name="phone" id="phone" class="form-control"
           value="{{ old('phone', $client?->phone) }}" placeholder="Leave blank if not available">
</div>
<div class="mb-3">
    <label class="form-label" for="address">Address</label>
    <textarea name="address" id="address" class="form-control" rows="3" required>{{ old('address', $client?->address) }}</textarea>
</div>
