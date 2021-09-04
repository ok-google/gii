@extends('superuser.app')

@section('content')

<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.accounting.setting_profit_loss.store') }}" data-type="POST" enctype="multipart/form-data">
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Setting Profit Loss</h3>
    </div>
    
    <div class="block-content">
      <div class="form-group row">
        <div class="col-md-12">
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right" for="act_from">Active</label>
            <div class="col-md-3">
              <input type="date" class="form-control" name="act_from" value="{{ $act_from }}">
            </div>
            <label class="col-md-1 col-form-label text-right" for="act_to">to</label>
            <div class="col-md-3">
              <input type="date" class="form-control" name="act_to" value="{{ $act_to }}">
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-3">
              <h3 class="block-title">Pendapatan Penjualan</h3>
            </div>
            <div class="col-md-9">
              <hr style="height: 1px;background-color: #333;">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right" for="a">COA Penjualan</label>
            <div class="col-md-4">
              <select class="js-select2 form-control" id="a" name="a" data-placeholder="Select COA">
                <option></option>
                @foreach($coa as $item)
                  <option value="{{ $item->id }}" {{ $a == $item->id ? 'selected' : ''}}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Faktor Pengurang:</label>
          </div>

          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right" for="b">Select COA</label>
            <div class="col-md-6">
              <div class="form-group row">
                <div id="b" class="col-md-12">
                  @if ($b)
                    @foreach ($b as $value)
                    <div class="form-group row">
                      <div class="col-md-8">
                        <select class="js-select2 form-control col-md-12" name="b[]" data-placeholder="Select COA">
                          <option></option>
                          @foreach($coa as $item)
                            <option value="{{ $item->id }}" {{ $value == $item->id ? 'selected' : ''}}>{{ $item->name }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-1 text-right">
                        <a href="#" class="remove">
                          <button type="button" class="btn bg-gd-cherry border-0 text-white">
                            <i class="fa fa-close"></i>
                          </button>
                        </a>
                      </div>
                    </div>
                    @endforeach
                  @endif
                </div>
              </div>
              <div class="form-group row">
                <div class="col-md-8 text-right">
                  <a href="#" id="b-add">
                    <button type="button" class="btn bg-gd-sea border-0 text-white">
                      <i class="fa fa-plus mr-10"></i> ADD
                    </button>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-3">
              <h3 class="block-title">Harga Pokok Penjualan</h3>
            </div>
            <div class="col-md-9">
              <hr style="height: 1px;background-color: #333;">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right" for="c">COA Persediaan</label>
            <div class="col-md-4">
              <select class="js-select2 form-control" id="c" name="c" data-placeholder="Select COA">
                <option></option>
                @foreach($coa as $item)
                  <option value="{{ $item->id }}" {{ $c == $item->id ? 'selected' : ''}}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Faktor Penambah:</label>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right" for="d">Select COA</label>
            <div class="col-md-6">
              <div class="form-group row">
                <div id="d" class="col-md-12">
                  @if ($d)
                    @foreach ($d as $value)
                    <div class="form-group row">
                      <div class="col-md-8">
                        <select class="js-select2 form-control col-md-12" name="d[]" data-placeholder="Select COA">
                          <option></option>
                          @foreach($coa as $item)
                            <option value="{{ $item->id }}" {{ $value == $item->id ? 'selected' : ''}}>{{ $item->name }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-1 text-right">
                        <a href="#" class="remove">
                          <button type="button" class="btn bg-gd-cherry border-0 text-white">
                            <i class="fa fa-close"></i>
                          </button>
                        </a>
                      </div>
                    </div>
                    @endforeach
                  @endif
                </div>
              </div>
              <div class="form-group row">
                <div class="col-md-8 text-right">
                  <a href="#" id="d-add">
                    <button type="button" class="btn bg-gd-sea border-0 text-white">
                      <i class="fa fa-plus mr-10"></i> ADD
                    </button>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-3">
              <h3 class="block-title">Beban Operasional</h3>
            </div>
            <div class="col-md-9">
              <hr style="height: 1px;background-color: #333;">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Beban Penjualan</label>
          </div>

          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right" for="e">Select COA</label>
            <div class="col-md-6">
              <div class="form-group row">
                <div id="e" class="col-md-12">
                  @if ($e)
                    @foreach ($e as $value)
                    <div class="form-group row">
                      <div class="col-md-8">
                        <select class="js-select2 form-control col-md-12" name="e[]" data-placeholder="Select COA">
                          <option></option>
                          @foreach($coa as $item)
                            <option value="{{ $item->id }}" {{ $value == $item->id ? 'selected' : ''}}>{{ $item->name }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-1 text-right">
                        <a href="#" class="remove">
                          <button type="button" class="btn bg-gd-cherry border-0 text-white">
                            <i class="fa fa-close"></i>
                          </button>
                        </a>
                      </div>
                    </div>
                    @endforeach
                  @endif
                </div>
              </div>
              <div class="form-group row">
                <div class="col-md-8 text-right">
                  <a href="#" id="e-add">
                    <button type="button" class="btn bg-gd-sea border-0 text-white">
                      <i class="fa fa-plus mr-10"></i> ADD
                    </button>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Beban Administrasi</label>
          </div>

          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right" for="f">Select COA</label>
            <div class="col-md-6">
              <div class="form-group row">
                <div id="f" class="col-md-12">
                  @if ($f)
                    @foreach ($f as $value)
                    <div class="form-group row">
                      <div class="col-md-8">
                        <select class="js-select2 form-control col-md-12" name="f[]" data-placeholder="Select COA">
                          <option></option>
                          @foreach($coa as $item)
                            <option value="{{ $item->id }}" {{ $value == $item->id ? 'selected' : ''}}>{{ $item->name }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-1 text-right">
                        <a href="#" class="remove">
                          <button type="button" class="btn bg-gd-cherry border-0 text-white">
                            <i class="fa fa-close"></i>
                          </button>
                        </a>
                      </div>
                    </div>
                    @endforeach
                  @endif
                </div>
              </div>
              <div class="form-group row">
                <div class="col-md-8 text-right">
                  <a href="#" id="f-add">
                    <button type="button" class="btn bg-gd-sea border-0 text-white">
                      <i class="fa fa-plus mr-10"></i> ADD
                    </button>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-3">
              <h3 class="block-title">Pendapatan dan Keuntungan Lain</h3>
            </div>
            <div class="col-md-9">
              <hr style="height: 1px;background-color: #333;">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right" for="g">Select COA</label>
            <div class="col-md-6">
              <div class="form-group row">
                <div id="g" class="col-md-12">
                  @if ($g)
                    @foreach ($g as $value)
                    <div class="form-group row">
                      <div class="col-md-8">
                        <select class="js-select2 form-control col-md-12" name="g[]" data-placeholder="Select COA">
                          <option></option>
                          @foreach($coa as $item)
                            <option value="{{ $item->id }}" {{ $value == $item->id ? 'selected' : ''}}>{{ $item->name }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-1 text-right">
                        <a href="#" class="remove">
                          <button type="button" class="btn bg-gd-cherry border-0 text-white">
                            <i class="fa fa-close"></i>
                          </button>
                        </a>
                      </div>
                    </div>
                    @endforeach
                  @endif
                </div>
              </div>
              <div class="form-group row">
                <div class="col-md-8 text-right">
                  <a href="#" id="g-add">
                    <button type="button" class="btn bg-gd-sea border-0 text-white">
                      <i class="fa fa-plus mr-10"></i> ADD
                    </button>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-3">
              <h3 class="block-title">Beban dan Kerugian Lain</h3>
            </div>
            <div class="col-md-9">
              <hr style="height: 1px;background-color: #333;">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right" for="h">Select COA</label>
            <div class="col-md-6">
              <div class="form-group row">
                <div id="h" class="col-md-12">
                  @if ($h)
                    @foreach ($h as $value)
                    <div class="form-group row">
                      <div class="col-md-8">
                        <select class="js-select2 form-control col-md-12" name="h[]" data-placeholder="Select COA">
                          <option></option>
                          @foreach($coa as $item)
                            <option value="{{ $item->id }}" {{ $value == $item->id ? 'selected' : ''}}>{{ $item->name }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-1 text-right">
                        <a href="#" class="remove">
                          <button type="button" class="btn bg-gd-cherry border-0 text-white">
                            <i class="fa fa-close"></i>
                          </button>
                        </a>
                      </div>
                    </div>
                    @endforeach
                  @endif
                </div>
              </div>
              <div class="form-group row">
                <div class="col-md-8 text-right">
                  <a href="#" id="h-add">
                    <button type="button" class="btn bg-gd-sea border-0 text-white">
                      <i class="fa fa-plus mr-10"></i> ADD
                    </button>
                  </a>
                </div>
              </div>
            </div>
          </div>


        </div>
      </div>
    </div>

    <div class="block-content">
      <div class="form-group row pt-30">
        <div class="col-md-6 text-right">
          <button type="submit" class="btn bg-gd-corporate border-0 text-white" id="submit-table">
            Save Setting <i class="fa fa-arrow-right ml-10"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection

@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2()

    var coas = <?php echo json_encode($coa); ?>;
    
    var makeOption = '';
    $.map( coas, function( val, i ) {
      makeOption += '<option value="'+ val['id'] +'">'+ val['name'] +'</option>';
    });
    
    $('#b-add').on( 'click', function (e) {
      e.preventDefault();
      var makeSelect = '<div class="form-group row"><div class="col-md-8"><select class="js-select2 form-control" name="b[]" data-placeholder="Select COA">';
        makeSelect += '<option></option>';
        makeSelect += makeOption;
        makeSelect += '</select></div><div class="col-md-1 text-right"><a href="#" class="remove"><button type="button" class="btn bg-gd-cherry border-0 text-white"><i class="fa fa-close"></i></button></a></div></div>';
      $('#b').append(makeSelect);
      $(".js-select2").select2();
    });

    $('#d-add').on( 'click', function (e) {
      e.preventDefault();
      var makeSelect = '<div class="form-group row"><div class="col-md-8"><select class="js-select2 form-control" name="d[]" data-placeholder="Select COA">';
        makeSelect += '<option></option>';
        makeSelect += makeOption;
        makeSelect += '</select></div><div class="col-md-1 text-right"><a href="#" class="remove"><button type="button" class="btn bg-gd-cherry border-0 text-white"><i class="fa fa-close"></i></button></a></div></div>';
      $('#d').append(makeSelect);
      $(".js-select2").select2();
    });

    $('#e-add').on( 'click', function (e) {
      e.preventDefault();
      var makeSelect = '<div class="form-group row"><div class="col-md-8"><select class="js-select2 form-control" name="e[]" data-placeholder="Select COA">';
        makeSelect += '<option></option>';
        makeSelect += makeOption;
        makeSelect += '</select></div><div class="col-md-1 text-right"><a href="#" class="remove"><button type="button" class="btn bg-gd-cherry border-0 text-white"><i class="fa fa-close"></i></button></a></div></div>';
      $('#e').append(makeSelect);
      $(".js-select2").select2();
    });

    $('#f-add').on( 'click', function (e) {
      e.preventDefault();
      var makeSelect = '<div class="form-group row"><div class="col-md-8"><select class="js-select2 form-control" name="f[]" data-placeholder="Select COA">';
        makeSelect += '<option></option>';
        makeSelect += makeOption;
        makeSelect += '</select></div><div class="col-md-1 text-right"><a href="#" class="remove"><button type="button" class="btn bg-gd-cherry border-0 text-white"><i class="fa fa-close"></i></button></a></div></div>';
      $('#f').append(makeSelect);
      $(".js-select2").select2();
    });

    $('#g-add').on( 'click', function (e) {
      e.preventDefault();
      var makeSelect = '<div class="form-group row"><div class="col-md-8"><select class="js-select2 form-control" name="g[]" data-placeholder="Select COA">';
        makeSelect += '<option></option>';
        makeSelect += makeOption;
        makeSelect += '</select></div><div class="col-md-1 text-right"><a href="#" class="remove"><button type="button" class="btn bg-gd-cherry border-0 text-white"><i class="fa fa-close"></i></button></a></div></div>';
      $('#g').append(makeSelect);
      $(".js-select2").select2();
    });

    $('#h-add').on( 'click', function (e) {
      e.preventDefault();
      var makeSelect = '<div class="form-group row"><div class="col-md-8"><select class="js-select2 form-control" name="h[]" data-placeholder="Select COA">';
        makeSelect += '<option></option>';
        makeSelect += makeOption;
        makeSelect += '</select></div><div class="col-md-1 text-right"><a href="#" class="remove"><button type="button" class="btn bg-gd-cherry border-0 text-white"><i class="fa fa-close"></i></button></a></div></div>';
      $('#h').append(makeSelect);
      $(".js-select2").select2();
    });

    $('form').on( 'click', '.remove', function (e) {
      e.preventDefault();
      $(this).closest('.form-group').remove();
    });
  })
</script>
@endpush
