function showAdminMainLoader() {
    const loader = document.querySelector(".preloder");

    if (loader) {
        loader.style.display = "grid";
    }
}

function hideAdminMainLoader() {
    const loader = document.querySelector(".preloder");

    if (loader) {
        loader.style.display = "none";
    }
}
