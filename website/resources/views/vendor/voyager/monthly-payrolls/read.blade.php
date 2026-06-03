@extends('voyager::master')

@section('page_title', __('voyager::generic.view') . ' ' . $dataType->getTranslatedAttribute('display_name_singular'))

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i> {{ __('voyager::generic.viewing') }}
        {{ ucfirst($dataType->getTranslatedAttribute('display_name_singular')) }} &nbsp;

        @can('edit', $dataTypeContent)
            <a href="{{ route('voyager.' . $dataType->slug . '.edit', $dataTypeContent->getKey()) }}" class="btn btn-info">
                <i class="glyphicon glyphicon-pencil"></i> <span
                    class="hidden-xs hidden-sm">{{ __('voyager::generic.edit') }}</span>
            </a>
        @endcan
        @can('delete', $dataTypeContent)
            @if ($isSoftDeleted)
                <a href="{{ route('voyager.' . $dataType->slug . '.restore', $dataTypeContent->getKey()) }}"
                    title="{{ __('voyager::generic.restore') }}" class="btn btn-default restore"
                    data-id="{{ $dataTypeContent->getKey() }}" id="restore-{{ $dataTypeContent->getKey() }}">
                    <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">{{ __('voyager::generic.restore') }}</span>
                </a>
            @else
                <a href="javascript:;" title="{{ __('voyager::generic.delete') }}" class="btn btn-danger delete"
                    data-id="{{ $dataTypeContent->getKey() }}" id="delete-{{ $dataTypeContent->getKey() }}">
                    <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">{{ __('voyager::generic.delete') }}</span>
                </a>
            @endif
        @endcan
        @can('browse', $dataTypeContent)
            <a href="{{ route('voyager.' . $dataType->slug . '.index') }}" class="btn btn-warning">
                <i class="glyphicon glyphicon-list"></i> <span
                    class="hidden-xs hidden-sm">{{ __('voyager::generic.return_to_list') }}</span>
            </a>
        @endcan
    </h1>
    @include('voyager::multilingual.language-selector')
@stop

@section('content')
    <div class="page-content read container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="panel panel-bordered" style="padding-bottom:5px;">
                    <!-- form start -->
                    @foreach ($dataType->readRows as $row)
                        @php
                            if ($dataTypeContent->{$row->field . '_read'}) {
                                $dataTypeContent->{$row->field} = $dataTypeContent->{$row->field . '_read'};
                            }
                        @endphp
                        <div class="panel-heading" style="border-bottom:0;">
                            <h3 class="panel-title">{{ $row->getTranslatedAttribute('display_name') }}</h3>
                        </div>

                        <div class="panel-body" style="padding-top:0;">
                            @if (isset($row->details->view_read))
                                @include($row->details->view_read, [
                                    'row' => $row,
                                    'dataType' => $dataType,
                                    'dataTypeContent' => $dataTypeContent,
                                    'content' => $dataTypeContent->{$row->field},
                                    'view' => 'read',
                                    'options' => $row->details,
                                ])
                            @elseif (isset($row->details->view))
                                @include($row->details->view, [
                                    'row' => $row,
                                    'dataType' => $dataType,
                                    'dataTypeContent' => $dataTypeContent,
                                    'content' => $dataTypeContent->{$row->field},
                                    'action' => 'read',
                                    'view' => 'read',
                                    'options' => $row->details,
                                ])
                            @elseif($row->type == 'image')
                                <img class="img-responsive"
                                    src="{{ filter_var($dataTypeContent->{$row->field}, FILTER_VALIDATE_URL) ? $dataTypeContent->{$row->field} : Voyager::image($dataTypeContent->{$row->field}) }}">
                            @elseif($row->type == 'multiple_images')
                                @if (json_decode($dataTypeContent->{$row->field}))
                                    @foreach (json_decode($dataTypeContent->{$row->field}) as $file)
                                        <img class="img-responsive"
                                            src="{{ filter_var($file, FILTER_VALIDATE_URL) ? $file : Voyager::image($file) }}">
                                    @endforeach
                                @else
                                    <img class="img-responsive"
                                        src="{{ filter_var($dataTypeContent->{$row->field}, FILTER_VALIDATE_URL) ? $dataTypeContent->{$row->field} : Voyager::image($dataTypeContent->{$row->field}) }}">
                                @endif
                            @elseif($row->type == 'relationship')
                                @include('voyager::formfields.relationship', [
                                    'view' => 'read',
                                    'options' => $row->details,
                                ])
                            @elseif(
                                $row->type == 'select_dropdown' &&
                                    property_exists($row->details, 'options') &&
                                    !empty($row->details->options->{$dataTypeContent->{$row->field}}))
                                <?php echo $row->details->options->{$dataTypeContent->{$row->field}}; ?>
                            @elseif($row->type == 'select_multiple')
                                @if (property_exists($row->details, 'relationship'))
                                    @foreach (json_decode($dataTypeContent->{$row->field}) as $item)
                                        {{ $item->{$row->field} }}
                                    @endforeach
                                @elseif(property_exists($row->details, 'options'))
                                    @if (!empty(json_decode($dataTypeContent->{$row->field})))
                                        @foreach (json_decode($dataTypeContent->{$row->field}) as $item)
                                            @if (@$row->details->options->{$item})
                                                {{ $row->details->options->{$item} . (!$loop->last ? ', ' : '') }}
                                            @endif
                                        @endforeach
                                    @else
                                        {{ __('voyager::generic.none') }}
                                    @endif
                                @endif
                            @elseif($row->type == 'date' || $row->type == 'timestamp')
                                @if (property_exists($row->details, 'format') && !is_null($dataTypeContent->{$row->field}))
                                    {{ \Carbon\Carbon::parse($dataTypeContent->{$row->field})->formatLocalized($row->details->format) }}
                                @else
                                    {{ $dataTypeContent->{$row->field} }}
                                @endif
                            @elseif($row->type == 'checkbox')
                                @if (property_exists($row->details, 'on') && property_exists($row->details, 'off'))
                                    @if ($dataTypeContent->{$row->field})
                                        <span class="label label-info">{{ $row->details->on }}</span>
                                    @else
                                        <span class="label label-primary">{{ $row->details->off }}</span>
                                    @endif
                                @else
                                    {{ $dataTypeContent->{$row->field} }}
                                @endif
                            @elseif($row->type == 'color')
                                <span class="badge badge-lg"
                                    style="background-color: {{ $dataTypeContent->{$row->field} }}">{{ $dataTypeContent->{$row->field} }}</span>
                            @elseif($row->type == 'coordinates')
                                @include('voyager::partials.coordinates')
                            @elseif($row->type == 'rich_text_box')
                                @include('voyager::multilingual.input-hidden-bread-read')
                                {!! $dataTypeContent->{$row->field} !!}
                            @elseif($row->type == 'file')
                                @if (json_decode($dataTypeContent->{$row->field}))
                                    @foreach (json_decode($dataTypeContent->{$row->field}) as $file)
                                        <a
                                            href="{{ Storage::disk(config('voyager.storage.disk'))->url($file->download_link) ?: '' }}">
                                            {{ $file->original_name ?: '' }}
                                        </a>
                                        <br />
                                    @endforeach
                                @elseif($dataTypeContent->{$row->field})
                                    <a href="{{ Storage::disk(config('voyager.storage.disk'))->url($row->field) ?: '' }}">
                                        {{ __('voyager::generic.download') }}
                                    </a>
                                @endif
                            @else
                                @include('voyager::multilingual.input-hidden-bread-read')
                                <p>{{ $dataTypeContent->{$row->field} }}</p>
                            @endif
                        </div><!-- panel-body -->
                        @if (!$loop->last)
                            <hr style="margin:0;">
                        @endif
                    @endforeach

                </div>
            </div>
        </div>
    </div>

    {{-- BẮT ĐẦU PHẦN CUSTOM: HIỂN THỊ CÁC CÔNG VIỆC TRONG PHIẾU LƯƠNG --}}
    @php
        $payrollTasks = $dataTypeContent
            ->tasks()
            ->with(['order', 'stonePrice'])
            ->get()
            ->groupBy(function ($task) {
                return $task->order ? $task->order->name : 'Công việc lẻ (Không gắn với đơn hàng)';
            });
    @endphp

    <div class="panel panel-bordered" style="margin-top: 20px;">
        <div class="panel-heading" style="background-color: #f8f9fa;">
            <h3 class="panel-title" style="font-weight: bold; color: #333;">
                <i class="voyager-list"></i> Bảng kê chi tiết công việc nghiệm thu trong kỳ
            </h3>
        </div>
        <div class="panel-body">
            @if ($payrollTasks->isEmpty())
                <p class="text-muted" style="font-style: italic;">Không có công việc nào được đính kèm trong phiếu lương/ứng
                    này.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        @foreach ($payrollTasks as $orderName => $tasks)
                            <tr style="background-color: #e2e8f0;">
                                <td colspan="9" style="font-weight: bold; font-size: 15px; color: #1e293b;">
                                    📦 Đơn hàng: {{ $orderName }}
                                </td>
                            </tr>

                            <tr style="background-color: #f1f5f9; font-weight: bold;">
                                <th rowspan="2" style="width: 5%; text-align: center; vertical-align: middle;">STT</th>
                                <th rowspan="2" style="vertical-align: middle;">NỘI DUNG</th>
                                <th colspan="3" style="text-align: center;">Quy cách (mm)</th>
                                <th rowspan="2" style="text-align: center; vertical-align: middle;">Khối
                                    lượng<br>(S.Lượng)</th>
                                <th rowspan="2" style="text-align: center; vertical-align: middle;">ĐVT</th>
                                <th rowspan="2" style="text-align: center; vertical-align: middle;">Đơn giá<br>Sản xuất
                                </th>
                                <th rowspan="2" style="text-align: right; vertical-align: middle;">Thành tiền<br>Sản xuất
                                </th>
                            </tr>
                            <tr style="background-color: #f1f5f9; font-weight: bold;">
                                <th style="text-align: center; width: 6%;">Dài</th>
                                <th style="text-align: center; width: 6%;">Rộng</th>
                                <th style="text-align: center; width: 6%;">Cao</th>
                            </tr>

                            {{-- @php $orderTotal = 0; @endphp --}}
                            @foreach ($tasks as $index => $task)
                                @php
                                    // $orderTotal += $task->reward;
                                    $donGia = $task->stonePrice ? $task->stonePrice->price_per : 0;
                                @endphp
                                <tr>
                                    <td style="text-align: center;">{{ $index + 1 }}</td>
                                    <td>{{ $task->name }}</td>
                                    <td style="text-align: center;">{{ $task->length }}</td>
                                    <td style="text-align: center;">{{ $task->width }}</td>
                                    <td style="text-align: center;">{{ $task->height }}</td>
                                    <td style="text-align: center;">{{ $task->quantity ?? ($task->completed_count ?? 1) }}
                                    </td>
                                    <td style="text-align: center;">{{ $task->unit }}</td>
                                    <td style="text-align: right;">{{ $donGia > 0 ? number_format($donGia) : '' }}</td>
                                    <td style="text-align: right; font-weight: bold; color: #16a34a;">
                                        {{ number_format($task->reward ?? 0) }}
                                    </td>
                                </tr>
                            @endforeach

                            {{-- <tr style="background-color: #fdfbf7;">
                                <td colspan="8" style="text-align: right; font-weight: bold; color: #555;">Cộng đơn hàng
                                    này:</td>
                                <td style="text-align: right; font-weight: bold; color: #d97706; font-size: 15px;">
                                    {{ number_format($orderTotal) }}
                                </td>
                            </tr> --}}
                        @endforeach
                    </table>
                </div>
            @endif
        </div>
    </div>
    {{-- KẾT THÚC PHẦN CUSTOM --}}

    {{-- Single delete modal --}}
    <div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                        aria-label="{{ __('voyager::generic.close') }}"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i> {{ __('voyager::generic.delete_question') }}
                        {{ strtolower($dataType->getTranslatedAttribute('display_name_singular')) }}?</h4>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('voyager.' . $dataType->slug . '.index') }}" id="delete_form" method="POST">
                        {{ method_field('DELETE') }}
                        {{ csrf_field() }}
                        <input type="submit" class="btn btn-danger pull-right delete-confirm"
                            value="{{ __('voyager::generic.delete_confirm') }} {{ strtolower($dataType->getTranslatedAttribute('display_name_singular')) }}">
                    </form>
                    <button type="button" class="btn btn-default pull-right"
                        data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@stop

@section('javascript')
    @if ($isModelTranslatable)
        <script>
            $(document).ready(function() {
                $('.side-body').multilingual();
            });
        </script>
    @endif
    <script>
        var deleteFormAction;
        $('.delete').on('click', function(e) {
            var form = $('#delete_form')[0];

            if (!deleteFormAction) {
                // Save form action initial value
                deleteFormAction = form.action;
            }

            form.action = deleteFormAction.match(/\/[0-9]+$/) ?
                deleteFormAction.replace(/([0-9]+$)/, $(this).data('id')) :
                deleteFormAction + '/' + $(this).data('id');

            $('#delete_modal').modal('show');
        });
    </script>
@stop
