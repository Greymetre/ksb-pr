
function getUserlist()
{
  var token = $("meta[name='csrf-token']").attr("content");
  var base_url =$('.baseurl').data('baseurl'); 

    $.ajax({
        url: base_url + '/getUserList',
        dataType: "json",
        type: "POST",
        data:{ "_token": token},
        success: function(res){
            if(res){
              var $select = $('#tab_beat_users tr:last').find(".user");

              $select.empty();
              $select.append('<option value="">Select User</option>');
              
              $.each(res,function(key,value){ 
                $select.append(
                  '<option value="'+value.id+'">'+value.name+' '+value.mobile+'</option>'
                );
              });

              // 🔥 SELECT2 APPLY HERE
              $select.select2({
                  placeholder: "Select User",
                  allowClear: true,
                  width: '100%'
              });
            }
        }
    });
}


let selectedCustomers = new Set();

// Helper: Rebuild all customer dropdowns excluding taken IDs
function updateAllCustomerDropdowns() {
    const allSelects = document.querySelectorAll('.beat-customer-rows .customer');

    allSelects.forEach(select => {
        const currentValue = select.value || '';

        // Clear & rebuild
        select.innerHTML = '<option value="">Select Customer</option>';

        if (window.allAvailableCustomers && window.allAvailableCustomers.length > 0) {
            window.allAvailableCustomers.forEach(cust => {
                const idStr = String(cust.id);

                // Show option if:
                // - not taken by anyone else, OR
                // - it is the currently selected value in THIS dropdown
                if (!selectedCustomers.has(idStr) || idStr === currentValue) {
                    const option = new Option(
                        `${cust.name} ${cust.mobile || ''}`.trim(),
                        cust.id
                    );
                    select.add(option);
                }
            });
        }

        // Restore current selection (if still allowed)
        if (currentValue) {
            select.value = currentValue;
        }
    });

    // Re-init select2 (only if you're using it)
    if (typeof $.fn.select2 !== 'undefined') {
        $('.customer').select2();
    }
}



// When you get the list from server (first time or refresh)
function getRetailerlist() {
    var base_url = $('.baseurl').data('baseurl');
    var token    = $("meta[name='csrf-token']").attr("content");
    var state_id = $("select[name=state_id]").val();
    var district_id = $(".district").val() || [];
    var city_id     = $(".city").val() || [];
    var users = [];
    $(".user:selected").each(function(){ users.push($(this).val()); });

    $.ajax({
        url: base_url + '/getRetailerlist',
        dataType: "json",
        type: "POST",
        data: {
            "_token": token,
            state_id: state_id,
            district_id: district_id,
            city_id: city_id,
            user_id: users
        },
        success: function(res) {
            // Store full list globally
            window.allAvailableCustomers = res || [];
            updateAllCustomerDropdowns();

            // Populate only the last (new) row
            const lastSelect = $('#tab_beat_customer tr:last .customer')[0];
            if (lastSelect) {
                lastSelect.innerHTML = '<option value="">Select Customer</option>';

                res.forEach(cust => {
                    if (!selectedCustomers.has(String(cust.id))) {
                        const opt = new Option(`${cust.name} ${cust.mobile||''}`, cust.id);
                        lastSelect.add(opt);
                    }
                });

                $(lastSelect).select2();
            }
        },
        error: function() {
            console.error("Failed to load customers");
        }
    });
}

// ────────────────────────────────────────────────
// Customers dynamic rows + duplicate prevention
// ────────────────────────────────────────────────
$(document).ready(function () {
    const $customerTable = $('table.beat-customer-rows');
    let customerCounter = $customerTable.find('tbody tr').length + 1;
    if (window.existingBeatCustomers && window.existingBeatCustomers.length > 0) {
    window.existingBeatCustomers.forEach(id => {
        selectedCustomers.add(String(id));
    });
}

    // Add new row
    $('a.add-customer-rows').on('click', function (e) {
        e.preventDefault();

        const newRow = `
            <tr class="item-row">
                <td>${customerCounter}</td>
                <td>
                    <select name="customers[]" 
                            class="form-control customer rowchange select2">
                        <option value="">Select Customer</option>
                    </select>
                </td>
                <td class="td-actions text-right">
                    <a class="remove-customer-rows btn btn-danger" title="Remove row">
                        <i class="material-icons">close</i>
                    </a>
                </td>
            </tr>`;

        $customerTable.find('tbody').append(newRow);
        getRetailerlist(); // populate new row
        customerCounter++;
    });

    // Remove dynamic row
    $customerTable.on('click', '.remove-customer-rows', function () {
        const removedId = $(this).closest('tr').find('.customer').val();
        if (removedId) selectedCustomers.delete(String(removedId));
        $(this).closest('tr').remove();
        updateAllCustomerDropdowns();
    });

    // Track changes in dynamic selects
    $(document).on('change', '.beat-customer-rows .customer', function () {
        const customerId = this.value;
        const $row = $(this).closest('tr');
        const prevId = $row.data('prev-customer-id');

        if (prevId) selectedCustomers.delete(String(prevId));

        if (customerId) {
            selectedCustomers.add(String(customerId));
            $row.data('prev-customer-id', customerId);
        } else {
            $row.removeData('prev-customer-id');
        }

        updateAllCustomerDropdowns();
    });

    // ────────────────────────────────────────────────
    // Load existing (saved) customers in edit view
    // ────────────────────────────────────────────────

    // Run once on page load

    // Also catch any dynamic rows that might already exist
    $('.beat-customer-rows .customer').each(function () {
        const val = $(this).val();
        if (val && val !== '') {
            selectedCustomers.add(String(val));
        }
    });

    // Initial refresh
    // (will be more accurate after first getRetailerlist call)

    updateAllCustomerDropdowns();
});

// ────────────────────────────────────────────────
// Beat Users - Add / Remove dynamic rows
// ────────────────────────────────────────────────
$(document).ready(function(){
    var $usersTable = $('table.beat-users-rows');
    var counter = $usersTable.find('tbody tr').length + 1;  // better for edit mode

    $('a.add-users-rows').on('click', function(event){
        event.preventDefault();
        
        var newRow = 
            '<tr class="item-row">' +
                '<td>' + counter + '</td>' +
                '<td>' +
                    '<select name="users[]" class="form-control user rowchange select2">' +
                        '<option value="">Select User</option>' +
                    '</select>' +
                '</td>' +
                '<td class="td-actions text-right">' +
                    '<a class="remove-user-rows btn btn-danger" title="Remove row">' +
                        '<i class="material-icons">close</i>' +
                    '</a>' +
                '</td>' +
            '</tr>';

        $usersTable.find('tbody').append(newRow);

        // Load users into the newest row
        getUserlist();   // your existing function targets #tab_beat_users tr:last .user

        counter++;
    });

    $usersTable.on('click', '.remove-user-rows', function() {
        $(this).closest('tr').remove();
        // Optional: renumber if you care about the numbers
    });
});

function getScheduleUserlist()
{
  var token = $("meta[name='csrf-token']").attr("content");
  var base_url =$('.baseurl').data('baseurl'); 
  var beat_id = $('#beat_id').val();

    $.ajax({
        url: base_url + '/getUserList',
        dataType: "json",
        type: "POST",
        data:{ "_token": token, beat_id : beat_id},
        success: function(res){

            if(res){
                var $select = $('#tab_beat_schedule tr:last').find(".user");

                $select.empty();
                $select.append('<option value="">Select User</option>');
                
                $.each(res,function(key,value){ 
                    $select.append(
                      '<option value="'+value.id+'">'+value.name+' '+value.mobile+'</option>'
                    );
                });

                // 🔥 APPLY SELECT2 HERE
                $select.select2({
                    placeholder: "Select User",
                    allowClear: true,
                    width: '100%'
                });
            }
        }
    });
}
$(document).ready(function(){
    var $table = $('table.beat-schedule-rows'),
    counter = 1;
      $('a.add-schedule-rows').click(function(event){
    event.preventDefault();
    counter++;

    var newRow = 
        '<tr class="item-row"> <td>'+counter+'</td>'+
            '<td><select name="beatdetail[' + counter + '][user_id]" class="form-control user rowchange select2"></select></td>' +
            '<td><input type="date" name="beatdetail[' + counter + '][beat_date]" class="form-control datepicker"></td>' +
            '<td class="td-actions text-right"><a class="remove-rows btn btn-danger"><i class="material-icons">close</i></a></td>' +
        '</tr>';

    $table.append(newRow);

    // 🔥 LOAD USERS + APPLY SELECT2
    getScheduleUserlist();
});
});

function deleteschedules(e)
{
    if (confirm('Are you sure you want to delete this?')) {
        var id = e; 
        var token = $("meta[name='csrf-token']").attr("content");
        var base_url =$('.baseurl').data('baseurl');
        $.ajax({
            url: base_url + '/schedule-delete/'+id,
            dataType: "json",
            type: "DELETE",
            cache: false,
            data:{"id": id,"_token": token},
            success: function(res){
                if(res)
                {
                    swal({
                      title: "Deleted!",
                      text: "Schedule has been deleted successfully",
                      type: "success",
                      confirmButtonText: "OK",
                    });
                    location.reload();
                }
            }
        });
    }
    else
    {
        location.reload();
    }
}
function deleteUserFromBeat(e)
{
    if (confirm('Are you sure you want to delete this?')) {
        var id = e; 
        var token = $("meta[name='csrf-token']").attr("content");
        var base_url =$('.baseurl').data('baseurl');
        $.ajax({
            url: base_url + '/beat-user-delete/'+id,
            dataType: "json",
            type: "DELETE",
            cache: false,
            data:{"id": id,"_token": token},
            success: function(res){
                if(res)
                {
                    swal({
                      title: "Deleted!",
                      text: "Schedule has been deleted successfully",
                      type: "success",
                      confirmButtonText: "OK",
                    });
                    location.reload();
                }
            }
        });
    }
    else
    {
        location.reload();
    }
}

function deletecustomers(e)
{
    if (confirm('Are you sure you want to delete this?')) {
        var id = e; 
        var token = $("meta[name='csrf-token']").attr("content");
        var base_url =$('.baseurl').data('baseurl');
        $.ajax({
            url: base_url + '/beatcustomer-delete/'+id,
            dataType: "json",
            type: "DELETE",
            cache: false,
            data:{"id": id,"_token": token},
            success: function(res){
                if(res)
                {
                    swal({
                      title: "Deleted!",
                      text: "Schedule has been deleted successfully",
                      type: "success",
                      confirmButtonText: "OK",
                    });
                    location.reload();
                }
            }
        });
    }
    else
    {
        location.reload();
    }
}

/*=============== Beat Validation =====================*/
  $('#storeBeatData').validate({
    rules:{
      beat_name:
      {
        required:true,
        minlength:3,
        maxlength: 250,
      },
      description:
      {
        required:true,
        minlength:3,
        maxlength: 450,
      },
    },
    highlight: function(element) {
      $(element).closest('.error').css("display", "none");
    },
    unhighlight: function(element) {
      $(element).closest('.error').css("display", "block");
    },
    messages:{
      name:{
        minlength: "Please enter a valid Award Name.",
        required: "Please enter Award Name",
      },
      description:{
        required: "Please enter Description",
      },
    }
  });
    
