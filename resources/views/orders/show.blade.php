<x-app-layout>
  <style>
    .order-show-actions {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-end;
      gap: 7px;
      padding-top: 12px;
    }
    body.fk-shell .order-show-actions .btn {
      width: auto !important;
      min-width: 0 !important;
      min-height: 32px !important;
      height: 32px !important;
      margin: 0 !important;
      padding: 0 12px !important;
      border-radius: 8px !important;
      font-size: 11px !important;
      line-height: 30px !important;
      text-transform: none !important;
    }
  </style>
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-12">
                <h3 class="card-title pb-3">{!! trans('panel.order.title_singular') !!} Detail</h3>
              </div>
              <!-- /.col -->
            </div>

            <div class="invoice p-3 mb-3">
              <!-- title row -->
              <div class="row">
                <div class="col-3">
                  <h4>
                    <small class="float-left">{!! trans('panel.order.id') !!} # {!! $orders['id'] !!}</small>
                  </h4>
                </div>
                <div class="col-3">
                  <h4>
                    <!-- <img src="" class="brand-image" width="70px" alt="Logo"> <span> </span> -->
                    <small class="float-left">{!! trans('panel.order.orderno') !!}: {!! $orders['orderno'] !!}</small>
                  </h4>
                </div>
                <div class="col-3">
                  <h4>
                    <!-- <img src="" class="brand-image" width="70px" alt="Logo"> <span> </span> -->
                    <small class="float-left">Date: {!! date("d-M-Y", strtotime($orders['order_date'])) !!}</small>
                  </h4>
                </div>
                <div class="col-3">
                  <h4>
                    <!-- <img src="" class="brand-image" width="70px" alt="Logo"> <span> </span> -->
                    <small class="float-left">Created By: {{ data_get($orders, 'createdbyname.name', '-') }}</small>
                  </h4>
                </div>
                <!-- /.col -->
              </div>
              <hr>
              <!-- info row -->
              <div class="row invoice-info">
                <div class="col-sm-6 invoice-col">
                  From
                  <address>
                    <strong>{{ data_get($orders, 'sellers.name', '-') }}</strong>
                    @if(data_get($orders, 'sellers.customertypes.customertype_name'))
                      ({{ data_get($orders, 'sellers.customertypes.customertype_name') }})
                    @endif<br>
                    {{ data_get($orders, 'sellers.customeraddress.address1', '') }}, {{ data_get($orders, 'sellers.customeraddress.address2', '') }}<br>
                    {{ data_get($orders, 'sellers.customeraddress.locality', '-') }}, {{ data_get($orders, 'sellers.customeraddress.cityname.city_name', '-') }} {{ data_get($orders, 'sellers.customeraddress.pincodename.pincode', '-') }}<br>
                    Phone: {{ data_get($orders, 'sellers.mobile', '-') }}<br>
                    Email: {{ data_get($orders, 'sellers.email', '-') }}
                  </address>
                </div>
                <!-- /.col -->
                <div class="col-sm-6 invoice-col">
                  To
                  <address>
                    <strong>{{ data_get($orders, 'buyers.name', '-') }}</strong>
                    @if(data_get($orders, 'buyers.customertypes.customertype_name'))
                      ({{ data_get($orders, 'buyers.customertypes.customertype_name') }})
                    @endif<br>
                    {{ data_get($orders, 'buyers.customeraddress.address1', '') }}, {{ data_get($orders, 'buyers.customeraddress.address2', '') }}<br>
                    {{ data_get($orders, 'buyers.customeraddress.locality', '-') }}, {{ data_get($orders, 'buyers.customeraddress.cityname.city_name', '-') }} {{ data_get($orders, 'buyers.customeraddress.pincodename.pincode', '-') }}<br>
                    Phone: {{ data_get($orders, 'buyers.mobile', '-') }}<br>
                    Email: {{ data_get($orders, 'buyers.email', '-') }}
                  </address>
                </div>
              </div>
              <!-- /.row -->

              <!-- Table row -->
              <div class="row">
                <div class="col-12 table-responsive">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>{!! trans('panel.global.products') !!}</th>
                        <th>{!! trans('panel.global.quantity') !!}</th>
                        <!-- <th>{!! trans('panel.global.product_detail') !!}</th> -->
                        <th>{!! trans('panel.global.list_price') !!}</th>
                        <th>Tax</th>
                        <th>{!! trans('panel.global.amount') !!}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($orders->exists && isset($orderdetails))
                      @foreach($orderdetails as $rows )
                      <tr>
                        <td>
                          {!! isset($rows['products']['product_name']) ? $rows['products']['product_name'] : '' !!}
                          {!! isset($rows['products']['product_code']) ? $rows['products']['product_code'] : '' !!} <br>
                          Detail Title :
                          <span class="prd_title">
                            {!! isset($rows['productdetails']['detail_title']) ? $rows['productdetails']['detail_title'] : '' !!}
                          </span>
                        </td>

                        <td>{!! $rows['quantity'] !!}</td>
                        <!--  <td>
                              GST Percent : <span class="gst_percent">{!! isset($rows['productdetails']['gst']) ? $rows['productdetails']['gst'] : '' !!}</span> <br>
                            GST Amount : <span class="gstamount">{!! $rows['tax_amount'] !!}</span> <br>
                            </td> -->

                        <td>{!! $rows['price'] !!}</td>
                        <td>{!! $rows['gst'] !!}</td>
                        <td>{!! $rows['line_total'] !!}</td>
                      </tr>
                      @endforeach
                      @endif
                    </tbody>
                  </table>
                </div>
                <!-- /.col -->
              </div>
              <!-- /.row -->

              <div class="row">
                <!-- accepted payments column -->
                <div class="col-6">
                  <p class="lead"></p>
                  <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
                    {!! $orders['description'] !!}
                  </p>
                </div>
                <!-- /.col -->
                <div class="col-6">
                  <p class="lead"></p>


                  <div class="table-responsive">
                    <table class="table">
                      <tbody>
                        <tr>
                          <th style="width:50%">Subtotal :</th>
                          <td>-</td>
                          <td>{!! $orders['sub_total'] !!}</td>
                        </tr>

                        <tr>
                          <th style="width:50%">5%Tax:</th>
                          <td>-</td>
                          <td>{!! $orders->gst5_amt !!}</td>
                        </tr>
                        <tr>
                          <th style="width:50%">12%Tax:</th>
                          <td>-</td>
                          <td>{!! $orders->gst12_amt !!}</td>
                        </tr>
                        <tr>
                          <th style="width:50%">18%Tax:</th>
                          <td>-</td>
                          <td>{!! $orders->gst18_amt !!}</td>
                        </tr>
                        <tr>
                          <th style="width:50%">28%Tax:</th>
                          <td>-</td>
                          <td>{!! $orders->gst28_amt !!}</td>
                        </tr>


                        <tr>
                          <th>Tax</th>
                          <td>-</td>
                          <td>{!! $orders['total_gst'] !!}</td>
                        </tr>
                        <tr>
                          <th>Total:</th>
                          <td>-</td>
                          <td>{!! $orders['grand_total'] !!}</td>
                        </tr>
                        <tr>
                          <th>Remark:</th>
                          <td colspan="2">{!! $orders['order_remark'] !!}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <!-- /.col -->
              </div>
              <!-- /.row -->

              <!-- this row will not appear when printing -->
              <div class="row no-print">
                <div class="col-12 order-show-actions">
                  @if($orders['status_id'] != 1 && $orders['status_id'] != 4)
                  <a href="{!! url('order-dispatched/'.encrypt($orders->id)) !!}" class="btn btn-success float-right">Fully Dispatched</a>
                  @endif

                  @if($orders['status_id'] != 1 && $orders['status_id'] != 4)
                  <a href="{!! url('order-partially-dispatched/'.encrypt($orders->id)) !!}" class="btn btn-theme float-right">Partially Dispatched</a>
                  @endif

                  @if($orders['status_id'] != 1 && $orders['status_id'] != 4)
                  <button type="button" data-orderid="{!! encrypt($orders->id) !!}" id="cancleButton" class="btn btn-danger float-right">Cancel</button>
                  @endif
                  @if($orders['status_id'] != NULL)
                  @if(auth()->user()->can('pendding_orders'))
                  <button type="button" data-orderid="{!! encrypt($orders->id) !!}" id="penddingButton" class="btn btn-warning float-right">Pending</button>
                  @endif
                  @endif
                  @if($orders['status_id'] == 4)
                  <button type="button" disabled class="btn btn-danger float-right">Canceled</button>
                  @endif
                  @if($orders['status_id'] == 1)
                  <button type="button" disabled class="btn btn-success float-right">Dispatched</button>
                  @endif
                </div>
              </div>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
  </section>
  <script>
    $("#cancleButton").on("click", function() {
      Swal.fire({
        title: "Are you sure?",
        text: "Enter remark:",
        icon: "warning",
        input: 'text',
        returnInputValueOnDeny: true,
        showCancelButton: true,
        confirmButtonText: 'Yes Cancle',
        cancelButtonText: 'No',
        inputValidator: (value) => {
          if (!value) {
            return "You need to write something!";
          }
        }
      }).then((result) => {
        if (result.value) {
          var token = $("meta[name='csrf-token']").attr("content");
          var base_url = $('.baseurl').data('baseurl');
          var orderId = $(this).data("orderid");
          $.ajax({
            url: base_url + '/order-cancle/' + orderId,
            dataType: "json",
            type: "POST",
            data: {
              _token: token,
              remark: result.value
            },
            success: function(res) {
              Swal.fire({
                title: res.status,
                text: res.message,
              });
              if (res.status == 'success') {
                window.location.href = base_url + '/orders';
              }
            }
          });
        }
      });

    })
    $("#penddingButton").on("click", function() {
      Swal.fire({
        title: "Are you sure?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
      }).then((result) => {
        if (result.value) {
          var token = $("meta[name='csrf-token']").attr("content");
          var base_url = $('.baseurl').data('baseurl');
          var orderId = $(this).data("orderid");
          $.ajax({
            url: base_url + '/order-pendding/' + orderId,
            dataType: "json",
            type: "POST",
            data: {
              _token: token,
              // remark: result.value
            },
            success: function(res) {
              Swal.fire({
                title: res.status,
                text: res.message,
              });
              if (res.status == 'success') {
                window.location.href = base_url + '/orders';
              }
            }
          });
        }
      });

    })
  </script>
  <!-- /.content -->
</x-app-layout>
