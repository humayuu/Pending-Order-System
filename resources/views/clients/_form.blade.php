<div class="mb-3">
    <label class="form-label" for="name">Name</label>
    <input type="text" name="name" id="name" class="form-control" required
           value="{{ old('name', $client?->name) }}">
</div>
<div class="mb-3">
    <label class="form-label" for="phone">Phone <span class="text-body-secondary fw-normal">(optional)</span></label>
    <input type="text" name="phone" id="phone" class="form-control"
           value="{{ old('phone', $client?->phone) }}" placeholder="Leave blank if not available (saved as ----------)">
</div>
<div class="mb-3">
    <label class="form-label" for="address">Address <span class="text-body-secondary fw-normal">(optional)</span></label>
    <textarea name="address" id="address" class="form-control" rows="3"
              placeholder="Leave blank if not available (saved as ----------)">{{ old('address', $client?->address) }}</textarea>
</div>
