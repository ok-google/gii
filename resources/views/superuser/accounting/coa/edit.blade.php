@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Accounting</span>
  <a class="breadcrumb-item" href="{{ route('superuser.accounting.coa.index') }}">Edit COA</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Master COA</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.accounting.coa.update', $coa->id) }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="_method" value="PUT">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-1">
          <input type="text" class="form-control" id="code_column_1" name="code_column_1" onkeyup="nospaces(this)" value="{{ $pecah_code[0] }}" maxlength="2">
        </div>
        <div class="col-md-1">
          <input type="text" class="form-control" id="code_column_2" name="code_column_2" onkeyup="nospaces(this)" value="{{ $pecah_code[1] }}" maxlength="2">
        </div>
        <div class="col-md-1">
          <input type="text" class="form-control" id="code_column_3" name="code_column_3" onkeyup="nospaces(this)" value="{{ $pecah_code[2] }}" maxlength="2">
        </div>
        <div class="col-md-1">
          <input type="text" class="form-control" id="code_column_4" name="code_column_4" onkeyup="nospaces(this)" value="{{ $pecah_code[3] }}" maxlength="4">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Chart of Account <span class="text-danger">*</span></label>
        <div class="col-md-4">
          <input type="text" class="form-control" id="name" name="name" value="{{ $coa->name }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="kode_pelunasan">Kode Pelunasan</label>
        <div class="col-md-4">
          <input type="text" class="form-control" id="kode_pelunasan" name="kode_pelunasan" value="{{ $coa->kode_pelunasan }}">
          <small class="form-text text-muted font-italic">ex : PDM/SH/</small>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="group">Group <span class="text-danger">*</span></label>
        <div class="col-md-4">
          <select class="js-select2 form-control" id="group" name="group" data-placeholder="Select Group">
            <option></option>
            @foreach(\App\Entities\Accounting\Coa::GROUP as $group => $group_value)
            <option value="{{ $group_value }}" {{ $group_value == $coa->group ? 'selected': '' }}>{{ $group }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Select Parent Lv 1</label>
        <div class="col-md-4">
          <select class="js-select2 form-control" id="parent_level_1" name="parent_level_1" data-placeholder="Select Parent Lv 1" data-value="{{ $coa->parent_level_1 }}">
            <option></option>
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Select Parent Lv 2</label>
        <div class="col-md-4">
          <select class="js-select2 form-control" id="parent_level_2" name="parent_level_2" data-placeholder="Select Parent Lv 2" data-value="{{ $coa->parent_level_2 }}">
            <option></option>
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Select Parent Lv 3</label>
        <div class="col-md-4">
          <select class="js-select2 form-control" id="parent_level_3" name="parent_level_3" data-placeholder="Select Parent Lv 3" data-value="{{ $coa->parent_level_3 }}">
            <option></option>
          </select>
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
  $(document).ready(function () {
    var parent_level_1 = '';
    $('.js-select2').select2()

    function addLoadSpiner(el) {
    if (el.length > 0) {
      if ($("#img_" + el[0].id).length > 0) {
        $("#img_" + el[0].id).css('display', 'block');
      }               
      else {
        var img = $('<img class="ddloading">');
        img.attr('id', "img_" + el[0].id);
        img.attr('src', 'http://ajaxloadingimages.net/gif/image?imageid=aero-spinner&forecolor=000000&backcolor=ffffff&transparent=true');
        img.css({ 'display': 'inline-block', 'width': '25px', 'height': '25px', 'position': 'absolute', 'left': '50%', 'margin-top': '5px' });
        img.prependTo(el[0].nextElementSibling);
      }
      el.prop("disabled", true);               
    }
  }

  function hideLoadSpinner(el) {
    if (el.length > 0) {
      if ($("#img_" + el[0].id).length > 0) {
        setTimeout(function () {
          $("#img_" + el[0].id).css('display', 'none');
          el.prop("disabled", false);
        }, 500);                  
      }
    }
  }

  $.ajax({
    url: '{{ route('superuser.accounting.coa.select_parent_level_1') }}',
    type: 'GET',
    dataType: 'json',
    beforeSend: function () {
      addLoadSpiner($('#parent_level_1'))
    },
    complete: function () {
      hideLoadSpinner($('#parent_level_1'))
    },
    success: function(json) {
      if (json.code == 200) {
        console.log(json.data);
        for (i = 0; i < Object.keys(json.data).length; i++) {
          let newOption = new Option(json.data[i].name, json.data[i].id, false, false);
          $('#parent_level_1').append(newOption).trigger('change');
        }

        let data_val = $('#parent_level_1').data('value')
        if (data_val > 0) {
          $('#parent_level_1').val(data_val);
          $('#parent_level_1').trigger('select2:select');
        }
      }
    }
  });

  $("#parent_level_1").on('select2:select', function() {
    parent_level_1 = $("#parent_level_1").val();

    $.ajax({
      url: '{{ route('superuser.accounting.coa.select_parent_level_2') }}',
      data: "id_parent_level_1=" + parent_level_1,
      type: 'GET',
      cache: false,
      dataType: 'json',
      beforeSend: function () {
        addLoadSpiner($('#parent_level_2'))
      },
      complete: function () {
        hideLoadSpinner($('#parent_level_2'))
      },
      success: function(json) {
        $('#parent_level_2').empty().trigger('change');
        $('#parent_level_3').empty().trigger('change');
        
        if (json.code == 200) {
          console.log(json.data);
          let ph = new Option('', '', false, false);
          $('#parent_level_2').append(ph).trigger('change');

          for (i = 0; i < Object.keys(json.data).length; i++) {
            let newOption = new Option(json.data[i].name, json.data[i].id, false, false);
            $('#parent_level_2').append(newOption).trigger('change');
          }

          let data_val = $('#parent_level_2').data('value')
          if (data_val > 0) {
            $('#parent_level_2').val(data_val);
            $('#parent_level_2').trigger('select2:select');
          }
        }
      }
    });
  });

  $("#parent_level_2").on('select2:select', function() {
    var parent_level_2 = $("#parent_level_2").val();

    $.ajax({
      url: '{{ route('superuser.accounting.coa.select_parent_level_3') }}',
      data: "id_parent_level_2=" + parent_level_2 + "&id_parent_level_1=" + parent_level_1,
      type: 'GET',
      cache: false,
      dataType: 'json',
      beforeSend: function () {
        addLoadSpiner($('#parent_level_3'))
      },
      complete: function () {
        hideLoadSpinner($('#parent_level_3'))
      },
      success: function(json) {
        $('#parent_level_3').empty().trigger('change');

        if (json.code == 200) {
          console.log(json.data);
          let ph = new Option('', '', false, false);
          $('#parent_level_3').append(ph).trigger('change');

          for (i = 0; i < Object.keys(json.data).length; i++) {
            let newOption = new Option(json.data[i].name, json.data[i].id, false, false);
            $('#parent_level_3').append(newOption).trigger('change');
          }

          let data_val = $('#parent_level_3').data('value')
          if (data_val > 0) {
            $('#parent_level_3').val(data_val);
            $('#parent_level_3').trigger('select2:select');
          }
        }
      }
    });
  });

  })
</script>
@endpush
