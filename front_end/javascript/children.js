// Fill the Edit Child modal with the details of the profile that was clicked.
document.addEventListener("click", (event) => {
    const button = event.target.closest(".edit-child");
    if (!button) return;

    document.getElementById("editChildId").value = button.dataset.childId;
    document.getElementById("editFirstName").value = button.dataset.firstName;
    document.getElementById("editLastName").value = button.dataset.lastName;
    document.getElementById("editDob").value = button.dataset.dob;
    document.getElementById("editGender").value = button.dataset.gender;
    document.getElementById("editDomain").value = button.dataset.domain;
    document.getElementById("editNotes").value = button.dataset.notes;
});
