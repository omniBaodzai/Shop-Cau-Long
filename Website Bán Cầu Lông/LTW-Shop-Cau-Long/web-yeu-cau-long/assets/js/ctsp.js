document.addEventListener('DOMContentLoaded', () => {
  // Xử lý đánh giá sao
  const stars = document.querySelectorAll('.review-form-stars i');
  const ratingInput = document.getElementById('rating');
  let selectedRating = 0;

  stars.forEach((star, index) => {
    star.addEventListener('click', () => {
      selectedRating = index + 1;
      ratingInput.value = selectedRating;
      stars.forEach((s, i) => {
        if (i < selectedRating) {
          s.classList.add('active');
          s.classList.remove('ri-star-line');
          s.classList.add('ri-star-fill');
        } else {
          s.classList.remove('active', 'ri-star-fill', 'ri-star-half-fill');
          s.classList.add('ri-star-line');
        }
      });
    });

    star.addEventListener('mouseenter', () => {
      stars.forEach((s, i) => {
        if (i <= index) {
          s.classList.add('hover');
          s.classList.remove('ri-star-line');
          s.classList.add('ri-star-fill');
        } else {
          if (selectedRating === 0 || i >= selectedRating) {
            s.classList.remove('ri-star-fill', 'ri-star-half-fill');
            s.classList.add('ri-star-line');
          }
        }
      });
    });

    star.addEventListener('mouseleave', () => {
      stars.forEach((s, i) => {
        s.classList.remove('hover');
        if (i < selectedRating) {
          s.classList.add('active');
          s.classList.remove('ri-star-line');
          s.classList.add('ri-star-fill');
        } else {
          s.classList.remove('active', 'ri-star-fill', 'ri-star-half-fill');
          s.classList.add('ri-star-line');
        }
      });
    });
  });
});

function addToCart(productId, quantity) {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "gio-hang.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onload = function () {
    if (xhr.status === 200) {
      try {
        const response = JSON.parse(xhr.responseText);
        document.getElementById('cart-modal-product-name').textContent = response.product_name;
        document.getElementById('cart-modal-product-price').textContent = response.product_price + ' đ';
        document.getElementById('cart-modal-product-image').src = response.product_image;
        document.getElementById('cart-modal-total-items').textContent = response.total_items;
        document.getElementById('cart-modal').style.display = 'block';
      } catch (e) {
        console.error('Lỗi khi phân tích phản hồi:', e);
        alert('Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng. Vui lòng thử lại.');
      }
    } else {
      console.error('Yêu cầu thất bại với trạng thái:', xhr.status);
      alert('Không thể kết nối đến máy chủ. Vui lòng thử lại.');
    }
  };

  xhr.send(`action=add_to_cart&product_id=${productId}&quantity=${quantity}`);
}

function closeCartModal() {
  document.getElementById('cart-modal').style.display = 'none';
}

function showTab(tab) {
  document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
  document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
  document.querySelector('.tab-btn[onclick*="' + tab + '"]').classList.add('active');
  document.getElementById(tab).classList.add('active');
}

function changeQty(amount) {
  const qtyInput = document.getElementById('qty');
  let currentQty = parseInt(qtyInput.value, 10);
  const minQty = parseInt(qtyInput.min, 10);
  const maxQty = parseInt(qtyInput.max, 10);

  currentQty += amount;

  if (currentQty >= minQty && currentQty <= maxQty) {
    qtyInput.value = currentQty;
  }
}

function syncQty(form) {
  const qtyInput = document.getElementById('qty');
  const hiddenQtyInput = form.querySelector('input[name="quantity"]');
  hiddenQtyInput.value = qtyInput.value;
}