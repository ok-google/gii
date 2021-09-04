@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.index') }}">Customer</a>
  <span class="breadcrumb-item active">Show</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Customer</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Name</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Store</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->store }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Image</label>
      <div class="col-md-7">
        <a href="{{ $customer->img_store }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
          <img src="{{ $customer->img_store }}" class="img-fluid img-show-small">
        </a>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Address</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->address }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Type</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">
          <a href="{{ route('superuser.master.customer_type.show', $customer->type->id) }}">
            {{ $customer->type->name }}
          </a>
        </div>
      </div>
    </div>
    @if( \App\Entities\Master\CustomerType::where('id', $customer->type->id)->first()->grosir_address )
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Address</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">
          {{ $customer->address_do }}
        </div>
      </div>
    </div>
    @endif
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Category</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">
          <a href="{{ route('superuser.master.customer_category.show', $customer->category->id) }}">
            {{ $customer->category->name }}
          </a>
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Provinsi</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->text_provinsi }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kota</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->text_kota }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kecamatan</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->text_kecamatan }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kelurahan</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->text_kelurahan }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Zipcode</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->zipcode }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Email</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->email }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Phone</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->phone }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Fax</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->fax }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Website</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->website }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Owner Name</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->owner_name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Plafon Piutang</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ rupiah($customer->plafon_piutang) }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->status() }}</div>
      </div>
    </div>
    <hr>
    <div class="block-header">
      <h3 class="block-title text-center">COA Setting</h3>
    </div>
    <div class="row">
      <div class="col-md-3"></div>
      <label class="col-md-3 col-form-label text-center">Debet</label>
      <div class="col-md-1"></div>
      <label class="col-md-3 col-form-label text-center">Credit</label>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">HEAD OFFICE</label>
      <div class="col-md-3">
        @foreach ($coa_head_office as $item)
          @if ($item->id == $coa_head_office_selected)
            <div class="form-control-plaintext">{{ $item->code }} / {{ $item->name }}</div>
            @break
          @endif
        @endforeach
      </div>
      <div class="col-md-1"></div>
      <div class="col-md-3">
        @foreach ($coa_head_office as $item)
          @if ($item->id == $coa_penjualan_head_office_selected)
            <div class="form-control-plaintext">{{ $item->code }} / {{ $item->name }}</div>
            @break
          @endif
        @endforeach
      </div>
    </div>
    @foreach ($branches as $branch)
    <div class="row">
      <label class="col-md-3 col-form-label text-right">{{ $branch->name }}</label>
      <div class="col-md-3">
        @foreach ($coa_branch[$branch->id] as $item)
          @if ($item->id == $coa_branch_selected[$branch->id])
            <div class="form-control-plaintext">{{ $item->code }} / {{ $item->name }}</div>
            @break
          @endif
        @endforeach
      </div>
      <div class="col-md-1"></div>
      <div class="col-md-3">
        @foreach ($coa_branch[$branch->id] as $item)
          @if ($item->id == $coa_penjualan_branch_selected[$branch->id])
            <div class="form-control-plaintext">{{ $item->code }} / {{ $item->name }}</div>
            @break
          @endif
        @endforeach
      </div>
    </div>
    @endforeach
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="javascript:history.back()">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      @if($customer->status != $customer::STATUS['DELETED'])
      <div class="col-md-6 text-right">
        <a href="javascript:deleteConfirmation('{{ route('superuser.master.customer.destroy', $customer->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.master.customer.edit', $customer->id) }}">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
      </div>
      @endif
    </div>
  </div>
</div>

{{-- <div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Other Address ({{ $customer->other_addresses->count() }})</h3>

    <a href="{{ route('superuser.master.customer.other_address.create', [$customer->id]) }}">
      <button type="button" class="btn btn-outline-primary min-width-125 pull-right">Create</button>
    </a>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped table-vcenter table-responsive js-table-sections table-hover">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center" style="width: 30%">Label</th>
          <th style="width: 30%"></th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      @foreach($customer->other_addresses as $other_address)
      <tbody class="js-table-sections-header {{ $other_address->status == $other_address::STATUS['DELETED'] ? 'table-danger' : '' }}">
        <tr>
          <td class="text-center">{{ $loop->iteration }}</td>
          <td class="text-center">{{ $other_address->label }}</td>
          <td></td>
          <td class="text-center">
            @if ($other_address->status != $other_address::STATUS['DELETED'])
            <a href="{{ route('superuser.master.customer.other_address.edit', [$customer->id, $other_address->id]) }}">
              <button type="button" class="btn btn-sm btn-circle btn-alt-warning" title="Edit">
                <i class="fa fa-pencil"></i>
              </button>
            </a>
            <a href="javascript:deleteConfirmation('{{ route('superuser.master.customer.other_address.destroy', [$customer->id, $other_address->id]) }}')">
              <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                  <i class="fa fa-times"></i>
              </button>
            </a>
            @endif
          </td>
        </tr>
      </tbody>
      <tbody>
        @if($other_address->phone)
        <tr>
          <td class="text-right" colspan="2">Phone</td>
          <td class="text-left" colspan="2">{{ $other_address->phone }}</td>
        </tr>
        @endif
        @if($other_address->contact_person)
        <tr>
          <td class="text-right" colspan="2">Contact Person</td>
          <td class="text-left" colspan="2">{{ $other_address->contact_person }}</td>
        </tr>
        @endif
        @if($other_address->address)
        <tr>
          <td class="text-right" colspan="2">Address</td>
          <td class="text-left" colspan="2">{{ $other_address->address }}</td>
        </tr>
        @endif
        @if($other_address->gps_latitude OR $other_address->gps_longitude)
        <tr>
          <td class="text-right" colspan="2">GPS Coordinate</td>
          <td class="text-left" colspan="2">(Latitude) {{ $other_address->gps_latitude }} - (Longitude) {{ $other_address->gps_longitude }}</td>
        </tr>
        @endif
        @if($other_address->text_provinsi)
        <tr>
          <td class="text-right" colspan="2">Provinsi</td>
          <td class="text-left" colspan="2">{{ $other_address->text_provinsi }}</td>
        </tr>
        @endif
        @if($other_address->text_kota)
        <tr>
          <td class="text-right" colspan="2">Kota</td>
          <td class="text-left" colspan="2">{{ $other_address->text_kota }}</td>
        </tr>
        @endif
        @if($other_address->text_kecamatan)
        <tr>
          <td class="text-right" colspan="2">Kecamatan</td>
          <td class="text-left" colspan="2">{{ $other_address->text_kecamatan }}</td>
        </tr>
        @endif
        @if($other_address->text_kelurahan)
        <tr>
          <td class="text-right" colspan="2">Kelurahan</td>
          <td class="text-left" colspan="2">{{ $other_address->text_kelurahan }}</td>
        </tr>
        @endif
        @if($other_address->zipcode)
        <tr>
          <td class="text-right" colspan="2">Zipcode</td>
          <td class="text-left" colspan="2">{{ $other_address->zipcode }}</td>
        </tr>
        @endif
        <tr>
          <td class="text-right" colspan="2">Status</td>
          <td class="text-left" colspan="2">{{ $other_address->status() }}</td>
        </tr>
      </tbody>
      @endforeach
    </table>
  </div>
</div> --}}
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable').DataTable({
      columnDefs: [
        { orderable: false, targets: [3] }
      ]
    })

    $('a.img-lightbox').magnificPopup({
      type: 'image',
      closeOnContentClick: true,
    });

    Codebase.helpers('table-tools')
  })
</script>
@endpush
