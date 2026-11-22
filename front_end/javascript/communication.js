// communication.js - JavaScript for communication.php
// it uses the PHP files in back_end/communication/

var API = "../back_end/communication/";

var categories = [];      // all categories
var cards = [];           // all cards
var currentCategory = 0;  // the category that is open in child mode
var sentence = [];        // the cards the child tapped


// makes text safe before putting it inside HTML
function safe(text) {
    var div = document.createElement("div");
    div.textContent = text == null ? "" : text;
    return div.innerHTML;
}

// go back to login if the session expired
async function checkResponse(res) {
    if (res.status === 401) {
        window.location.href = "index.php";
        return { success: false };
    }
    try {
        return await res.json();
    } catch (e) {
        return { success: false, message: "Server error" };
    }
}

async function getData(file) {
    var res = await fetch(API + file, { credentials: "same-origin" });
    return checkResponse(res);
}

async function postData(file, data) {
    var res = await fetch(API + file, {
        method: "POST",
        body: new URLSearchParams(data),
        credentials: "same-origin"
    });
    return checkResponse(res);
}

function showMessage(text, type) {
    document.getElementById("message").innerHTML =
        '<div class="alert alert-' + type + '">' + safe(text) + "</div>";
}


/* ---------- speaking ---------- */

function speak(text) {
    if (!("speechSynthesis" in window)) {
        showMessage("Your browser can't speak. Try Chrome or Edge.", "warning");
        return;
    }
    window.speechSynthesis.cancel(); // stop anything that is still speaking

    var voice = new SpeechSynthesisUtterance(text);
    voice.lang = document.getElementById("voiceLang").value;
    voice.rate = parseFloat(document.getElementById("voiceSpeed").value);
    window.speechSynthesis.speak(voice);
}

// the word the board says for a card
function wordOf(card) {
    return card.spoken_text ? card.spoken_text : card.card_name;
}


/* ---------- pictures ---------- */

// shows an image if the path looks like an image file, if not shows it as text (emoji)
function pictureHtml(card) {
    var path = card.image_path || "";
    if (/\.(png|jpe?g|gif|svg|webp)$/i.test(path)) {
        return '<img src="' + safe(path) + '" alt="">';
    }
    return safe(path);
}


/* ---------- loading data ---------- */

async function loadData() {
    var catData = await getData("categories.php");
    var cardData = await getData("cards.php");

    if (!catData.success || !cardData.success) {
        showMessage(catData.message || cardData.message || "Could not load the board", "danger");
        return;
    }

    categories = catData.categories;
    cards = cardData.cards;

    // keep the open category if it still exists, if not open the first one
    var found = categories.some(function (c) {
        return c.category_id == currentCategory;
    });
    if (!found) {
        currentCategory = categories.length > 0 ? categories[0].category_id : 0;
    }

    showCategoryTabs();
    showCards();
    showCategoryDropdown();
    showManageList();
}


/* ---------- child mode ---------- */

function showCategoryTabs() {
    var html = "";
    categories.forEach(function (c) {
        var style = c.category_id == currentCategory ? "btn-warning" : "btn-outline-dark";
        html += '<button class="btn ' + style + ' btn-lg" data-category="' + c.category_id + '">'
              + safe(c.category_name) + "</button>";
    });
    document.getElementById("categoryTabs").innerHTML = html;
}

function showCards() {
    var html = "";
    cards.forEach(function (card) {
        if (card.category_id != currentCategory) {
            return;
        }
        html += '<div class="col-6 col-md-3">';
        html += '<button class="picture-card w-100 p-2" data-card="' + card.card_id + '">';
        html += '<div class="picture">' + pictureHtml(card) + "</div>";
        html += "<div>" + safe(card.card_name) + "</div>";
        html += "</button></div>";
    });

    if (html === "") {
        html = '<p class="text-muted">No cards in this category yet.</p>';
    }
    document.getElementById("cardGrid").innerHTML = html;
}

function showSentence() {
    var html = "";
    sentence.forEach(function (card) {
        html += '<span class="sentence-word">' + pictureHtml(card) + " " + safe(card.card_name) + "</span>";
    });
    document.getElementById("sentenceWords").innerHTML = html;
}

// tap a card: say the word and add it to the sentence
function tapCard(cardId) {
    var card = cards.find(function (c) {
        return c.card_id == cardId;
    });
    if (!card) {
        return;
    }
    sentence.push(card);
    showSentence();
    speak(wordOf(card));
}

// say the whole sentence
function speakSentence() {
    if (sentence.length === 0) {
        return;
    }
    var words = sentence.map(wordOf);
    speak("I want " + words.join(" and "));
}


/* ---------- caregiver mode ---------- */

function showCategoryDropdown() {
    var html = "";
    categories.forEach(function (c) {
        html += '<option value="' + c.category_id + '">' + safe(c.category_name) + "</option>";
    });
    document.getElementById("cardCategory").innerHTML = html;
}

function showManageList() {
    var box = document.getElementById("manageList");
    if (categories.length === 0) {
        box.innerHTML = '<p class="text-muted">No categories yet. Add one first.</p>';
        return;
    }

    var html = "";
    categories.forEach(function (c) {
        html += '<div class="d-flex justify-content-between align-items-center mt-3">';
        html += '<strong>' + safe(c.category_name) + "</strong>";
        html += '<button class="btn btn-sm btn-outline-danger" data-delete="category" data-id="' + c.category_id + '">Delete Category</button>';
        html += "</div>";

        cards.forEach(function (card) {
            if (card.category_id != c.category_id) {
                return;
            }
            html += '<div class="d-flex justify-content-between align-items-center border-bottom py-2">';
            html += "<span>" + pictureHtml(card) + " " + safe(card.card_name) + "</span>";
            html += '<button class="btn btn-sm btn-outline-danger" data-delete="card" data-id="' + card.card_id + '">Delete</button>';
            html += "</div>";
        });
    });
    box.innerHTML = html;
}

// switch between child mode and caregiver mode
function setMode(mode) {
    var isChild = mode === "child";
    document.getElementById("childMode").style.display = isChild ? "block" : "none";
    document.getElementById("caregiverMode").style.display = isChild ? "none" : "block";
    document.getElementById("childModeBtn").className = isChild ? "btn btn-primary" : "btn btn-outline-primary";
    document.getElementById("caregiverModeBtn").className = isChild ? "btn btn-outline-primary" : "btn btn-primary";
}


/* ---------- events ---------- */

document.addEventListener("DOMContentLoaded", function () {

    document.getElementById("childModeBtn").addEventListener("click", function () {
        setMode("child");
    });
    document.getElementById("caregiverModeBtn").addEventListener("click", function () {
        setMode("caregiver");
    });

    // choose a category
    document.getElementById("categoryTabs").addEventListener("click", function (e) {
        var button = e.target.closest("button");
        if (!button) {
            return;
        }
        currentCategory = button.dataset.category;
        showCategoryTabs();
        showCards();
    });

    // tap a card
    document.getElementById("cardGrid").addEventListener("click", function (e) {
        var button = e.target.closest("button");
        if (button) {
            tapCard(button.dataset.card);
        }
    });

    document.getElementById("speakBtn").addEventListener("click", speakSentence);
    document.getElementById("clearBtn").addEventListener("click", function () {
        sentence = [];
        showSentence();
    });

    // add a category
    document.getElementById("categoryForm").addEventListener("submit", async function (e) {
        e.preventDefault();
        var result = await postData("create.php", {
            type: "category",
            category_name: document.getElementById("category_name").value
        });
        showMessage(result.message, result.success ? "success" : "danger");
        if (result.success) {
            document.getElementById("category_name").value = "";
            loadData();
        }
    });

    // add a card
    document.getElementById("cardForm").addEventListener("submit", async function (e) {
        e.preventDefault();
        var result = await postData("create.php", {
            type: "card",
            category_id: document.getElementById("cardCategory").value,
            card_name: document.getElementById("card_name").value,
            image_path: document.getElementById("image_path").value,
            spoken_text: document.getElementById("spoken_text").value
        });
        showMessage(result.message, result.success ? "success" : "danger");
        if (result.success) {
            document.getElementById("card_name").value = "";
            document.getElementById("image_path").value = "";
            document.getElementById("spoken_text").value = "";
            loadData();
        }
    });

    // delete a category or a card
    document.getElementById("manageList").addEventListener("click", async function (e) {
        var button = e.target.closest("button");
        if (!button) {
            return;
        }
        var type = button.dataset.delete;
        var question = type === "category"
            ? "Delete this category and all of its cards?"
            : "Delete this card?";
        if (!confirm(question)) {
            return;
        }
        var result = await postData("delete.php", { type: type, id: button.dataset.id });
        showMessage(result.message, result.success ? "success" : "danger");
        if (result.success) {
            sentence = [];
            showSentence();
            loadData();
        }
    });

    document.getElementById("testVoiceBtn").addEventListener("click", function () {
        speak("Hello, this is my voice");
    });

    loadData();
});
