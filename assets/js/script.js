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

function buildBookingPath(destinationId) {
    const baseUrl = window.appBaseUrl || "";

    if (!baseUrl) {
        return "";
    }

    if (!destinationId) {
        return `${baseUrl}/pages/booking.php`;
    }

    return `${baseUrl}/pages/booking.php?destination_id=${encodeURIComponent(destinationId)}`;
}

function getCurrentBookingPrice() {
    const originCityInput = document.getElementById("origin_city_id");
    const destinationInput = document.getElementById("destination_id");
    const passengersInput = document.getElementById("passengers");
    const returnInput = document.getElementById("return");
    const prices = window.bookingPrices || {};

    if (!originCityInput || !destinationInput || !passengersInput || !returnInput) {
        return null;
    }

    const originCityId = originCityInput.value;
    const destinationId = destinationInput.value;
    const passengersCount = parseInt(passengersInput.value, 10) || 0;
    const hasReturnFlight = returnInput.value !== "";

    if (!originCityId || !destinationId || !passengersCount) {
        return null;
    }

    let pricePerPassenger = 0;

    if (prices[originCityId] && prices[originCityId][destinationId]) {
        pricePerPassenger = parseFloat(prices[originCityId][destinationId]) || 0;
    }

    if (!pricePerPassenger) {
        return null;
    }

    if (hasReturnFlight) {
        pricePerPassenger *= 2;
    }

    return {
        originCityId,
        totalPrice: pricePerPassenger * passengersCount,
    };
}

function getOriginCityLabel(originCityId) {
    const originCities = window.bookingOriginCities || {};

    if (originCityId && originCities[originCityId]) {
        return originCities[originCityId].label;
    }

    return "";
}

function updateLivePrice() {
    const livePriceBox = document.getElementById("livePrice");
    const offerPriceBox = document.getElementById("offerPriceBox");
    const offerPriceText = document.getElementById("offerPriceText");
    const currentPrice = getCurrentBookingPrice();

    if (livePriceBox) {
        if (!currentPrice) {
            livePriceBox.textContent = "Çmimi: $0";
        } else {
            livePriceBox.textContent = `Çmimi: $${currentPrice.totalPrice.toFixed(2)}`;
        }
    }

    if (offerPriceBox && offerPriceText) {
        if (!currentPrice) {
            offerPriceBox.classList.add("is-hidden");
            offerPriceText.textContent = "";
        } else {
            offerPriceBox.classList.remove("is-hidden");
            offerPriceText.textContent = `Nga ${getOriginCityLabel(currentPrice.originCityId)} • $${currentPrice.totalPrice.toFixed(2)}`;
        }
    }
}

function applyOfferPanelState(panelData) {
    const offerCard = document.getElementById("selectedDestinationCard");
    const offerMedia = document.getElementById("offerPanelMedia");
    const eyebrow = document.getElementById("destinationEyebrow");
    const title = document.getElementById("destinationTitle");
    const subtitle = document.getElementById("destinationSubtitle");
    const description = document.getElementById("destinationDescription");

    if (!offerCard || !offerMedia || !eyebrow || !title || !subtitle || !description) {
        return;
    }

    offerCard.classList.toggle("is-default", !!panelData.isDefault);
    offerCard.classList.toggle("has-destination", !panelData.isDefault);
    offerMedia.style.backgroundImage = `url('${panelData.imageUrl}')`;
    eyebrow.textContent = panelData.eyebrow;
    title.textContent = panelData.title;
    subtitle.textContent = panelData.subtitle;
    subtitle.classList.toggle("is-hidden", !panelData.subtitle);
    description.textContent = panelData.description;
}

function updateDestinationDetails(useTransition = false) {
    const destinationInput = document.getElementById("destination_id");
    const offerCard = document.getElementById("selectedDestinationCard");
    const loginLink = document.getElementById("bookingLoginLink");
    const destinations = window.bookingDestinations || {};
    const defaultPanel = window.bookingDefaultPanel || {};

    if (!destinationInput) {
        return;
    }

    const selectedId = destinationInput.value;
    const selectedDestination = destinations[selectedId];
    const nextPath = buildBookingPath(selectedId);
    let panelData = {
        eyebrow: defaultPanel.eyebrow || "Fly With Us",
        title: defaultPanel.title || "Udhëtimi juaj fillon këtu",
        subtitle: defaultPanel.subtitle || "",
        description: defaultPanel.description || "",
        imageUrl: defaultPanel.imageUrl || "",
        isDefault: true,
    };

    if (selectedDestination) {
        panelData = {
            eyebrow: selectedDestination.country,
            title: selectedDestination.city,
            subtitle: "",
            description: selectedDestination.description,
            imageUrl: selectedDestination.imageUrl,
            isDefault: false,
        };
    }

    if (nextPath && window.location.pathname.endsWith("/booking.php")) {
        window.history.replaceState({}, "", nextPath);

        if (loginLink) {
            loginLink.href = `${window.appBaseUrl}/login.php?redirect=${encodeURIComponent(nextPath)}`;
        }
    }

    const applyState = () => {
        applyOfferPanelState(panelData);
        updateLivePrice();
    };

    if (!offerCard || !useTransition) {
        applyState();
        return;
    }

    offerCard.classList.add("is-updating");

    window.setTimeout(() => {
        applyState();
        offerCard.classList.remove("is-updating");
    }, 180);
}

function activateGuestLoginNotice() {
    const notice = document.getElementById("guestLoginNotice");

    if (!notice) {
        return;
    }

    notice.classList.add("is-active");
    notice.scrollIntoView({ behavior: "smooth", block: "center" });

    window.setTimeout(() => {
        notice.classList.remove("is-active");
    }, 2200);
}

function showBookingActionMessage(message, type) {
    const messageBox = document.getElementById("bookingActionMessage");

    if (!messageBox) {
        return;
    }

    messageBox.hidden = false;
    messageBox.textContent = message;
    messageBox.className = `alert ${type}`;
}

function initializeBookingCancellation() {
    const cancelButtons = document.querySelectorAll(".js-cancel-booking");

    if (!cancelButtons.length) {
        return;
    }

    cancelButtons.forEach((button) => {
        button.addEventListener("click", async () => {
            const bookingId = button.dataset.bookingId;

            if (!bookingId) {
                return;
            }

            const confirmed = window.confirm("A jeni i sigurt qe deshironi ta anuloni kete rezervim?");

            if (!confirmed) {
                return;
            }

            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = "Duke anuluar...";

            try {
                const response = await fetch(`${window.appBaseUrl}/pages/ajax-cancel-booking.php`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: new URLSearchParams({ booking_id: bookingId }).toString(),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || "Veprimi nuk u ruajt.");
                }

                const row = document.getElementById(`booking-row-${bookingId}`);

                if (row) {
                    const statusBadge = row.querySelector("[data-booking-status]");
                    const actionsCell = row.querySelector("[data-booking-actions]");

                    if (statusBadge) {
                        statusBadge.textContent = data.status_label || "Anuluar";
                        statusBadge.classList.remove("status-badge--active");
                        statusBadge.classList.add("status-badge--cancelled");
                    }

                    if (actionsCell) {
                        actionsCell.innerHTML = '<span class="table-action-note">Anuluar</span>';
                    }
                }

                showBookingActionMessage(data.message || "Rezervimi u anulua me sukses.", "success");
            } catch (error) {
                button.disabled = false;
                button.textContent = originalText;
                showBookingActionMessage(error.message || "Ndodhi nje gabim. Ju lutemi provoni perseri.", "error");
            }
        });
    });
}

function getWeatherCodeLabel(weatherCode) {
    const weatherLabels = {
        0: "E kthjellet",
        1: "Kryesisht kthjellet",
        2: "Pjeserisht me re",
        3: "Me re",
        45: "Mjegull",
        48: "Mjegull e dendur",
        51: "Shi i lehte",
        53: "Shi i moderuar",
        55: "Shi i forte",
        61: "Shi",
        63: "Shi i moderuar",
        65: "Shi i forte",
        71: "Bore e lehte",
        73: "Bore",
        75: "Bore e forte",
        80: "Rrebeshe",
        81: "Rrebeshe mesatare",
        82: "Rrebeshe te forta",
        95: "Stuhi",
    };

    if (Object.prototype.hasOwnProperty.call(weatherLabels, weatherCode)) {
        return weatherLabels[weatherCode];
    }

    return "Te dhena jo te plota";
}

async function updateWeatherInfoCard() {
    const destinationInput = document.getElementById("destination_id");
    const destinations = window.bookingDestinations || {};
    const loading = document.getElementById("weatherApiLoading");
    const empty = document.getElementById("weatherApiEmpty");
    const error = document.getElementById("weatherApiError");
    const content = document.getElementById("weatherApiContent");
    const locationField = document.getElementById("weatherApiLocation");
    const temperatureField = document.getElementById("weatherApiTemperature");
    const conditionField = document.getElementById("weatherApiCondition");
    const windField = document.getElementById("weatherApiWind");

    if (
        !destinationInput ||
        !loading ||
        !empty ||
        !error ||
        !content ||
        !locationField ||
        !temperatureField ||
        !conditionField ||
        !windField
    ) {
        return;
    }

    const selectedDestination = destinations[destinationInput.value];
    const requestedDestinationId = destinationInput.value;

    error.hidden = true;
    content.hidden = true;
    loading.hidden = true;

    if (!selectedDestination || !selectedDestination.city) {
        empty.hidden = false;
        return;
    }

    empty.hidden = true;
    loading.hidden = false;

    try {
        const geocodingResponse = await fetch(
            `https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(selectedDestination.city)}&count=8&language=en&format=json`
        );

        if (!geocodingResponse.ok) {
            throw new Error("Geocoding failed");
        }

        const geocodingData = await geocodingResponse.json();
        const locations = Array.isArray(geocodingData.results) ? geocodingData.results : [];
        const matchedLocation =
            locations.find((location) =>
                String(location.country || "").toLowerCase() === String(selectedDestination.country || "").toLowerCase()
            ) || locations[0];

        if (!matchedLocation) {
            throw new Error("Location not found");
        }

        const forecastResponse = await fetch(
            `https://api.open-meteo.com/v1/forecast?latitude=${encodeURIComponent(matchedLocation.latitude)}&longitude=${encodeURIComponent(matchedLocation.longitude)}&current=temperature_2m,weather_code,wind_speed_10m&timezone=auto&forecast_days=1`
        );

        if (!forecastResponse.ok) {
            throw new Error("Forecast failed");
        }

        const forecastData = await forecastResponse.json();
        const current = forecastData.current || null;

        if (!current || destinationInput.value !== requestedDestinationId) {
            return;
        }

        locationField.textContent = `${selectedDestination.city}, ${selectedDestination.country}`;
        temperatureField.textContent = typeof current.temperature_2m === "number"
            ? `${current.temperature_2m}°C`
            : "-";
        conditionField.textContent = getWeatherCodeLabel(current.weather_code);
        windField.textContent = typeof current.wind_speed_10m === "number"
            ? `${current.wind_speed_10m} km/h`
            : "-";

        content.hidden = false;
    } catch (apiError) {
        error.hidden = false;
    } finally {
        loading.hidden = true;
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const yearSpan = document.getElementById("year");
    const departInput = document.getElementById("depart");
    const returnInput = document.getElementById("return");
    const destinationInput = document.getElementById("destination_id");
    const originCityInput = document.getElementById("origin_city_id");
    const passengersInput = document.getElementById("passengers");
    const guestBookingButton = document.getElementById("guestBookingButton");

    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }

    if (departInput && returnInput) {
        departInput.addEventListener("change", () => {
            returnInput.min = departInput.value;
            updateLivePrice();
        });

        returnInput.addEventListener("change", updateLivePrice);
    }

    if (destinationInput) {
        destinationInput.addEventListener("change", () => {
            updateDestinationDetails(true);
            updateWeatherInfoCard();
        });
    }

    if (originCityInput) {
        originCityInput.addEventListener("change", updateLivePrice);
    }

    if (passengersInput) {
        passengersInput.addEventListener("change", updateLivePrice);
    }

    if (guestBookingButton) {
        guestBookingButton.addEventListener("click", activateGuestLoginNotice);
    }

    updateDestinationDetails(false);
    updateLivePrice();

    updatePopupVisibility(
        document.getElementById("successPopup"),
        document.getElementById("closePopup")
    );

    updatePopupVisibility(
        document.getElementById("contactPopup"),
        document.getElementById("contactClose")
    );

    initializeBookingCancellation();
    updateWeatherInfoCard();
});