const heroElement = document.querySelector(".heroSwiper");
if (heroElement) {
  new Swiper(".heroSwiper", {
    loop: true,
    autoHeight: true,
    speed: 1000,
    autoplay: {
      delay: 2500,
      disableOnInteraction: true,
    },
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    pagination: {
      el: ".swiper-pagination",
      dynamicBullets: true,
      clickable: true,
    },
  });
}

const infoElement = document.querySelector(".infoSlider");
if (infoElement) {
  new Swiper(".infoSlider", {
    loop: true,
    autoHeight: true,
    speed: 1000,
    autoplay: {
      delay: 2500,
      disableOnInteraction: true,
    },
  });
}

document.addEventListener("DOMContentLoaded", () => {
  const galleryConfig = window.HOME_GALLERY;
  const loadMoreBtn = document.getElementById("homeGalleryLoadMoreBtn");
  const loadMoreWrap = document.getElementById("homeGalleryLoadMoreWrap");
  const galleryGrid = document.getElementById("homeGalleryGrid");

  if (!galleryConfig || !loadMoreBtn || !galleryGrid) return;

  let offset = Number(galleryConfig.initialCount || 0);
  let isLoading = false;

  const updateButtonVisibility = () => {
    if (!loadMoreWrap) return;

    if (offset >= Number(galleryConfig.totalCount || 0)) {
      loadMoreWrap.style.display = "none";
    }
  };

  loadMoreBtn.addEventListener("click", async () => {
    if (isLoading) return;

    isLoading = true;
    loadMoreBtn.disabled = true;
    loadMoreBtn.textContent = "Loading...";

    try {
      const response = await fetch(
        `${galleryConfig.loadMoreUrl}?offset=${encodeURIComponent(offset)}`,
        {
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
          },
        }
      );

      if (!response.ok) {
        throw new Error("Failed to load more images.");
      }

      const data = await response.json();

      if (data.html) {
        galleryGrid.insertAdjacentHTML("beforeend", data.html);
      }

      offset = Number(data.next_offset || offset);

      if (!data.has_more || !data.loaded_count) {
        updateButtonVisibility();
      }
    } catch (error) {
      console.error("Home gallery load more failed:", error);
    } finally {
      isLoading = false;
      loadMoreBtn.disabled = false;
      loadMoreBtn.textContent = "Load More Images";
      updateButtonVisibility();
    }
  });

  updateButtonVisibility();
});
