function openImportModal(productId) {
    document.getElementById('import_product_id').value = productId;
    document.getElementById('importModal').style.display = 'block';
}

function closeImportModal() {
    document.getElementById('importModal').style.display = 'none';
}

function viewCustomerOrders(customerId) {
    openModal('customer-orders-modal');
    document.getElementById('customer-orders-body').innerHTML = '<p>Đang tải đơn hàng...</p>';

    fetch('admin.php?action=get_customer_orders&user_id=' + customerId)
        .then(response => response.text())
        .then(data => {
            document.getElementById('customer-orders-body').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('customer-orders-body').innerHTML = '<p>Lỗi khi tải đơn hàng.</p>';
            console.error('Error loading customer orders:', error);
        });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

function showConfirmDeleteModal(url) {
    openModal('confirm-delete-modal');
    const confirmButton = document.getElementById('confirm-delete-btn');
    if (confirmButton) {
        confirmButton.onclick = function() {
            window.location.href = url;
        };
    }
}

function openEditModal(product) {
    const editId = document.getElementById('edit-id');
    const editName = document.getElementById('edit-name');
    const editImage = document.getElementById('edit-image');
    const editCategory = document.getElementById('edit-category');
    const editPrice = document.getElementById('edit-price');
    const editStock = document.getElementById('edit-stock');
    const editDescription = document.getElementById('edit-description');

    if (editId && editName && editImage && editCategory && editPrice && editStock && editDescription) {
        editId.value = product.id;
        editName.value = product.name;
        editImage.value = product.image;
        editCategory.value = product.category;
        editPrice.value = product.price;
        editStock.value = product.stock;
        editDescription.value = product.description;
        openModal('edit-modal');
    }
}

function openEditOrderModal(orderId, currentStatus) {
    const editOrderId = document.getElementById('edit-order-id');
    const editOrderStatus = document.getElementById('edit-order-status');
    if (editOrderId && editOrderStatus) {
        editOrderId.value = orderId;
        editOrderStatus.value = currentStatus;
        openModal('edit-order-modal');
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        closeModal(event.target.id);
    }
};

// Chart scripts
let quickChart;
fetch('admin.php?action=quick_revenue_7days')
    .then(res => res.json())
    .then(data => {
        const canvas = document.getElementById('quickRevenueChart');
        if (!canvas) {
            console.error("Canvas 'quickRevenueChart' not found.");
            return;
        }
        quickChart = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Doanh thu',
                    data: data.revenue,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(227, 238, 227, 0.86)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    })
    .catch(error => {
        console.error('Error loading quick revenue:', error);
    });

let monthlyChart, categoriesChart;

function loadMonthlyRevenue(days = 365) {
    const canvas = document.getElementById('monthlyRevenueChart');
    if (!canvas) {
        console.error("Canvas 'monthlyRevenueChart' not found.");
        return;
    }

    fetch(`admin.php?action=monthly_revenue&days=${days}`)
        .then(res => {
            if (!res.ok) {
                throw new Error('Network response was not ok');
            }
            return res.json();
        })
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }
            if (monthlyChart) {
                monthlyChart.destroy();
            }
            monthlyChart = new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Doanh thu',
                        data: data.revenue,
                        backgroundColor: '#4CAF50'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading monthly revenue:', error);
        });
}

function loadTopCategories(days = 365) {
    const canvas = document.getElementById('topCategoriesChart');
    if (!canvas) {
        console.error("Canvas 'topCategoriesChart' not found.");
        return;
    }

    fetch('admin.php?action=top_categories&days=' + days)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }
            if (categoriesChart) {
                categoriesChart.destroy();
            }
            categoriesChart = new Chart(canvas.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.quantities,
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4CAF50', '#FF9800']
                    }]
                }
            });
        })
        .catch(error => {
            console.error('Error loading top categories:', error);
        });
}

function loadChartsIfReportsTab() {
    const reportsContent = document.getElementById('reports-content');
    const monthlyCanvas = document.getElementById('monthlyRevenueChart');
    const categoriesCanvas = document.getElementById('topCategoriesChart');
    if (reportsContent && reportsContent.style.display !== 'none' && monthlyCanvas && categoriesCanvas) {
        loadMonthlyRevenue(365);
        loadTopCategories(365);
    }
}

// Gắn sự kiện cho các nút chọn khoảng thời gian
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn[data-range]').forEach(button => {
        button.addEventListener('click', () => {
            const days = parseInt(button.getAttribute('data-range'));
            loadMonthlyRevenue(days);
            loadTopCategories(days);
        });
    });
    loadChartsIfReportsTab();
});

fetch('admin.php?action=growth_percent')
    .then(res => res.json())
    .then(data => {
        const growthDisplay = document.getElementById('growth-display');
        if (growthDisplay) {
            growthDisplay.innerText = data.growth + '%';
        }
    })
    .catch(error => {
        console.error('Error loading growth percent:', error);
    });