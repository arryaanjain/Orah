let materialCounter = 1;
let unitCounter = 1;
let customerCounter = 1;

function addUserInput() {
    let userInputDiv = document.getElementById('userInputs');
    let inputGroup = document.createElement('div');
    inputGroup.className = 'input-group mb-3';
    inputGroup.id = `material-group-${materialCounter}`;
    
    inputGroup.innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <label for="name${materialCounter}" class="me-2">Material Name:</label>
            <input type="text" class="form-control" name="material_name[]" id="name${materialCounter}" required>
            <button type="button" class="btn btn-danger" onclick="deleteInput('material-group-${materialCounter}')">Delete</button>
        </div>
    `;

    userInputDiv.appendChild(inputGroup);
    materialCounter++;
}

function addUserInput2() {
    let userInputDiv2 = document.getElementById('userInputs2');
    let inputGroup = document.createElement('div');
    inputGroup.className = 'input-group mb-3';
    inputGroup.id = `unit-group-${unitCounter}`;
    
    inputGroup.innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <label for="name2${unitCounter}" class="me-2">Unit Name:</label>
            <input type="text" class="form-control" name="unit_name[]" id="name2${unitCounter}" required>
            <button type="button" class="btn btn-danger" onclick="deleteInput('unit-group-${unitCounter}')">Delete</button>
        </div>
    `;

    userInputDiv2.appendChild(inputGroup);
    unitCounter++;
}

function addCustomerInput() {
    let customerInputDiv = document.getElementById('customerInputs');
    let inputGroup = document.createElement('div');
    inputGroup.className = 'input-group mb-3';
    inputGroup.id = `customer-group-${customerCounter}`;
    
    inputGroup.innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <label for="billing_name${customerCounter}" class="me-2">Billing Name:</label>
            <input type="text" class="form-control" name="billing_name[]" id="billing_name${customerCounter}" required>
            <label for="place${customerCounter}" class="me-2">Place:</label>
            <input type="text" class="form-control" name="place[]" id="place${customerCounter}" required>
            <label for="gst_number${customerCounter}" class="me-2">GST Number:</label>
            <input type="text" class="form-control" name="gst_number[]" id="gst_number${customerCounter}">
            <label for="email${customerCounter}" class="me-2">Email:</label>
            <input type="email" class="form-control" name="email[]" id="email${customerCounter}">
            <label for="phone${customerCounter}" class="me-2">Phone:</label>
            <input type="text" class="form-control" name="phone[]" id="phone${customerCounter}">
            <button type="button" class="btn btn-danger" onclick="deleteInput('customer-group-${customerCounter}')">Delete</button>
        </div>
    `;

    customerInputDiv.appendChild(inputGroup);
    customerCounter++;
}

function deleteInput(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.remove();
    }
}
