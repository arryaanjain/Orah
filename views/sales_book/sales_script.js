$(document).ready(function () {
    const companyName = $('#companyName').val();

    // Function to enable autocomplete for specific elements
    function enableAutocomplete(selector, type) {
        $(document).on('focus', selector, function () {
            if (!$(this).data('autocomplete-initialized')) {
                $(this).autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            url: '/PIMS/views/sales_book/sales_autocomplete.php',
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

    // Add new row on button click
    $('#addRowBtn').click(function () {
        const newRow = `
        <tr>
            <td><input type="text" class="autocomplete product" name="product_name[]"></td>
            <td><input type="number" class="quantity" name="qty[]"></td>
            <td><input type="text" class="autocomplete billingName" name="billing_name[]"></td>
            <td><button type="button" class="deleteRowBtn">Delete</button></td>
        </tr>
        `;
        $('#salesTableBody').append(newRow);
        enableAutocomplete('.autocomplete.product', 'product');
        enableAutocomplete('.autocomplete.billingName', 'customer');
    });

    // Delete row on button click
    $(document).on('click', '.deleteRowBtn', function () {
        $(this).closest('tr').remove();
    });

    // Show calculation results
    $('#showCalculationBtn').click(function () {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "/PIMS/views/sales_book/calculate.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    let jsonResponse = JSON.parse(xhr.responseText);
                    displayInventoryStatus(jsonResponse);
                } catch (error) {
                    console.error("Error parsing JSON response:", error);
                    document.getElementById("calculationResults").innerHTML = "<p style='color: red;'>Invalid JSON response from server</p>";
                }
            }
        };

        xhr.send(JSON.stringify({ products: $('#orderName').val() }));
    });

});

// Function to display inventory status
function displayInventoryStatus(responseData) {
    let calculationResults = document.getElementById("calculationResults");
    calculationResults.innerHTML = "";

    for (let product in responseData.products) {
        let inventoryStatus = responseData.products[product];

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
        calculationResults.innerHTML += tableHtml;
    }
}


//table data that is fetched on selecting the order ticket
$(document).ready(function () {
    // When order ticket is selected, update the table
    $('#orderTicket').change(function () {
        let orderData = $(this).val() ? JSON.parse($(this).val()) : null;

        if (orderData) {
            const newRow = `
            <tr>
                <td><input type="date" class="salesDate" name="sales_date[]" required></td>
                <td><input type="text" class="product_name" name="product_name[]" value="${orderData.product_name}" readonly></td>
                <td><input type="number" class="quantity" name="qty[]" value="${orderData.qty}"></td>
                <td><input type="text" class="billingName" name="billing_name[]" value="${orderData.billing_name}" readonly></td>
                <td><button type="button" class="deleteRowBtn">Delete</button></td>
            </tr>
            `;
            $('#salesTableBody').append(newRow);
        }
    });

    // Add empty row manually (for flexibility)
    $('#addSalesRowBtn').click(function () {
        const emptyRow = `
        <tr>
            <td><input type="date" class="salesDate" name="sales_date[]" required></td>
            <td><input type="text" class="product_name" name="product_name[]" required readonly></td>
            <td><input type="number" class="quantity" name="qty[]" required readonly></td>
            <td><input type="text" class="billingName" name="billing_name[]" required readonly></td>
            <td><button type="button" class="deleteRowBtn">Delete</button></td>
        </tr>
        `;
        $('#salesTableBody').append(emptyRow);
    });

    // Delete row
    $(document).on('click', '.deleteRowBtn', function () {
        $(this).closest('tr').remove();
    });
});

$('#salesForm').submit(function (e) {
    e.preventDefault();

    $.ajax({
        url: '/PIMS/views/sales_book/sales_submit.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function (response) {
            alert(response.success || response.error);
            location.reload();
        },
        error: function () {
            alert('Error processing order.');
        }
    });
});
