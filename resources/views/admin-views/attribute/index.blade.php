@extends('layouts.admin.app')

@section('title','Add new attribute')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-add-circle-outlined"></i> {{__('messages.add')}} {{__('messages.new')}} {{__('messages.attribute')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.attribute.store')}}" method="post">
                    @csrf
                    <div class="form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{__('messages.name')}}</label>
                        <input type="text" name="name" class="form-control" placeholder="New Attribute" required>
                    </div>
                    <button type="submit" class="btn btn-primary">{{__('messages.submit')}}</button>
                </form>
            </div>

            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <hr>
                <div class="card">
                    <div class="card-header">
                    <h5>{{__('messages.attribute')}} {{__('messages.list')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$attributes->total()}}</span></h5>
                        <form action="javascript:" id="search-form" >
                                        <!-- Search -->
                                        @csrf
                            <div class="input-group input-group-merge input-group-flush">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="tio-search"></i>
                                    </div>
                                </div>
                                <input id="datatableSearch_" type="search" name="search" class="form-control"
                                        placeholder="Search" aria-label="Search" required>
                                <button type="submit" class="btn btn-light">{{__('messages.search')}}</button>
                            </div>
                            <!-- End Search -->
                        </form>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                 "order": [],
                                 "orderCellsTop": true
                               }'>
                            <thead class="thead-light">
                            <tr>
                                <th>{{__('messages.#')}}</th>
                                <th style="width: 50%">{{__('messages.name')}}</th>
                                <th style="width: 10%">{{__('messages.action')}}</th>
                            </tr>
                            
                            </thead>

                            <tbody id="set-rows">
                            @foreach($attributes as $key=>$attribute)
                                <tr>
                                    <td>{{$key+$attributes->firstItem()}}</td>
                                    <td>
                                    <span class="d-block font-size-sm text-body">
                                        {{$attribute['name']}}
                                    </span>
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-white" href="{{route('admin.attribute.edit',[$attribute['id']])}}" title="{{__('messages.edit')}}"><i class="tio-edit"></i>
                                        </a>
                                        <a class="btn btn-sm btn-white" href="javascript:" onclick="form_alert('attribute-{{$attribute['id']}}','Want to delete this attribute ?')" title="{{__('messages.delete')}}"><i class="tio-delete-outlined"></i>
                                        </a>
                                        <form action="{{route('admin.attribute.delete',[$attribute['id']])}}"
                                                method="post" id="attribute-{{$attribute['id']}}">
                                            @csrf @method('delete')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <hr>
                        <table class="page-area">
                            <tfoot>
                            {!! $attributes->links() !!}
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            var datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function () {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });


            $('#column3_search').on('change', function () {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
    </script>
    <script>
        $('#search-form').on('submit', function () {
            var formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.attribute.search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#set-rows').html(data.view);
                    $('.page-area').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });
    </script>
@endpush