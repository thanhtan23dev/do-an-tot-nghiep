@extends('voyager::bread.edit-add')

@section('javascript')
    @parent

    @if (auth()->check() && auth()->user()->role && auth()->user()->role->name == 'giam_sat')
        <script>
            $(document).ready(function() {
                $('.panel-body .form-group').css({
                    'pointer-events': 'none',
                    'opacity': '0.5'
                });

                $('.form-group:has(select[name="status"])').css({
                    'pointer-events': 'auto',
                    'opacity': '1'
                });

                $('.form-group:has(textarea[name="reject_reason"]), .form-group:has(input[name="reject_reason"])').css({
                    'pointer-events': 'auto',
                    'opacity': '1'
                });
            });
        </script>
    @endif
@stop
