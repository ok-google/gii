@extends('superuser.app')

@section('content')
    <nav class="breadcrumb bg-white push">
        <span class="breadcrumb-item">Finance</span>
        <a class="breadcrumb-item" href="{{ route('superuser.finance.journal_setting.index') }}">Journal Setting</a>
        <span class="breadcrumb-item active">Edit</span>
    </nav>
    <div id="alert-block"></div>

    <div class="block">
        <div class="block-header block-header-default">
            <h3 class="block-title">Edit Setting</h3>
        </div>
        <div class="block-content">
            <form class="ajax" data-action="{{ route('superuser.finance.journal_setting.update', $js) }}" data-type="POST" enctype="multipart/form-data">
                <input type="hidden" name="_method" value="PUT">
                <div class="form-group row">
                    <label class="col-md-2 col-form-label text-left" for="name">Transaction <span class="text-danger">*</span></label>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="name" name="name" value="{{ $js->name }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-form-label text-left" for="debet_coa">Debet Entry</label>
                    <div class="col-md-3">
                        <select class="js-select2 form-control" id="debet_coa" name="debet_coa" data-placeholder="Select COA">
                            @foreach ($coa as $item)
                                <option></option>
                                <option value="{{ $item->id }}" {{ $js->debet_coa == $item->id ? 'selected' : '' }}>{{ $item->code }} / {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="debet_note" name="debet_note" placeholder="Note" value="{{ $js->debet_note }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-2 col-form-label text-left" for="credit_coa">Credit Entry</label>
                    <div class="col-md-3">
                        <select class="js-select2 form-control" id="credit_coa" name="credit_coa" data-placeholder="Select COA">
                            @foreach ($coa as $item)
                                <option></option>
                                <option value="{{ $item->id }}" {{ $js->credit_coa == $item->id ? 'selected' : '' }}>{{ $item->code }} /
                                    {{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="credit_note" name="credit_note" placeholder="Note" value="{{ $js->credit_note }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-2 col-form-label text-left" for="status">Status</label>
                    <div class="col-md-3">
                        <select class="js-select2 form-control" id="status" name="status" data-placeholder="Status">
                            @foreach ($status as $key => $value)
                                <option value="{{ $value }}" {{ $js->status == $value ? 'selected' : '' }}>{{ $key }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row pt-30">
                    <div class="col-md-6">
                        <a href="javascript:history.back()">
                            <button type="button" class="btn bg-gd-cherry border-0 text-white">
                                <i class="fa fa-arrow-left mr-10"></i> Back
                            </button>
                        </a>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn bg-gd-corporate border-0 text-white">
                            Submit <i class="fa fa-arrow-right ml-10"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@include('superuser.asset.plugin.select2')

@push('scripts')
    <script src="{{ asset('utility/superuser/js/form.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.js-select2').select2()

        })
    </script>
@endpush
