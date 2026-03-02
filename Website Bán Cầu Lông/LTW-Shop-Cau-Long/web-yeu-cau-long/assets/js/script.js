document.addEventListener("DOMContentLoaded", () => {
  // Hàm debounce để giới hạn tần suất gọi hàm
  const debounce = (fn, delay) => {
    let timeout;
    return (...args) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => fn(...args), delay);
    };
  };

  // 1. Mega Dropdown Menu
  const megaDropdown = document.querySelector(".mega-dropdown");
  if (megaDropdown) {
    megaDropdown.addEventListener("mouseenter", () => {
      megaDropdown.classList.add("mega-menu-active");
      megaDropdown.setAttribute("aria-expanded", "true");
    });
    megaDropdown.addEventListener("mouseleave", () => {
      megaDropdown.classList.remove("mega-menu-active");
      megaDropdown.setAttribute("aria-expanded", "false");
    });
  }

  // 2. Search Suggestions
  const searchInput = document.getElementById("main-search-input");
  const suggestionsBox = document.getElementById("search-suggestions");

  if (searchInput && suggestionsBox) {
    // Hàm xử lý tìm kiếm gợi ý
    const fetchSuggestions = debounce((query) => {
      if (query.length === 0) {
        suggestionsBox.innerHTML = "";
        suggestionsBox.style.display = "none";
        return;
      }

      fetch(`http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/get-suggestions.php?q=${encodeURIComponent(query)}`)
        .then((res) => {
          if (!res.ok) throw new Error("Lỗi khi tải gợi ý");
          return res.json();
        })
        .then((data) => {
          suggestionsBox.innerHTML = "";
          if (Array.isArray(data) && data.length > 0) {
            suggestionsBox.innerHTML = data
              .map(
                (item) => `
                  <div class="suggestion-item" data-id="${item.id}" role="option" aria-label="${item.name}">
                    <img src="${item.image}" alt="${item.name}" class="suggestion-img" loading="lazy" />
                    <span class="suggestion-name">${item.name}</span>
                  </div>
                `
              )
              .join("");
            suggestionsBox.style.display = "block";
          } else {
            suggestionsBox.innerHTML = '<div class="suggestion-empty">Không tìm thấy sản phẩm</div>';
            suggestionsBox.style.display = "block";
          }
        })
        .catch((error) => {
          suggestionsBox.innerHTML = '<div class="suggestion-error">Lỗi khi tải gợi ý, vui lòng thử lại</div>';
          suggestionsBox.style.display = "block";
          console.error("Lỗi tìm kiếm:", error);
        });
    }, 200);

    // Sự kiện khi nhập vào ô tìm kiếm
    searchInput.addEventListener("input", function () {
      fetchSuggestions(this.value.trim());
    });

    // Ẩn gợi ý khi mất focus
    searchInput.addEventListener("blur", () => {
      setTimeout(() => {
        suggestionsBox.style.display = "none";
      }, 150);
    });

    // Hiển thị lại gợi ý khi focus
    searchInput.addEventListener("focus", () => {
      if (suggestionsBox.innerHTML.trim() !== "") {
        suggestionsBox.style.display = "block";
      }
    });

    // Hỗ trợ điều hướng bằng bàn phím
    searchInput.addEventListener("keydown", (e) => {
      const suggestions = suggestionsBox.querySelectorAll(".suggestion-item");
      if (suggestions.length === 0) return;

      let activeIndex = Array.from(suggestions).findIndex((s) => s.classList.contains("active"));

      if (e.key === "ArrowDown") {
        e.preventDefault();
        activeIndex = activeIndex < suggestions.length - 1 ? activeIndex + 1 : 0;
        suggestions.forEach((s) => s.classList.remove("active"));
        suggestions[activeIndex].classList.add("active");
        suggestions[activeIndex].scrollIntoView({ block: "nearest" });
      } else if (e.key === "ArrowUp") {
        e.preventDefault();
        activeIndex = activeIndex > 0 ? activeIndex - 1 : suggestions.length - 1;
        suggestions.forEach((s) => s.classList.remove("active"));
        suggestions[activeIndex].classList.add("active");
        suggestions[activeIndex].scrollIntoView({ block: "nearest" });
      } else if (e.key === "Enter" && activeIndex >= 0) {
        e.preventDefault();
        const id = suggestions[activeIndex].getAttribute("data-id");
        if (id) {
          window.location.href = `http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/chi-tiet-san-pham.php?id=${id}`;
        }
      }
    });

    // Xử lý click vào gợi ý
    suggestionsBox.addEventListener("mousedown", (e) => {
      const item = e.target.closest(".suggestion-item");
      if (item) {
        const id = item.getAttribute("data-id");
        if (id) {
          window.location.href = `http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/chi-tiet-san-pham.php?id=${id}`;
        }
      }
    });
  }

  // 3. Category-Based Product Listing
  const tabs = document.querySelectorAll(".category-tabs .tab");
  const productList = document.querySelector(".product-list");

  if (tabs && productList) {
    const loadProducts = (category) => {
      productList.innerHTML = "<p>Đang tải sản phẩm...</p>";
      fetch(`./pages/get-products.php?category=${encodeURIComponent(category)}`)
        .then((response) => {
          if (!response.ok) throw new Error("Lỗi khi tải sản phẩm");
          return response.json();
        })
        .then((data) => {
          productList.innerHTML = "";
          if (Array.isArray(data) && data.length > 0) {
            const html = data
              .map(
                (product) => `
                  <div class="product-card" data-id="${product.id}" style="cursor: pointer;" role="article" aria-label="${product.name}">
                    <img src="${product.image}" alt="${product.name}" class="product-image" loading="lazy" />
                    <h3 class="product-name">${product.name}</h3>
                    <p class="product-price">${product.price.toLocaleString()} đ</p>
                  </div>
                `
              )
              .join("");
            productList.innerHTML = html;

            // Gắn sự kiện click cho các product card
            document.querySelectorAll(".product-card").forEach((card) => {
              card.addEventListener("click", () => {
                const productId = card.getAttribute("data-id");
                window.location.href = `pages/chi-tiet-san-pham.php?id=${productId}`;
              });
            });
          } else {
            productList.innerHTML = "<p>Không có sản phẩm nào.</p>";
          }
        })
        .catch((error) => {
          productList.innerHTML = "<p>Đã xảy ra lỗi khi tải sản phẩm, vui lòng thử lại.</p>";
          console.error("Lỗi tải sản phẩm:", error);
        });
    };

    // Gắn sự kiện click cho các tab danh mục
    tabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        tabs.forEach((t) => t.classList.remove("active"));
        tab.classList.add("active");
        const category = tab.getAttribute("category");
        loadProducts(category);
      });
    });

    // Tải sản phẩm ban đầu
    loadProducts("all");
  }
});