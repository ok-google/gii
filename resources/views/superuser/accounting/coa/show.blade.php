@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Accounting</span>
  <a class="breadcrumb-item" href="{{ route('superuser.accounting.coa.index') }}">Show COA</a>
  <span class="breadcrumb-item active">Show</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Master COA</h3>
  </div>
  <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code</label>
        <div class="col-md-4">
          <div class="form-control-plaintext">{{ $coa->code }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Chart of Account</label>
        <div class="col-md-4">
          <div class="form-control-plaintext">{{ $coa->name }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="kode_pelunasan">Kode Pelunasan</label>
        <div class="col-md-4">
          <div class="form-control-plaintext">{{ $coa->kode_pelunasan ?? '' }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="group">Group</label>
        <div class="col-md-4">
          <div class="form-control-plaintext">{{ $coa->group() }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Select Parent Lv 1</label>
        <div class="col-md-4">
          <div class="form-control-plaintext">{{ $coa->parent_level_one->name ?? '' }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Select Parent Lv 2</label>
        <div class="col-md-4">
          <div class="form-control-plaintext">{{ $coa->parent_level_two->name ?? '' }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Select Parent Lv 3</label>
        <div class="col-md-4">
          <div class="form-control-plaintext">{{ $coa->parent_level_three->name ?? ''}}</div>
        </div>
      </div>
      
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.accounting.coa.index') }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
      </div>
  </div>
</div>
@endsection

