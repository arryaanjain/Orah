$(document).ready(function () {
    const companyName = $('#companyName').val();

    // Populate table based on selected order_name
    $('#orderName').change(function () {
        const orderData = $(this).val() ? JSON.parse($(this).val()) : null;

        if (orderData) {
            $.ajax({
                url: '/PIMS/views/sales_book/sales_table_data.php',
                type: 'POST',
                data: { 
                    company_name: companyName, 
                    qty: orderData.qty, 
                    product_name: orderData.product_name 
                },
                success: function (response) {
                    $('#salesTableBody').html(response);
                },
                error: function (xhr, status, error) {
                    console.error(`Error: ${error}`);
                }
            });
        } else {
            $('#salesTableBody').html('');
        }
    });

});
