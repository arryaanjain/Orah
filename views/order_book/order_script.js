$(document).ready(function () {
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

function displayInventoryStatus(responseData) {
    let calculationResults = document.getElementById("calculationResults");
    calculationResults.innerHTML = ""; // Clear previous results

    // Loop through each product in the JSON response
    for (let product in responseData.products) {
        let inventoryStatus = responseData.products[product];

        // Create HTML structure for each product
        let tableHtml = `
            <hr>
            <h3>Inventory Status for Product: <strong>${product}</strong></h3>
            <table border="1" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Required Quantity</th>
                        <th>Available Quantity</th>
                        <th>Difference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>`;

        // Populate table rows
        inventoryStatus.forEach(status => {
            let statusHtml = (status.difference < 0)
                ? `<td style="color: red;">You need ${Math.abs(status.difference)} more</td>`
                : `<td style="color: green;">Inventory Competent</td>`;

            tableHtml += `
                <tr>
                    <td>${status.material}</td>
                    <td>${status.requiredQty}</td>
                    <td>${status.availableQty}</td>
                    <td>${status.difference}</td>
                    ${statusHtml}
                </tr>`;
        });

        tableHtml += `</tbody></table><br/>`;

        // Append the table to the div
        calculationResults.innerHTML += tableHtml;
    }
}

