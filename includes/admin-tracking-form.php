<?php
/**
 * Admin Tracking Update Form Component
 * Include this in admin/order-detail.php
 * Advanced form with predefined statuses
 */

// Get all tracking statuses
require_once '../config/shipping.php';
$all_statuses = get_tracking_statuses();
$current_step = 0;

if(!empty($order['current_status_detail'])) {
    $current_status_info = get_tracking_status($order['current_status_detail']);
    $current_step = $current_status_info['step_order'] ?? 0;
}
?>

<div class="content-card" style="margin-bottom: 24px;">
    <div class="card-header" style="padding: 20px 24px; border-bottom: 1px solid var(--border); background: var(--bg-gray);">
        <h3 style="display: flex; align-items: center; gap: 10px; margin: 0; font-size: 18px; font-weight: 700;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
            </svg>
            Update Status Tracking
        </h3>
        <p style="color: var(--text-gray); font-size: 13px; margin: 8px 0 0 0;">
            Update status pengiriman dengan detail lokasi dan catatan untuk customer
        </p>
    </div>
    
    <div style="padding: 24px;">
        <form method="POST" id="trackingUpdateForm">
            <!-- Status Selection Grid -->
            <div class="tracking-form-group">
                <label>Pilih Status Tracking</label>
                <div class="status-select-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; margin-top: 12px;">
                    <?php while($status = mysqli_fetch_assoc($all_statuses)): 
                        $is_completed = $status['step_order'] < $current_step;
                        $is_current = $status['step_order'] == $current_step;
                        $is_next = $status['step_order'] == $current_step + 1;
                        $option_class = $is_completed ? 'completed' : ($is_current ? 'selected' : '');
                    ?>
                    <div class="status-option <?php echo $option_class; ?>" onclick="selectStatus('<?php echo $status['status_code']; ?>', <?php echo $status['step_order']; ?>, <?php echo $is_completed ? 'true' : 'false'; ?>)">
                        <input type="radio" 
                               name="status_detail" 
                               value="<?php echo $status['status_code']; ?>" 
                               id="status_<?php echo $status['status_code']; ?>"
                               <?php echo $is_current ? 'checked' : ''; ?>
                               style="display: none;">
                        
                        <div class="status-option-header" style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <div class="status-step" style="width: 24px; height: 24px; border-radius: 50%; background: <?php echo $is_completed ? 'var(--success)' : 'var(--border)'; ?>; color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700;">
                                <?php echo $status['step_order']; ?>
                            </div>
                            <div class="status-name" style="font-size: 13px; font-weight: 600; color: var(--text-dark); flex: 1;">
                                <?php echo htmlspecialchars($status['status_name_id']); ?>
                            </div>
                            <?php if($is_completed): ?>
                                <span class="status-badge-tag badge-completed" style="padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 600; display: inline-flex; align-items: center; gap: 2px;"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                            <?php elseif($is_next): ?>
                                <span class="status-badge-tag badge-next" style="background: #DBEAFE; color: #2563EB; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 600;">NEXT</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <!-- Location Input -->
            <div class="tracking-form-group">
                <label for="location">Lokasi Saat Ini</label>
                <input type="text" 
                       name="location" 
                       id="location" 
                       class="form-input" 
                       placeholder="Contoh: Jakarta Pusat Sorting Center"
                       value="<?php echo htmlspecialchars($order['current_location'] ?? ''); ?>"
                       style="width: 100%; padding: 12px 16px; border: 1.5px solid var(--border); border-radius: 6px; font-size: 14px;">
                <small style="color: var(--text-gray); font-size: 12px; margin-top: 4px; display: block;">
                    Masukkan lokasi terkini paket (hub, sorting center, atau dalam perjalanan)
                </small>
            </div>
            
            <!-- Tracking Number -->
            <div class="tracking-form-group">
                <label for="tracking_number">Nomor Resi / AWB</label>
                <input type="text" 
                       name="tracking_number" 
                       id="tracking_number" 
                       class="form-input" 
                       placeholder="Contoh: JP123456789ID"
                       value="<?php echo htmlspecialchars($order['tracking_number'] ?? ''); ?>"
                       style="width: 100%; padding: 12px 16px; border: 1.5px solid var(--border); border-radius: 6px; font-size: 14px; font-family: monospace;">
            </div>
            
            <!-- Courier Info -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="tracking-form-group">
                    <label for="courier_name">Nama Kurir (Opsional)</label>
                    <input type="text" 
                           name="courier_name" 
                           id="courier_name" 
                           class="form-input" 
                           placeholder="Contoh: Budi Santoso"
                           value="<?php echo htmlspecialchars($order['courier_name'] ?? ''); ?>"
                           style="width: 100%; padding: 12px 16px; border: 1.5px solid var(--border); border-radius: 6px; font-size: 14px;">
                </div>
                
                <div class="tracking-form-group">
                    <label for="courier_phone">No. Telepon Kurir (Opsional)</label>
                    <input type="tel" 
                           name="courier_phone" 
                           id="courier_phone" 
                           class="form-input" 
                           placeholder="0812-xxxx-xxxx"
                           value="<?php echo htmlspecialchars($order['courier_phone'] ?? ''); ?>"
                           style="width: 100%; padding: 12px 16px; border: 1.5px solid var(--border); border-radius: 6px; font-size: 14px;">
                </div>
            </div>
            
            <!-- Notes -->
            <div class="tracking-form-group">
                <label for="notes">Catatan Update (Opsional)</label>
                <textarea name="notes" 
                          id="notes" 
                          rows="3" 
                          class="form-input" 
                          placeholder="Tambahkan catatan tentang update status ini..."
                          style="width: 100%; padding: 12px 16px; border: 1.5px solid var(--border); border-radius: 6px; font-size: 14px; resize: vertical;"></textarea>
                <small style="color: var(--text-gray); font-size: 12px; margin-top: 4px; display: block;">
                    Catatan ini akan dikirim ke customer melalui notifikasi
                </small>
            </div>
            
            <!-- Estimated Delivery Date -->
            <div class="tracking-form-group">
                <label for="estimated_arrival">Estimasi Tiba</label>
                <input type="datetime-local" 
                       name="estimated_arrival" 
                       id="estimated_arrival" 
                       class="form-input"
                       value="<?php echo !empty($order['estimated_delivery_date']) ? date('Y-m-d\TH:i', strtotime($order['estimated_delivery_date'])) : ''; ?>"
                       style="width: 100%; padding: 12px 16px; border: 1.5px solid var(--border); border-radius: 6px; font-size: 14px;">
                <small style="color: var(--text-gray); font-size: 12px; margin-top: 4px; display: block;">
                    Akan otomatis dikalkulasi berdasarkan layanan pengiriman yang dipilih
                </small>
            </div>
            
            <!-- Notification Options -->
            <div class="tracking-form-group">
                <label style="margin-bottom: 12px; display: block;">Opsi Notifikasi</label>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal;">
                        <input type="checkbox" name="send_email" value="1" checked style="width: 18px; height: 18px; cursor: pointer;">
                        <span style="font-size: 14px; color: var(--text-dark);">Kirim email notifikasi ke customer</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal;">
                        <input type="checkbox" name="send_in_app" value="1" checked style="width: 18px; height: 18px; cursor: pointer;">
                        <span style="font-size: 14px; color: var(--text-dark);">Kirim notifikasi in-app</span>
                    </label>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" 
                        name="update_tracking" 
                        class="btn-action btn-view" 
                        style="flex: 1; padding: 14px; font-size: 15px; font-weight: 600; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Update & Kirim Notifikasi
                </button>
                
                <button type="button" 
                        onclick="previewTracking()" 
                        style="padding: 14px 24px; background: var(--bg-gray); color: var(--text-dark); border: 1px solid var(--border); border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                    Preview
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Status selection handling
let currentStepOrder = <?php echo $current_step; ?>;

function selectStatus(statusCode, stepOrder, isCompleted) {
    if (isCompleted) {
        alert('Status ini sudah dilewati. Tidak bisa kembali ke status sebelumnya.');
        return;
    }
    
    // Remove all selected classes
    document.querySelectorAll('.status-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    
    // Add selected class to clicked option
    event.currentTarget.classList.add('selected');
    
    // Check the radio button
    document.getElementById('status_' + statusCode).checked = true;
    
    // Auto-fill estimated arrival based on step
    if (stepOrder > currentStepOrder) {
        const daysToAdd = Math.max(1, stepOrder - currentStepOrder);
        const estimatedDate = new Date();
        estimatedDate.setDate(estimatedDate.getDate() + daysToAdd);
        
        const formattedDate = estimatedDate.toISOString().slice(0, 16);
        document.getElementById('estimated_arrival').value = formattedDate;
    }
}

// Preview tracking update
function previewTracking() {
    const selectedStatus = document.querySelector('input[name="status_detail"]:checked');
    const location = document.getElementById('location').value;
    const notes = document.getElementById('notes').value;
    
    if (!selectedStatus) {
        alert('Mohon pilih status tracking terlebih dahulu!');
        return;
    }
    
    const statusLabel = selectedStatus.closest('.status-option').querySelector('.status-name').textContent;
    
    let preview = 'Preview Update Tracking:\n\n';
    preview += '• Status: ' + statusLabel + '\n';
    if (location) preview += '• Lokasi: ' + location + '\n';
    if (notes) preview += '• Catatan: ' + notes + '\n';
    
    alert(preview);
}

// Form validation
document.getElementById('trackingUpdateForm').addEventListener('submit', function(e) {
    const selectedStatus = document.querySelector('input[name="status_detail"]:checked');
    if (!selectedStatus) {
        e.preventDefault();
        alert('Mohon pilih status tracking!');
        
        // Scroll to status selection
        document.querySelector('.status-select-grid').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>

<link rel="stylesheet" href="../assets/css/tracking.css">
