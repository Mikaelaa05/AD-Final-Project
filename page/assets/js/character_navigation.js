// filepath: d:\School Files\FEU Tech School Files\FEU Tech\2nd Year\3rd Sem\App Dev LEC\Repos\AD-Task-1\page\assets\js\character_navigation.js

const images = {
    hitori: [
        "gotoh_hitori_1.jpg",
        "gotoh_hitori_2.jpg",
        "gotoh_hitori_3.jpg"
    ],
    kita: [
        "ikuyo_kita_1.jpg",
        "ikuyo_kita_2.jpg",
        "ikuyo_kita_3.jpg"
    ],
    nijika: [
        "ichiji_nijika_1.jpg",
        "ichiji_nijika_2.jpg",
        "ichiji_nijika_3.jpg"
    ],
    ryo: [
        "yamada_ryo_1.jpg",
        "yamada_ryo_2.jpg",
        "yamada_ryo_3.jpg"
    ]
};

const currentPage = window.location.pathname.split("/").pop().split(".")[0];

if (images[currentPage]) {
    let currentIndex = 0;

    function changeImage(direction) {
        const imageElement = document.getElementById("character-image");
        const characterImages = images[currentPage];

        if (direction === "left") {
            currentIndex = (currentIndex - 1 + characterImages.length) % characterImages.length;
        } else if (direction === "right") {
            currentIndex = (currentIndex + 1) % characterImages.length;
        }

        imageElement.src = `${BASE_URL}assets/img/${characterImages[currentIndex]}`;
    }
}