$(document).ready(function () {
    const companyName = $('#companyName').val();

    // Function to apply autocomplete for specific elements
    function enableAutocomplete(selector, type) {
        $(document).on('focus', selector, function () {
            if (!$(this).data('autocomplete-initialized')) {
                $(this).autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            url: '/PIMS/views/order_book/order_autocomplete.php',
                            data: {
                                term: request.term,
                                company_name: companyName,
                                type: type
                            },
                            success: function (data) {
                                response(data);
                            },
                            error: function (xhr, status, error) {
                                console.error(`Autocomplete error: ${error}`);
                            }
                        });
                    },
                    minLength: 2 // Start autocomplete after 2 characters
                });
                $(this).data('autocomplete-initialized', true);
            }
        });
    }

    // Enable autocomplete for product and customer (billing name) inputs
    enableAutocomplete('.autocomplete.product', 'product');
    enableAutocomplete('.autocomplete.billingName', 'customer');

    // Add new row on button click
    $('#addRowBtn').click(function () {
        const newRow = `
        <tr>
            <td><input type="date" class="orderDate" name="order_date[]"></td>    
            <td><input type="text" class="autocomplete product" name="column1[]"></td>
            <td><input type="number" class="quantity" name="column2[]"></td>
            <td><input type="text" class="autocomplete billingName" name="billing_name[]"></td>
            <td><button type="button" class="deleteRowBtn">Delete</button></td>
        </tr>
        `;
        $('#tableBody').append(newRow);
        enableAutocomplete('.autocomplete.product', 'product');
        enableAutocomplete('.autocomplete.billingName', 'customer');
    });
    
    // Delete row on button click
    $(document).on('click', '.deleteRowBtn', function () {
        $(this).closest('tr').remove();
    });
});
