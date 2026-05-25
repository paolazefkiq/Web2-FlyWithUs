function toggleMenu() {
    const nav = document.getElementById("navLinks");
    if (nav) {
        nav.classList.toggle("active");
    }
}

function updatePopupVisibility(popupElement, buttonElement) {
    if (!popupElement || !buttonElement) {
        return;
    }

    buttonElement.addEventListener("click", () => {
        popupElement.style.display = "none";
    });

    popupElement.addEventListener("click", (event) => {
        if (event.target === popupElement) {
            popupElement.style.display = "none";
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            popupElement.style.display = "none";
        }
    });
}

const yearSpan = document.getElementById("year");
if (yearSpan) {
    yearSpan.textContent = new Date().getFullYear();
}

const bookingForm = document.getElementById("bookingForm");
const fromInput = document.getElementById("from");
const toInput = document.getElementById("to");
const passengersInput = document.getElementById("passengers");
const returnInput = document.getElementById("return");
const departInput = document.getElementById("depart");
const livePriceBox = document.getElementById("livePrice");

if (departInput) {
    const today = new Date().toISOString().split("T")[0];
    departInput.setAttribute("min", today);
}

if (departInput && returnInput) {
    departInput.addEventListener("change", () => {
        returnInput.min = departInput.value;
    });
}

function updateLivePrice() {
    if (!fromInput || !toInput || !passengersInput || !returnInput || !livePriceBox) {
        return;
    }

    const from = fromInput.value;
    const to = toInput.value;
    const passengers = parseInt(passengersInput.value) || 0;
    const retDate = returnInput.value;

    if (!from || !to || !passengers) {
        livePriceBox.textContent = "Çmimi: $0";
        return;
    }

    let pricePerPerson = prices[from] ? prices[from][to] : 0;
    if (retDate) {
        pricePerPerson *= 2;
    }

    const totalPrice = pricePerPerson * passengers;
    livePriceBox.textContent = `Çmimi: $${totalPrice}`;
}

if (fromInput && toInput && passengersInput && returnInput) {
    [fromInput, toInput, passengersInput, returnInput].forEach(el => {
        el.addEventListener("change", updateLivePrice);
    });
}

document.addEventListener("DOMContentLoaded", function () {
    updateLivePrice();
});

const successPopup = document.getElementById("successPopup");
const closeBtn = document.getElementById("closePopup");

if (successPopup && closeBtn) {
    closeBtn.addEventListener("click", () => {
        successPopup.style.display = "none";
    });

    successPopup.addEventListener("click", function (e) {
        if (e.target === successPopup) {
            successPopup.style.display = "none";
        }
    });

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
            successPopup.style.display = "none";
        }
    });
}

document.querySelectorAll(".card-btn").forEach(button => {
    button.addEventListener("click", () => {
        const city = button.getAttribute("data-city");
        const bookingSection = document.getElementById("booking");
        const toField = document.getElementById("to");

        if (toField && city) {
            toField.value = city;
            updateLivePrice();
        }

        if (bookingSection) {
            bookingSection.scrollIntoView({ behavior: "smooth", block: "start" });
        }
    });
});

const contactPopup = document.getElementById("contactPopup");
const contactClose = document.getElementById("contactClose");

if (contactPopup && contactClose) {
    contactClose.addEventListener("click", () => {
        contactPopup.style.display = "none";
    });

    contactPopup.addEventListener("click", (e) => {
        if (e.target === contactPopup) {
            contactPopup.style.display = "none";
        }
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            contactPopup.style.display = "none";
        }
    });
}