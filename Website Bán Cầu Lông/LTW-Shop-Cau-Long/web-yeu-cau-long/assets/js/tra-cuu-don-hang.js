document.addEventListener('DOMContentLoaded', () => {
    const cancelForms = document.querySelectorAll('.cancel-form');
    const messageContainer = document.getElementById('message-container');

    cancelForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!confirm('Bạn chắc chắn muốn hủy đơn hàng này?')) {
                return;
            }

            const formData = new FormData(form);
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                const message = document.createElement('p');
                message.className = data.success ? 'success-message' : 'error-message';
                message.textContent = data.message;

                // Xóa thông báo cũ và thêm thông báo mới
                messageContainer.innerHTML = '';
                messageContainer.appendChild(message);

                // Tự động ẩn thông báo sau 3 giây
                setTimeout(() => {
                    messageContainer.innerHTML = '';
                }, 3000);

                // Cập nhật bảng nếu hủy thành công
                if (data.success) {
                    const row = form.closest('tr');
                    const statusCell = row.querySelector('.status-cell');
                    const paymentStatusCell = row.querySelector('.payment-status-cell');
                    const actionCell = row.querySelector('.action-cell');

                    statusCell.textContent = data.new_status;
                    paymentStatusCell.textContent = data.new_payment_status;
                    actionCell.innerHTML = '<span class="status-cancelled">Đã hủy</span>';
                }
            } catch (error) {
                console.error('Lỗi:', error);
                const message = document.createElement('p');
                message.className = 'error-message';
                message.textContent = 'Có lỗi xảy ra khi hủy đơn hàng.';
                messageContainer.innerHTML = '';
                messageContainer.appendChild(message);
                setTimeout(() => {
                    messageContainer.innerHTML = '';
                }, 3000);
            }
        });
    });
});