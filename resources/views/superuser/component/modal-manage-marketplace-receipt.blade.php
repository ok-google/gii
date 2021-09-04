<div class="modal fade" id="modal-manage-mr" tabindex="-1" role="dialog" aria-labelledby="modal-manage" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="block block-themed block-transparent mb-0">
        <div class="block-header bg-primary-dark">
          <h3 class="block-title">Transaction Acknowledge</h3>
          <div class="block-options">
            <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
              <i class="si si-close"></i>
            </button>
          </div>
        </div>
        <div class="block-content pb-20">
          <div class="row">
            <div class="col-md-12">
              <form class="ajax" data-action="{{ route('superuser.finance.marketplace_receipt.store') }}" data-type="POST" enctype="multipart/form-data">
                <span class="font-size-h5">Debet Transaction</span>
                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-center">Transaction</label>
                  <label class="col-md-4 col-form-label text-center">Total</label>
                  <label class="col-md-5 col-form-label text-center">COA Select</label>
                </div>
                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-center">Payment</label>
                  <label class="col-md-4 col-form-label text-center" id="payment_modal"></label>
                  <div class="col-md-5">
                    <select class="js-select2 form-control" id="coa_payment" name="coa_payment" data-placeholder="Select COA" required>
                      <option></option>
                      @foreach($coas as $coa)
                        <option value="{{ $coa->id }}">{{ $coa->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-center">Cost 1</label>
                  <label class="col-md-4 col-form-label text-center" id="cost_1_modal"></label>
                  <div class="col-md-5">
                    <select class="js-select2 form-control" id="coa_cost_1" name="coa_cost_1" data-placeholder="Select COA" required>
                      <option></option>
                      @foreach($coas as $coa)
                        <option value="{{ $coa->id }}">{{ $coa->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-center">Cost 2</label>
                  <label class="col-md-4 col-form-label text-center" id="cost_2_modal"></label>
                  <div class="col-md-5">
                    <select class="js-select2 form-control" id="coa_cost_2" name="coa_cost_2" data-placeholder="Select COA" required>
                      <option></option>
                      @foreach($coas as $coa)
                        <option value="{{ $coa->id }}">{{ $coa->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-center">Cost 3</label>
                  <label class="col-md-4 col-form-label text-center" id="cost_3_modal"></label>
                  <div class="col-md-5">
                    <select class="js-select2 form-control" id="coa_cost_3" name="coa_cost_3" data-placeholder="Select COA" required>
                      <option></option>
                      @foreach($coas as $coa)
                        <option value="{{ $coa->id }}">{{ $coa->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <hr>
                <span class="font-size-h5">Credit Transaction</span>
                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-center">Transaction</label>
                  <label class="col-md-4 col-form-label text-center">Total</label>
                  <label class="col-md-5 col-form-label text-center">COA Select</label>
                </div>
                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-center">Account Receivable</label>
                  <label class="col-md-4 col-form-label text-center" id="total_modal"></label>
                  <div class="col-md-5">
                    <select class="js-select2 form-control" id="coa_total" name="coa_total" data-placeholder="Select COA" required>
                      <option></option>
                      @foreach($coas as $coa)
                        <option value="{{ $coa->id }}">{{ $coa->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <button type="submit" class="btn mt-10 w-100 btn-alt-primary">Process</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@include('superuser.asset.plugin.select2')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('.js-select2').select2()
    $('.select2-container').css("width","100%")
  });
</script>
@endpush