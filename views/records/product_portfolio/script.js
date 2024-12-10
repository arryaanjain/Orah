$(document).ready(function () {
    const companyName = $('#companyName').val();

    // **Listen for changes to the product selection and fetch materials**
    $(document).on('change', 'select[name="edit_product_id"]', function () {
        var productName = $('select[name="edit_product_id"] option:selected').text().trim(); // Get the selected product name
        console.log(`Product changed: ${productName}`);

        // Set the product name in the input field
        $('#product_name').val(productName);

        // Check if a valid product is selected
        if (productName && productName !== '-- Select Product --') {
            // Make an AJAX request to fetch the materials for the selected product
            $.ajax({
                url: '/PIMS/views/records/product_portfolio/fetch_materials.php',
                method: 'GET',
                data: {
                    product_name: productName,
                    company_name: companyName
                },
                success: function (data) {
                    try {
                        console.log(`Data received from fetch_materials: ${JSON.stringify(data)}`);
                        
                        // Get the container where the material fields will be inserted
                        var materialsContainer = $('#materials-container');
                        materialsContainer.empty(); // Clear any previous material fields

                        // Loop through the fetched materials and create input fields for editing
                        data.forEach(function (material, index) {
                            const newRow = `
                            <tr data-material-id="${material.id}">
                                <td><input type="text" class="form-control" name="material[]" value="${material.material}" required></td>
                                <td><input type="number" class="form-control" name="qty[]" value="${material.qty}" required></td>
                                <td><input type="text" class="form-control" name="unit[]" value="${material.unit}" required></td>
                                <td>
                                    <button type="button" class="btn btn-warning editRowBtn">Edit</button>
                                    <button type="button" class="btn btn-danger deleteRowBtn">Delete</button>
                                </td>
                            </tr>
                            `;
                            materialsContainer.append(newRow);
                        });

                        // Add functionality for the edit and delete buttons
                        bindEditDeleteEvents();

                    } catch (error) {
                        console.error('Error parsing JSON response: ', error);
                        alert('Failed to load materials.');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching materials:', error);
                    alert('Failed to load materials.');
                }
            });
        } else {
            // Clear materials if no product is selected
            $('#materials-container').empty();
        }
    });

    // Function to handle the edit and delete button functionality
    function bindEditDeleteEvents() {
        // **Edit Button**: Allow editing the material row
        $('.editRowBtn').off('click').on('click', function () {
            const row = $(this).closest('tr'); // Get the closest table row
            const materialId = row.data('material-id'); // Get the material id from data-attribute

            // Make the input fields editable
            row.find('input').prop('readonly', false);

            // Change the button to "Save"
            $(this).text('Save').removeClass('btn-warning').addClass('btn-success');

            // Update the row when the "Save" button is clicked
            $(this).off('click').on('click', function () {
                const material = row.find('input[name="material[]"]').val();
                const qty = row.find('input[name="qty[]"]').val();
                const unit = row.find('input[name="unit[]"]').val();

                // Send an AJAX request to update the material on the server
                $.ajax({
                    url: '/PIMS/views/records/product_portfolio/update_product_material.php',
                    method: 'POST',
                    data: {
                        material_id: materialId,
                        material: material,
                        qty: qty,
                        unit: unit,
                        company_name: companyName,
                        product_name: $('#product_name').val() // Send product name along with company name
                    },
                    success: function (response) {
                        const result = JSON.parse(response);
                        if (result.status === "success") {
                            alert(result.message);
                            // Revert the button back to "Edit"
                            $(this).text('Edit').removeClass('btn-success').addClass('btn-warning');
                            row.find('input').prop('readonly', true);
                        } else {
                            alert(result.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error updating material:', error);
                        alert('Failed to update material.');
                    }
                });
            });
        });

        // **Delete Button**: Allow deleting the material row
        $('.deleteRowBtn').off('click').on('click', function () {
            const row = $(this).closest('tr'); // Get the closest table row
            const materialId = row.data('material-id'); // Get the material id from data-attribute

            // Confirm the deletion
            if (confirm('Are you sure you want to delete this material?')) {
                // Send an AJAX request to delete the material from the database
                $.ajax({
                    url: '/PIMS/views/records/product_portfolio/delete_product_material.php',
                    method: 'POST',
                    data: {
                        material_id: materialId,
                        company_name: companyName,
                        product_name: $('#product_name').val() // Send product name along with company name
                    },
                    success: function (response) {
                        const result = JSON.parse(response);
                        if (result.status === "success") {
                            alert(result.message);
                            row.remove(); // Remove the row from the table
                        } else {
                            alert(result.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error deleting material:', error);
                        alert('Failed to delete material.');
                    }
                });
            }
        });
    }
});
