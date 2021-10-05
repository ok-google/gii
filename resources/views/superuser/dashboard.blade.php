@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item active">Dashboard</span>
</nav>

<div class="row gutters-tiny">
   <div class="col-4">
    <a class="block" href="javascript:void(0)">
      {{-- <div class="block-content block-content-full">
        <div class="row">
          <div class="col-6">
            <i class="fa fa-dollar fa-2x text-body-bg-dark"></i>
          </div>
          <div class="col-6 text-right">
            <span class="text-muted">{{ Swap::latest('USD/IDR')->getDate()->format('d M Y H:i:s') }}</span>
          </div>
        </div>
        <div class="row">
          <div class="col-6 text-right border-r">
            <div class="font-size-h3 font-w600">USD</div>
            <div class="font-size-h4 font-w600"><i class="fa fa-dollar"></i>1</div>
          </div>
          <div class="col-6">
            <div class="font-size-h3 font-w600">IDR</div>
            <div class="font-size-h4 font-w600">{{ rupiah(Swap::latest('USD/IDR', ['cache_ttl' => \Carbon\Carbon::now()->secondsUntilEndOfDay()])->getValue()) }}</div>
          </div>
        </div>
      </div> --}}
    </a>
  </div> 
</div>
@endsection