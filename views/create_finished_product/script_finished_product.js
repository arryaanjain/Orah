$(document).ready(function () {
    const companyName = $('#companyName').val();
    function enableAutocomplete(selector, type) {
        $(document).on('focus', selector, function () {
            if (!$(this).data('autocomplete-initialized')) {
                $(this).autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            url: window.location.origin + '/PIMS/views/create_finished_product/autocomplete_finished_product.php',
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
                <td><input type="text" class="autocomplete material" name="material[]"></td>
                <td><input type="number" class="quantity" name="qty[]"></td>
                <td><input type="text" class="autocomplete unit" name="unit[]"></td>
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
