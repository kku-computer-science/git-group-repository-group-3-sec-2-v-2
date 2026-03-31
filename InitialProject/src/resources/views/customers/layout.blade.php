<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel8 CRUD @fahmidasclassroom.com</title>

    <!-- Bootstrap 5 (เดียวกับทุก layout ในระบบ) -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">

    <!-- Shared CSS Design Tokens (ใช้ร่วมกันทุก layout) -->
    <link rel="stylesheet" href="{{ asset('css/shared.css') }}">

    <!-- jQuery (ใช้ vendor version เดียวกัน) -->
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
</head>

<body>
    <div class="container">
        @yield('content')
    </div>
</body>
<script>
    $(document).ready(function() {

        /* When click New customer button */
        $('#new-customer').click(function() {
            $('#btn-save').val("create-customer");
            $('#customer').trigger("reset");
            $('#customerCrudModal').html("Add New Customer EiEi");
            $('#crud-modal').modal('show');
        });

        /* Edit customer */
        $('body').on('click', '#edit-customer', function() {
            var customer_id = $(this).data('id');
            $.get('customers/' + customer_id + '/edit', function(data) {
                $('#customerCrudModal').html("Edit customer");
                $('#btn-update').val("Update");
                $('#btn-save').prop('disabled', false);
                $('#crud-modal').modal('show');
                $('#cust_id').val(data.id);
                $('#name').val(data.name);
                $('#email').val(data.email);
                $('#address').val(data.address);
            })
        });
        /* Show customer */
        $('body').on('click', '#show-customer', function() {
            $('#customerCrudModal-show').html("Customer Details");
            $('#crud-modal-show').modal('show');
        });

        /* Delete customer */
        $('body').on('click', '#delete-customer', function() {
            var customer_id = $(this).data("id");
            var token = $("meta[name='csrf-token']").attr("content");
            confirm("Are You sure want to delete !");

            $.ajax({
                type: "DELETE",
                url: "http://127.0.0.1:8000/customers/" + customer_id,
                data: {
                    "id": customer_id,
                    "_token": token,
                },
                success: function(data) {
                    $('#msg').html('Customer entry deleted successfully');
                    $("#customer_id_" + customer_id).remove();
                },
                error: function(data) {
                    console.log('Error:', data);
                }
            });
        });
    });
</script>

</html>
