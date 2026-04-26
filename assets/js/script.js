function toggleMenu() {
    const nav = document.getElementById("navLinks");
    if (nav) {
        nav.classList.toggle("active");
    }
}

const yearSpan = document.getElementById("year");
if (yearSpan) {
    yearSpan.textContent = new Date().getFullYear();
}

const prices = {
    "Prishtina": {"New York":399,"Paris":299,"Tokyo":749,"Dubai":499,"Berlin":279,"London":350,"Rome":320},
    "Tirana": {"New York":420,"Paris":310,"Tokyo":770,"Dubai":520,"Berlin":290,"London":360,"Rome":330},
    "Shkup": {"New York":430,"Paris":320,"Tokyo":780,"Dubai":530,"Berlin":295,"London":370,"Rome":335},
    "Podgorica": {"New York":450,"Paris":340,"Tokyo":800,"Dubai":550,"Berlin":310,"London":380,"Rome":350},
    "Sarajevo": {"New York":440,"Paris":330,"Tokyo":790,"Dubai":540,"Berlin":305,"London":375,"Rome":345}
};

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