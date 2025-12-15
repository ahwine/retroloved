<?php
/**
 * Shipping Service Selection Component
 * Include this in checkout.php
 * Consistent with RetroLoved design system
 */

// Get all active shipping services
require_once '../config/shipping.php';
$shipping_services = get_shipping_services();
?>

<div class="form-card" style="margin-bottom: 24px;">
    <div class="form-card-header">
        <h3 style="display: flex; align-items: center; gap: 10px; margin: 0;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="3" width="15" height="13"></rect>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                <circle cx="18.5" cy="18.5" r="2.5"></circle>
            </svg>
            Pilih Layanan Pengiriman
        </h3>
        <p style="color: var(--text-gray); font-size: 14px; margin: 8px 0 0 0;">
            Pilih ekspedisi dan layanan pengiriman yang sesuai dengan kebutuhan Anda
        </p>
    </div>
    
    <div style="padding: 24px;">
        <div class="shipping-services-grid">
            <?php if(mysqli_num_rows($shipping_services) > 0): ?>
                <?php while($service = mysqli_fetch_assoc($shipping_services)): ?>
                    <div class="shipping-service-card" onclick="selectShipping(<?php echo $service['service_id']; ?>, <?php echo $service['base_cost']; ?>)">
                        <input type="radio" 
                               name="shipping_service_id" 
                               value="<?php echo $service['service_id']; ?>" 
                               id="shipping_<?php echo $service['service_id']; ?>"
                               data-cost="<?php echo $service['base_cost']; ?>"
                               style="display: none;"
                               required>
                        
                        <div class="shipping-service-header">
                            <div class="shipping-radio"></div>
                            
                            <div class="courier-info">
                                <div class="courier-name">
                                    <?php echo htmlspecialchars($service['courier_name']); ?> - <?php echo htmlspecialchars($service['service_name']); ?>
                                    
                                    <?php if($service['base_cost'] == 0): ?>
                                        <span class="service-badge badge-free">GRATIS ONGKIR</span>
                                    <?php elseif($service['estimated_days_max'] <= 2): ?>
                                        <span class="service-badge badge-fast">CEPAT</span>
                                    <?php elseif($service['base_cost'] < 15000): ?>
                                        <span class="service-badge badge-cheap">HEMAT</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="service-details">
                                    <span class="service-price">
                                        Rp <?php echo number_format($service['base_cost'], 0, ',', '.'); ?>
                                    </span>
                                    
                                    <span class="service-duration">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        <?php 
                                        if($service['estimated_days_min'] == 0 && $service['estimated_days_max'] == 0) {
                                            echo 'Instant - Ambil Langsung';
                                        } elseif($service['estimated_days_min'] == $service['estimated_days_max']) {
                                            echo $service['estimated_days_min'] . ' hari';
                                        } else {
                                            echo $service['estimated_days_min'] . '-' . $service['estimated_days_max'] . ' hari';
                                        }
                                        ?>
                                    </span>
                                </div>
                                
                                <?php if(!empty($service['description'])): ?>
                                    <p class="service-description">
                                        <?php echo htmlspecialchars($service['description']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: var(--text-gray);">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-bottom: 12px; opacity: 0.3;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <p>Belum ada layanan pengiriman tersedia</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Hidden inputs for form submission -->
        <input type="hidden" name="subtotal" id="subtotalInput" value="<?php echo $total; ?>">
        <input type="hidden" name="shipping_cost" id="shippingCostInput" value="0">
        <input type="hidden" name="total_amount" id="totalAmountInput" value="<?php echo $total; ?>">
    </div>
</div>

<script>
// Shipping selection functionality
let currentSubtotal = <?php echo $total; ?>;
let currentShippingCost = 0;

function selectShipping(serviceId, shippingCost) {
    // Update radio button
    const radio = document.getElementById('shipping_' + serviceId);
    if (radio) {
        radio.checked = true;
    }
    
    // Update visual selection
    document.querySelectorAll('.shipping-service-card').forEach(card => {
        card.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
    
    // Update pricing
    currentShippingCost = shippingCost;
    updatePricing();
    
    // Remove any validation errors
    const shippingSection = document.querySelector('.shipping-services');
    if (shippingSection) {
        shippingSection.style.border = 'none';
    }
}

function updatePricing() {
    const total = currentSubtotal + currentShippingCost;
    
    // Update order summary (right sidebar)
    const shippingCostElement = document.getElementById('orderSummaryShippingCost');
    const totalElement = document.getElementById('orderSummaryTotal');
    const totalValueElement = document.getElementById('orderSummaryTotalValue');
    
    if (shippingCostElement) {
        if (currentShippingCost === 0) {
            shippingCostElement.innerHTML = '<span class="free-shipping-badge">Gratis</span>';
        } else {
            shippingCostElement.textContent = 'Rp ' + formatNumber(currentShippingCost);
        }
    }
    
    if (totalElement) {
        totalElement.textContent = 'Rp ' + formatNumber(total);
    }
    
    if (totalValueElement) {
        totalValueElement.textContent = 'Rp ' + formatNumber(total);
    }
    
    // Update hidden inputs
    document.getElementById('shippingCostInput').value = currentShippingCost;
    document.getElementById('totalAmountInput').value = total;
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Validasi pilihan pengiriman sebelum checkout
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.querySelector('form[name="checkout_form"]');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const shippingSelected = document.querySelector('input[name="shipping_service_id"]:checked');
            if (!shippingSelected) {
                e.preventDefault();
                if (typeof toastWarning === 'function') {
                    toastWarning('Mohon pilih layanan pengiriman terlebih dahulu!');
                } else {
                    console.error('toastWarning tidak tersedia');
                }
                
                // Scroll to shipping section
                const shippingSection = document.querySelector('.shipping-services-grid');
                if (shippingSection) {
                    shippingSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    shippingSection.parentElement.style.border = '2px solid #EF4444';
                    shippingSection.parentElement.style.borderRadius = '8px';
                    shippingSection.parentElement.style.padding = '16px';
                    setTimeout(() => {
                        shippingSection.parentElement.style.border = 'none';
                        shippingSection.parentElement.style.padding = '24px';
                    }, 3000);
                }
            }
        });
    }
});
</script>

<style>
/* Additional inline styles for consistency */
.form-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 24px;
}

.form-card-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
    background: var(--bg-gray);
}

.form-card-header h3 {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-dark);
}

.form-card-header svg {
    color: var(--primary);
}
</style>
