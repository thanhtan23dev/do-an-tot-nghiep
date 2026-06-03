@if ($action == 'browse' || $action == 'read')
    <span>
        {{ number_format((float) $content, 0, ',', '.') }} đ
    </span>
@else
    <input type="number" class="form-control" name="{{ $row->field }}" value="{{ (float) $content }}" step="1">
@endif
