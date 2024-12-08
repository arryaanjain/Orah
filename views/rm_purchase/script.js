$(document).ready(function () {
    const companyName = $('#companyName').val();

    // Function to apply autocomplete for specific elements
    function enableAutocomplete(selector, type) {
        $(document).on('focus', selector, function () {
            if (!$(this).data('autocomplete-initialized')) {
                $(this).autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            url: '/PIMS/views/rm_purchase/autocomplete.php',
                            data: {
                                term: request.term,
                                company_name: companyName,
                                type: type
                            },
                            success: function (data) {
                                response(data);
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
        const newRow = `
            <tr>
                <td><input type="text" class="autocomplete material" name="column1[]"></td>
                <td><input type="number" class="quantity" name="column2[]"></td>
                <td><input type="text" class="autocomplete unit" name="column3[]"></td>
                <td><button class="deleteRowBtn">Delete</button></td>
            </tr>
        `;
        $('#tableBody').append(newRow);
    });

    // Delete row on button click
    $(document).on('click', '.deleteRowBtn', function () {
        $(this).closest('tr').remove();
    });
});
