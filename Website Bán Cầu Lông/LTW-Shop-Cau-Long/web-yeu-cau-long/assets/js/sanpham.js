// Dữ liệu sản phẩm từ biến toàn cục được gán bên ngoài
const perPage = 20;

function renderProducts(page) {
    const list = document.querySelector(".badminton-list");
    list.innerHTML = "";

    const start = (page - 1) * perPage;
    const end = start + perPage;
    const items = window.products.slice(start, end);

    if (items.length > 0) {
        items.forEach((p) => {
            list.innerHTML += `
                <div class="badminton-item">
                    <div class="badminton-imgbox">
                        <a href="chi-tiet-san-pham.php?id=${p.id}">
                            <img src="${p.img}" alt="${p.name}" />
                            <span class="badminton-badge">Premium</span>
                        </a>
                    </div>
                    <div class="badminton-name" title="${p.name}">
                        <a href="chi-tiet-san-pham.php?id=${p.id}">${p.name}</a>
                    </div>
                    <div class="badminton-price">${p.price}</div>
                </div>
            `;
        });

        for (let i = items.length; i < perPage; i++) {
            list.innerHTML += `<div class="badminton-item empty"></div>`;
        }
    } else {
        list.innerHTML = '<div class="no-products">Không tìm thấy sản phẩm nào phù hợp.</div>';
    }
}

function renderPagination(page, totalPages) {
    const pag = document.querySelector(".badminton-pagination");
    let html = "";

    const currentParams = new URLSearchParams(window.location.search);
    currentParams.delete('page');

    function getPageLink(pageNum) {
        const newParams = new URLSearchParams(currentParams);
        newParams.set('page', pageNum);
        return window.location.pathname + '?' + newParams.toString();
    }

    html += `<a href="${getPageLink(1)}" class="page-btn first" ${page === 1 ? "disabled" : ""} title="Trang đầu"><i class="ri-arrow-left-double-line"></i></a>`;
    html += `<a href="${getPageLink(Math.max(1, page - 1))}" class="page-btn prev" ${page === 1 ? "disabled" : ""} title="Trang trước"><i class="ri-arrow-left-s-line"></i></a>`;

    let start = Math.max(1, page - 2);
    let end = Math.min(totalPages, page + 2);

    if (start > 1) {
        html += `<a href="${getPageLink(1)}" class="page-btn">1</a>`;
        if (start > 2) html += `<span class="page-ellipsis">...</span>`;
    }

    for (let i = start; i <= end; i++) {
        html += `<a href="${getPageLink(i)}" class="page-btn${i === page ? " active" : ""}">${i}</a>`;
    }

    if (end < totalPages) {
        if (end < totalPages - 1) html += `<span class="page-ellipsis">...</span>`;
        html += `<a href="${getPageLink(totalPages)}" class="page-btn">${totalPages}</a>`;
    }

    html += `<a href="${getPageLink(Math.min(totalPages, page + 1))}" class="page-btn next" ${page === totalPages ? "disabled" : ""} title="Trang sau"><i class="ri-arrow-right-s-line"></i></a>`;
    html += `<a href="${getPageLink(totalPages)}" class="page-btn last" ${page === totalPages ? "disabled" : ""} title="Trang cuối"><i class="ri-arrow-right-double-line"></i></a>`;

    pag.innerHTML = html;

    pag.querySelectorAll(".page-btn").forEach((btn) => {
        if (btn.disabled || btn.classList.contains("active")) return;
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            window.location.href = btn.href;
        });
    });
}

function goToPage(page) {
    const totalPages = Math.ceil(window.products.length / perPage) || 1;
    const currentParams = new URLSearchParams(window.location.search);
    currentParams.set('page', page);
    const newUrl = window.location.pathname + '?' + currentParams.toString();
    history.replaceState(null, '', newUrl);

    renderProducts(page);
    renderPagination(page, totalPages);
}

function getPageFromURL() {
    const params = new URLSearchParams(window.location.search);
    const page = parseInt(params.get('page'));
    return (page && page > 0) ? page : 1;
}

document.addEventListener("DOMContentLoaded", () => {
    goToPage(getPageFromURL());
});
