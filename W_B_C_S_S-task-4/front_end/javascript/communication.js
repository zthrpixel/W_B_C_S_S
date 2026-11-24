function escapeHtml(value) {
    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

async function apiFetch(url, options = {}) {
    const response = await fetch(url, options);
    let data;
    try {
        data = await response.json();
    } catch (error) {
        throw new Error("The server returned an invalid response.");
    }
    if (response.status === 401) {
        window.location.href = "index.php";
        throw new Error("Please log in again.");
    }
    if (!response.ok || data.success === false) {
        throw new Error(data.message || "Something went wrong.");
    }
    return data;
}

function showAlert(message, type = "danger") {
    document.getElementById("communicationAlert").innerHTML =
        `<div class="alert alert-${escapeHtml(type)}" role="alert">${escapeHtml(message)}</div>`;
}

let selectedCategoryId = "";

async function loadCategories() {
    const data = await apiFetch("../back_end/communication/categories.php");
    const categories = data.categories || data.data || [];
    const list = document.getElementById("categoryList");
    const select = document.getElementById("cardCategory");

    // Choose the default category first so it is highlighted on the first render.
    if (!selectedCategoryId && categories.length) selectedCategoryId = categories[0].category_id;

    list.innerHTML = categories.length ? categories.map((category) => `
        <div class="col-sm-6 col-md-4">
            <button type="button" class="card category-card w-100 h-100 p-3 ${selectedCategoryId == category.category_id ? "active" : ""}"
                data-category-id="${escapeHtml(category.category_id)}">
                <strong>${escapeHtml(category.category_name || "Category")}</strong>
            </button>
        </div>`).join("") : '<div class="col-12"><p class="alert alert-info">No categories found.</p></div>';

    select.innerHTML = '<option value="">Choose a category</option>';
    categories.forEach((category) => {
        const option = document.createElement("option");
        option.value = category.category_id;
        option.textContent = category.category_name;
        select.appendChild(option);
    });
    // Default the "add card" form to the category currently being viewed.
    select.value = selectedCategoryId;
}

async function loadCards() {
    if (!selectedCategoryId) {
        document.getElementById("cardList").innerHTML =
            '<div class="col-12"><p class="alert alert-info">Choose a category.</p></div>';
        return;
    }
    const data = await apiFetch("../back_end/communication/cards.php?category_id=" +
        encodeURIComponent(selectedCategoryId));
    const cards = data.cards || data.data || [];
    document.getElementById("cardList").innerHTML = cards.length ? cards.map((card) => `
        <div class="col-sm-6 col-md-4">
            <article class="card picture-card text-center p-3">
                <div class="picture-symbol mb-2">${escapeHtml(card.image_path || "💬")}</div>
                <h3 class="h5">${escapeHtml(card.card_name)}</h3>
                <button type="button" class="btn btn-success w-100 speak-card"
                    data-text="${escapeHtml(card.spoken_text || card.card_name)}">Speak</button>
                <button type="button" class="btn btn-outline-danger mt-2 w-100 delete-card"
                    data-card-id="${escapeHtml(card.card_id)}">Delete</button>
            </article>
        </div>`).join("") : '<div class="col-12"><p class="alert alert-info">No cards in this category.</p></div>';
}

document.getElementById("categoryList").addEventListener("click", (event) => {
    const button = event.target.closest("[data-category-id]");
    if (!button) return;
    selectedCategoryId = button.dataset.categoryId;
    document.querySelectorAll(".category-card").forEach((card) => card.classList.remove("active"));
    button.classList.add("active");
    loadCards().catch((error) => showAlert(error.message));
});

document.getElementById("cardList").addEventListener("click", async (event) => {
    const speakButton = event.target.closest(".speak-card");
    if (speakButton && "speechSynthesis" in window) {
        speechSynthesis.cancel();
        speechSynthesis.speak(new SpeechSynthesisUtterance(speakButton.dataset.text));
        return;
    }

    const deleteButton = event.target.closest(".delete-card");
    if (deleteButton && confirm("Delete this picture card?")) {
        try {
            await apiFetch("../back_end/communication/delete.php", {
                method: "POST",
                body: new URLSearchParams({ card_id: deleteButton.dataset.cardId })
            });
            showAlert("Picture card deleted.", "success");
            await loadCards();
        } catch (error) {
            showAlert(error.message);
        }
    }
});

document.getElementById("createCardForm").addEventListener("submit", async (event) => {
    event.preventDefault();
    const form = event.currentTarget;
    try {
        await apiFetch("../back_end/communication/create.php", {
            method: "POST",
            body: new FormData(form)
        });
        selectedCategoryId = form.category_id.value;
        form.reset();
        showAlert("Picture card added.", "success");
        await loadCategories();
        await loadCards();
    } catch (error) {
        showAlert(error.message);
    }
});

(async () => {
    try {
        await loadCategories();
        await loadCards();
    } catch (error) {
        showAlert(error.message);
    }
})();
