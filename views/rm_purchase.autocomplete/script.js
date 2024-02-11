//table
$(document).ready(function() {
    var addRowBtn = $("#addRowBtn");
    var tableBody = $("#tableBody");

    addRowBtn.on("click", function() {
        var newRow = $("<tr>" +
            "<td><input type='text' class='autocomplete' name='column1[]'></td>" +
            "<td><input type='text' class='autocomplete' name='column2[]'></td>" +
            "<td><input type='text' class='autocomplete' name='column3[]'></td>" +
            "<td><button class='deleteRowBtn'>Delete</button></td>" +
            "</tr>");
        tableBody.append(newRow);
    });

    tableBody.on("click", ".deleteRowBtn", function() {
        $(this).closest("tr").remove();
    });

    $(document).on("input", ".autocomplete", function() {
        $(this).autocomplete({
            source: "autocomplete.php"
        });
    });

    $(document).on("click", function(e) {
        if (!$(e.target).hasClass("ui-autocomplete-input")) {
            $(".ui-autocomplete").hide();
        }
    });
});


//autocomplete
document.addEventListener("DOMContentLoaded", function() {
    var input = document.getElementById("myInput");
    var dropdown = document.getElementById("myDropdown");

    input.addEventListener("input", function() {
        var value = this.value;

        // Send AJAX request to the server
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "autocomplete.php?value=" + encodeURIComponent(value), true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = xhr.responseText;
                dropdown.innerHTML = response;
                dropdown.style.display = response ? "block" : "none";
            }
        };
        xhr.send();
    });
    document.addEventListener("click", function(e) {
        if (!dropdown.contains(e.target)) {
            dropdown.style.display = "none";
        }
    });
});