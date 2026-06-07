// SafeTrade SA JavaScript

console.log("SafeTrade SA Loaded");

// Welcome message
window.onload = function () {
    console.log("Welcome to SafeTrade SA");
};

// Product form validation
function validateProductForm() {

    let title = document.getElementsByName("title")[0].value;
    let price = document.getElementsByName("price")[0].value;

    if (title.trim() === "") {
        alert("Product title cannot be empty.");
        return false;
    }

    if (price <= 0) {
        alert("Please enter a valid product price.");
        return false;
    }

    return true;
}

// Registration form validation
function validateRegisterForm() {

    let password =
        document.getElementsByName("password")[0].value;

    if (password.length < 8) {

        alert(
            "Password must contain at least 8 characters."
        );

        return false;
    }

    return true;
}

// Search functionality
function searchProducts() {

    let input =
        document.getElementById("searchInput");

    let filter =
        input.value.toUpperCase();

    let cards =
        document.getElementsByClassName("card");

    for (let i = 0; i < cards.length; i++) {

        let title =
            cards[i].getElementsByTagName("h3")[0];

        if (
            title.innerHTML.toUpperCase()
            .indexOf(filter) > -1
        ) {

            cards[i].style.display = "";

        } else {

            cards[i].style.display = "none";
        }
    }
}