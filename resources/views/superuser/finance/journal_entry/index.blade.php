@extends('superuser.app')

@section('content')
    <nav class="breadcrumb bg-white push">
        <span class="breadcrumb-item">Finance</span>
        <a class="breadcrumb-item" href="{{ route('superuser.finance.journal_entry.index') }}">Journal Entry</a>
    </nav>
    <div id="alert-block"></div>

    <form class="ajax" data-action="{{ route('superuser.finance.journal_entry.store') }}" data-type="POST" enctype="multipart/form-data">
        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">Journal Entry</h3>
            </div>
            <div class="block-content">
                <div class="form-group row">
                    <label class="col-md-2 col-form-label text-left" for="date">Date <span class="text-danger">*</span></label>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="date" name="date" {{ $min_date ? 'min=' . $min_date : '' }}
                            {{ $to_date ? 'max=' . $to_date : '' }}>
                    </div>
                    <div class="col-md-4">
                    </div>
                    <div class="col-md-3">
                        <select class="js-select2 form-control" id="by_journal_setting" name="by_journal_setting" data-placeholder="Select Setting">
                            @foreach ($journal_setting as $item)
                                <option></option>
                                <option value="{{ $item->id }}" data-name="{{ $item->name }}" data-debet_coa="{{ $item->debet_coa }}"
                                    data-debet_note="{{ $item->debet_note }}" data-credit_coa="{{ $item->credit_coa }}"
                                    data-credit_note="{{ $item->credit_note }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-form-label text-left" for="name">Transaction <span class="text-danger">*</span></label>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="name" name="name">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-form-label text-left" for="debet_coa">Debet Entry</label>
                    <div class="col-md-3">
                        <select class="js-select2 form-control" id="debet_coa" name="debet_coa" data-placeholder="Select COA">
                            @foreach ($coa as $item)
                                <option></option>
                                <option value="{{ $item->id }}">{{ $item->code }} / {{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" id="debet_entry" name="debet_entry" placeholder="Nominal">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="debet_note" name="debet_note" placeholder="Note">
                    </div>
                    <div class="col-md-1 text-right">
                        <a href="#" id="debet_add">
                            <button type="button" class="btn bg-gd-sea border-0 text-white">
                                INSERT
                            </button>
                        </a>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-2 col-form-label text-left" for="credit_coa">Credit Entry</label>
                    <div class="col-md-3">
                        <select class="js-select2 form-control" id="credit_coa" name="credit_coa" data-placeholder="Select COA">
                            @foreach ($coa as $item)
                                <option></option>
                                <option value="{{ $item->id }}">{{ $item->code }} / {{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" id="credit_entry" name="credit_entry" placeholder="Nominal">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="credit_note" name="credit_note" placeholder="Note">
                    </div>
                    <div class="col-md-1 text-right">
                        <a href="#" id="credit_add">
                            <button type="button" class="btn bg-gd-sea border-0 text-white">
                                INSERT
                            </button>
                        </a>
                    </div>
                </div>
            </div>
            <div class="block-content">
                <table id="datatable" class="table table-striped table-vcenter table-responsive">
                    <thead>
                        <tr>
                            <th class="text-center">Counter</th>
                            <th class="text-center">Transaction</th>
                            <th class="text-center">Debet</th>
                            <th class="text-center">Credit</th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <div class="form-group row justify-content-end">
                    <label class="col-md-3 col-form-label text-right" for="subtotal">Total</label>
                    <div class="col-md-3 col-form-label text-center">
                        <input type="hidden" class="form-control" id="subtotal_debet" name="subtotal_debet">
                        <span id="subtotal_debet_text">Rp. 0</span>
                    </div>
                    <div class="col-md-3 col-form-label text-center">
                        <input type="hidden" class="form-control" id="subtotal_credit" name="subtotal_credit">
                        <span id="subtotal_credit_text">Rp. 0</span>
                    </div>
                    <div class="col-md-1">
                    </div>
                </div>
            </div>

            <div class="block-content">
                <div class="form-group row pt-30">
                    <div class="col-md-6">
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn bg-gd-corporate border-0 text-white" id="submit-table">
                            Submit <i class="fa fa-arrow-right ml-10"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
    <script src="{{ asset('utility/superuser/js/form.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.js-select2').select2()

            var table = $('#datatable').DataTable({
                paging: false,
                bInfo: false,
                searching: false,
                columns: [{
                        name: 'counter',
                        "visible": false
                    },
                    {
                        name: 'ppb',
                        orderable: false
                    },
                    {
                        name: 'total',
                        orderable: false,
                        searcable: false,
                        width: "25%"
                    },
                    {
                        name: 'paid',
                        orderable: false,
                        searcable: false,
                        width: "25%"
                    },
                    {
                        name: 'action',
                        orderable: false,
                        searcable: false,
                        width: "9%"
                    }
                ],
                'order': [
                    [0, 'desc']
                ]
            })

            var counter = 1;

            $('#debet_add').on('click', function(e) {
                e.preventDefault();
                var debet_coa = $('#debet_coa').select2('data');

                if (debet_coa[0]['id'] && $('#date').val() && $('#name').val() && $('#debet_entry').val()) {
                    var name = $('#name').val();
                    if ($('#debet_note').val()) {
                        name += ' - ' + $('#debet_note').val();
                    }
                    table.row.add([
                        counter,
                        '<input type="hidden" name="date_detail[]" value="' + $('#date').val() +
                        '"><input type="hidden" name="name_detail[]" value="' + name +
                        '"><input type="hidden" name="coa_id[]" value="' + debet_coa[0]['id'] +
                        '"><input type="hidden" name="debet[]" value="' + $('#debet_entry').val() +
                        '"><input type="hidden" name="credit[]" value=""><span>' + name + '</span>',
                        '<span>Rp. ' + Number($('#debet_entry').val()).toLocaleString("id-ID", {
                            maximumFractionDigits: 2
                        }) + '</span>',
                        '',
                        '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                    ]).draw(false);
                    counter++;

                    $('#debet_entry').val('')
                    $('#debet_note').val('')
                    $('#debet_coa').val(null).trigger("change")
                    grandTotal()
                }
            });

            $('#credit_add').on('click', function(e) {
                e.preventDefault();
                var credit_coa = $('#credit_coa').select2('data');

                if (credit_coa[0]['id'] && $('#date').val() && $('#name').val() && $('#credit_entry').val()) {
                    var name = $('#name').val();
                    if ($('#credit_note').val()) {
                        name += ' - ' + $('#credit_note').val();
                    }
                    table.row.add([
                        counter,
                        '<input type="hidden" name="date_detail[]" value="' + $('#date').val() +
                        '"><input type="hidden" name="name_detail[]" value="' + name +
                        '"><input type="hidden" name="coa_id[]" value="' + credit_coa[0]['id'] +
                        '"><input type="hidden" name="debet[]" value=""><input type="hidden" name="credit[]" value="' + $('#credit_entry')
                        .val() + '"><span>' + name + '</span>',
                        '',
                        '<span>Rp. ' + Number($('#credit_entry').val()).toLocaleString("id-ID", {
                            maximumFractionDigits: 2
                        }) + '</span>',
                        '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                    ]).draw(false);
                    counter++;

                    $('#credit_entry').val('')
                    $('#credit_note').val('')
                    $('#credit_coa').val(null).trigger("change")
                    grandTotal()
                }
            });

            $('#datatable tbody').on('click', '.row-delete', function(e) {
                e.preventDefault();
                table.row($(this).parents('tr')).remove().draw();

                grandTotal()
            });

            function grandTotal() {
                var subtotal_debet = 0;
                $('input[name="debet[]"]').each(function() {
                    subtotal_debet += Number($(this).val());
                });
                $('#subtotal_debet').val(subtotal_debet);
                $('#subtotal_debet_text').text('Rp. ' + subtotal_debet.toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                }));

                var subtotal_credit = 0;
                $('input[name="credit[]"]').each(function() {
                    subtotal_credit += Number($(this).val());
                });
                $('#subtotal_credit').val(subtotal_credit);
                $('#subtotal_credit_text').text('Rp. ' + subtotal_credit.toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                }));
            }

            $('#by_journal_setting').on('select2:select', function() {
                $('#name').val($(this).find(':selected').data('name'))
                $('#debet_coa').val($(this).find(':selected').data('debet_coa')).trigger('change')
                $('#debet_note').val($(this).find(':selected').data('debet_note'))
                $('#credit_coa').val($(this).find(':selected').data('credit_coa')).trigger('change')
                $('#credit_note').val($(this).find(':selected').data('credit_note'))
            });
        })
    </script>
@endpush
