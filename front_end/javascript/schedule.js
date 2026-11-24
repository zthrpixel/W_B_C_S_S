document.addEventListener("DOMContentLoaded", () => {

    // --- Search: hide schedule cards that don't match the typed text ---
    const searchInput = document.getElementById("searchSchedule");
    const noMatches = document.getElementById("noScheduleMatches");

    if (searchInput) {
        searchInput.addEventListener("input", () => {
            const query = searchInput.value.trim().toLowerCase();
            const cards = document.querySelectorAll(".schedule-col");
            let visible = 0;

            cards.forEach((card) => {
                const matches = card.textContent.toLowerCase().includes(query);
                card.hidden = !matches;
                if (matches) visible++;
            });

            if (noMatches) noMatches.hidden = visible > 0 || cards.length === 0;
        });
    }

    // --- Create form: add another time + activity row ---
    const addRowButton = document.getElementById("addActivityRow");
    const rowContainer = document.getElementById("activityRows");

    if (addRowButton && rowContainer) {
        addRowButton.addEventListener("click", () => {
            const row = document.createElement("div");
            row.className = "row g-2 mb-2 activity-row";
            row.innerHTML = `
                <div class="col-md-4">
                    <input type="time" name="activity_time[]" class="form-control" aria-label="Activity time">
                </div>
                <div class="col-md-8">
                    <input type="text" name="activity_name[]" class="form-control" maxlength="150"
                           placeholder="Activity name" aria-label="Activity name">
                </div>`;
            rowContainer.appendChild(row);
            row.querySelector("input[type=time]").focus();
        });
    }

    // --- Edit modal: fill the form from the clicked schedule ---
    document.addEventListener("click", (event) => {
        const button = event.target.closest(".edit-schedule");
        if (!button) return;

        document.getElementById("editScheduleId").value = button.dataset.scheduleId;
        document.getElementById("editScheduleName").value = button.dataset.name;
        document.getElementById("editScheduleDate").value = button.dataset.date;
        document.getElementById("editScheduleDescription").value = button.dataset.description;
    });

});
