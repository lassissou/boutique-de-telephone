document.addEventListener("DOMContentLoaded", function () {
    const carousels = document.querySelectorAll(".liste-produits, .liste-promotions");

    carousels.forEach(carousel => {
        let isDown = false;
        let startX;
        let scrollLeft;

        carousel.addEventListener("mousedown", (e) => {
            isDown = true;
            carousel.classList.add("active");
            startX = e.pageX - carousel.offsetLeft;
            scrollLeft = carousel.scrollLeft;
        });

        carousel.addEventListener("mouseleave", () => {
            isDown = false;
            carousel.classList.remove("active");
        });

        carousel.addEventListener("mouseup", () => {
            isDown = false;
            carousel.classList.remove("active");
        });

        carousel.addEventListener("mousemove", (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - carousel.offsetLeft;
            const walk = (x - startX) * 2; // Adjust scroll speed
            carousel.scrollLeft = scrollLeft - walk;
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const produits = document.querySelectorAll(".produit");

    produits.forEach(produit => {
        const prix = produit.querySelector(".prix");

        produit.addEventListener("mouseenter", () => {
            if (prix) {
                prix.style.display = "block"; // Affiche le prix
            }
        });

        produit.addEventListener("mouseleave", () => {
            if (prix) {
                prix.style.display = "none"; // Cache le prix
            }
        });
    });
});