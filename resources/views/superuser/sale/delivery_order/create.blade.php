@extends('superuser.app')

@section('content')
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Sale</span>
    <a class="breadcrumb-item" href="{{ route('superuser.sale.delivery_order.index') }}">Packing</a>
    <span class="breadcrumb-item active">Create</span>
  </nav>
  <div id="alert-block"></div>
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Create Packing</h3>
    </div>
    <div class="block-content">
      <form class="ajax" data-action="{{ route('superuser.sale.delivery_order.store') }}" data-type="POST"
        enctype="multipart/form-data">
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="code">Packing Number <span
              class="text-danger">*</span></label>
          <div class="col-md-7">
            <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)"
              value="{{ App\Repositories\DeliveryOrderRepo::generateCode() }}" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse <span
              class="text-danger">*</span></label>
          <div class="col-md-7">
            <select class="js-select2 form-control" id="warehouse" name="warehouse" data-placeholder="Select Warehouse">
              <option></option>
              @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="store_name">Store <span
              class="text-danger">*</span></label>
          <div class="col-md-7">
            <select class="js-select2 form-control" id="store_name" name="store_name" data-placeholder="Select Store">
              <option></option>
            </select>
            <div class="form-control-plaintext"><i id="remaining"></i></div>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="ekspedisi_marketplace">Ekspedisi <span
              class="text-danger">*</span></label>
          <div class="col-md-7">
            <select class="js-select2 form-control" id="ekspedisi_marketplace" name="ekspedisi_marketplace" data-placeholder="Select Ekspedisi">
              <option></option>
            </select>
            <div class="form-control-plaintext"><i id="remaining"></i></div>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="order_count">Order Count <span
              class="text-danger">*</span></label>
          <div class="col-md-2">
            <input type="number" class="form-control" id="order_count" name="order_count">
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
    function addLoadSpiner(el) {
      if (el.length > 0) {
        if ($("#img_" + el[0].id).length > 0) {
          $("#img_" + el[0].id).css('display', 'block');
        } else {
          var img = $('<img class="ddloading">');
          img.attr('id', "img_" + el[0].id);
          img.attr('src',
            'http://ajaxloadingimages.net/gif/image?imageid=aero-spinner&forecolor=000000&backcolor=ffffff&transparent=true'
          );
          img.css({
            'display': 'inline-block',
            'width': '25px',
            'height': '25px',
            'position': 'absolute',
            'left': '50%',
            'margin-top': '5px'
          });
          img.prependTo(el[0].nextElementSibling);
        }
        el.prop("disabled", true);
      }
    }

    function hideLoadSpinner(el) {
      if (el.length > 0) {
        if ($("#img_" + el[0].id).length > 0) {
          setTimeout(function() {
            $("#img_" + el[0].id).css('display', 'none');
            el.prop("disabled", false);
          }, 500);
        }
      }
    }

    $(document).ready(function() {
      $('.js-select2').select2()

      $('#warehouse').on('select2:select', function(e) {
        $.ajax({
          url: '{{ route('superuser.sale.delivery_order.get_store') }}',
          data: {
            id: $(this).val(),
            _token: "{{ csrf_token() }}"
          },
          type: 'POST',
          cache: false,
          dataType: 'json',
          beforeSend: function() {
            addLoadSpiner($('#store_name'))
          },
          complete: function() {
            hideLoadSpinner($('#store_name'))
          },
          success: function(json) {
            $('#store_name').empty().trigger('change');
            $('#remaining').text('');

            if (json.code == 200) {
              let ph = new Option('', '', false, false);
              $('#store_name').append(ph).trigger('change');

              for (i = 0; i < Object.keys(json.data).length; i++) {
                let newOption = '<option value="' + json.data[i].store_name + '" data-total="' + json.data[
                  i].total + '">' + json.data[i].store_name + '</option>';
                $('#store_name').append(newOption).trigger('change');
              }
            }
          }
        });
      });

      $('#store_name').on('select2:select', function(e) {
        let total = $(e.params.data.element).data('total');
        $('#remaining').text(total + " remaining");
      });

    })

  </script>
@endpush
