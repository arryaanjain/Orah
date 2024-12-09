$(document).ready(function () {
    const companyName = $('#companyName').val();

    // Function to apply autocomplete for specific elements
    function enableAutocomplete(selector, type) {
        $(document).on('focus', selector, function () {
            if (!$(this).data('autocomplete-initialized')) {
                console.log(`Initializing autocomplete for ${type} on selector: ${selector}`);
                $(this).autocomplete({
                    source: function (request, response) {
                        console.log(`Autocomplete request for term: ${request.term}`);
                        $.ajax({
                            url: '/PIMS/views/rm_purchase/autocomplete.php',
                            data: {
                                term: request.term,
                                company_name: companyName,
                                type: type
                            },
                            success: function (data) {
                                console.log(`Autocomplete data received: ${JSON.stringify(data)}`);
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

    // Enable autocomplete for material and unit inputs
    enableAutocomplete('.autocomplete.material', 'material');
    enableAutocomplete('.autocomplete.unit', 'unit');

    // Add new row on button click
    $('#addRowBtn').click(function () {
        console.log('Add Row button clicked');
        
        const newRow = `
        <tr>
                   
                <td><input type="date" class="purchaseDate" name="purchase_date[]"></td>    
                <td><input type="text" class="autocomplete material" name="column1[]"></td>
                <td><input type="number" class="quantity" name="column2[]"></td>
                <td><input type="text" class="autocomplete unit" name="column3[]"></td>
                <td><button type="button" class="deleteRowBtn">Delete</button></td>
            </tr>
        `;
        console.log(`New row added: ${newRow}`);
        $('#tableBody').append(newRow);
    
        // Re-initialize autocomplete after adding a new row
        enableAutocomplete('.autocomplete.material', 'material');
        enableAutocomplete('.autocomplete.unit', 'unit');
    });
    
    // Delete row on button click
    $(document).on('click', '.deleteRowBtn', function () {
        console.log('Delete button clicked');
        $(this).closest('tr').remove();
    });
});
