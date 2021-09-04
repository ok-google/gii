<script type="text/javascript">
  var start_date;
  var end_date;
  var DateFilterFunction = (function(oSettings, aData, iDataIndex) {
    var dateStart = parseDateValue(start_date);
    var dateEnd = parseDateValue(end_date);
    //value column filter
    var evalDate = parseDateValue(aData[5]);
    if ((isNaN(dateStart) && isNaN(dateEnd)) ||
      (isNaN(dateStart) && evalDate <= dateEnd) ||
      (dateStart <= evalDate && isNaN(dateEnd)) ||
      (dateStart <= evalDate && evalDate <= dateEnd)) {
      return true;
    }
    return false;
  });

  // converting format date dd/mm/yyyy to date format js
  function parseDateValue(rawDate) {
    var dateArray = rawDate.split("/");
    var parsedDate = new Date(dateArray[2], parseInt(dateArray[1]) - 1, dateArray[
      0]); // -1 because months are from 0 to 11   
    return parsedDate;
  }

  $(document).ready(function() {

    let datatableUrl = '{{ route('superuser.finance.receipt.json') }}';

    var datatable = $('#datatable').DataTable({
      dom: "<'row'<'col-sm-6'l><'col-sm-3' <'datesearchbox'>><'col-sm-3'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
      processing: true,
      // serverSide: true,
      ajax: {
        "url": datatableUrl,
        "dataType": "json",
        "type": "GET",
        "data": {
          _token: "{{ csrf_token() }}"
        }
      },
      columns: [{
          data: 'DT_RowIndex',
          name: 'id'
        },
        {
          data: 'created_at',
          render: {
            _: 'display',
            sort: 'timestamp'
          }
        },
        {
          data: 'code'
        },
        {
          data: 'status'
        },
        {
          data: 'action',
          orderable: false,
          searcable: false
        },
        {
          data: 'date_filter',
          visible: false
        },
      ],
      order: [
        [1, 'desc']
      ],
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50],
        [10, 25, 50]
      ],
    });

    $("div.datesearchbox").html(
      '<div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span></div><input type="text" class="form-control form-control-sm pull-right" id="datesearch" placeholder="Filter by date range"> </div>'
    );

    document.getElementsByClassName("datesearchbox")[0].style.textAlign = "right";

    //konfigurasi daterangepicker pada input dengan id datesearch
    $('#datesearch').daterangepicker({
      autoUpdateInput: false
    });

    //menangani proses saat apply date range
    $('#datesearch').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
      start_date = picker.startDate.format('DD/MM/YYYY');
      end_date = picker.endDate.format('DD/MM/YYYY');
      $.fn.dataTableExt.afnFiltering.push(DateFilterFunction);
      datatable.draw();
    });

    $('#datesearch').on('cancel.daterangepicker', function(ev, picker) {
      $(this).val('');
      start_date = '';
      end_date = '';
      $.fn.dataTable.ext.search.splice($.fn.dataTable.ext.search.indexOf(DateFilterFunction, 1));
      datatable.draw();
    });
  });

</script>
