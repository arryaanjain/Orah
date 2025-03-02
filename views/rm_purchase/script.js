$(document).ready(function () {
    function enableAutocomplete(selector, type) {
        console.log(`Initializing autocomplete for ${selector}, type: ${type}`);

        $(document).on('focus', selector, function () {
            if (!$(this).data('autocomplete-initialized')) {
                console.log(`Autocomplete activated for ${selector}`);

                $(this).autocomplete({
                    source: function (request, response) {
                        console.log(`Fetching autocomplete data for ${type}:`, request.term);
                        
                        $.ajax({
                            url: '/PIMS/views/rm_purchase/autocomplete.php',
                            dataType: "json",
                            data: { term: request.term, type: type },
                            success: function (data) {
                                console.log("Autocomplete Response:", data);
                                response(data);
                            },
                            error: function (xhr) {
                                console.error("Autocomplete AJAX error:", xhr.responseText);
                            }
                        });
                    },
                    minLength: 2
                });

                $(this).data('autocomplete-initialized', true);
            }
        });
    }

    enableAutocomplete('.autocomplete.material', 'material');
    enableAutocomplete('.autocomplete.unit', 'unit');

    $('#addRowBtn').click(function () {
        const newRow = `
        <tr>
            <td><input type="date" class="purchaseDate" name="purchase_date[]"></td>    
            <td><input type="text" class="autocomplete material" name="column1[]"></td>
            <td><input type="number" class="quantity" name="column2[]"></td>
            <td><input type="text" class="autocomplete unit" name="column3[]"></td>
            <td><button type="button" class="deleteRowBtn">Delete</button></td>
        </tr>
        `;

        $('#tableBody').append(newRow);

        enableAutocomplete('.autocomplete.material', 'material');
        enableAutocomplete('.autocomplete.unit', 'unit');
    });

    $(document).on('click', '.deleteRowBtn', function () {
        $(this).closest('tr').remove();
    });
});
