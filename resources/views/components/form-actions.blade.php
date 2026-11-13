@props(['cancel', 'save' => 'Save changes', 'submit' => false])
<div class="flex items-center justify-end gap-2 pt-1">
    <a href="{{ $cancel }}" class="btn">Cancel</a>
    @if ($submit)
        <button type="submit" class="btn btn-primary">{{ $save }}</button>
    @else
        <button type="button" class="btn btn-primary" data-toast="Saved successfully (demo)">{{ $save }}</button>
    @endif
</div>
