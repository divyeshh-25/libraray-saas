@props(['cols', 'message' => 'Nothing to show.'])
<tr data-empty class="hidden">
    <td colspan="{{ $cols }}" class="!p-0">
        <div class="empty">
            <div class="ring"><x-icon name="search" class="w-5 h-5" /></div>
            <div class="font-medium">{{ $message }}</div>
            <p class="text-muted text-[13px] mt-1">Try a different search or clear the filters to see everything.</p>
            <button type="button" class="btn btn-sm mt-4" data-clear-filters>Clear filters</button>
        </div>
    </td>
</tr>
