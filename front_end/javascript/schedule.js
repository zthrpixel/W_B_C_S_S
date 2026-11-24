document.addEventListener("DOMContentLoaded", () => {

    const deleteButtons =
    document.querySelectorAll(".delete-btn");

    deleteButtons.forEach(button => {

        button.addEventListener("click", () => {

            const confirmed =
            confirm(
                "Are you sure you want to delete this schedule?"
            );

            if(!confirmed)
            {
                return;
            }

        });

    });

});
