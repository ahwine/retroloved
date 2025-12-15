<?php
/**
 * Order Tracking Timeline Component
 * Menampilkan timeline tracking pesanan
 * RetroLoved E-Commerce System
 */

// Get tracking history for this order
require_once '../config/shipping.php';
$tracking_history = get_order_tracking_history($order['order_id']);
$current_status = $order['current_status_detail'] ?? 'order_placed';

// Calculate progress based on main order status
$order_status = trim($order['status']);
$progress = calculate_tracking_progress($order_status);
?>

<div style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    <!-- Header -->
    <h2 style="font-size: 24px; font-weight: 800; margin-bottom: 8px; display: flex; align-items: center; gap: 12px; color: #1F2937;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <rect x="1" y="3" width="15" height="13"></rect>
            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
            <circle cx="5.5" cy="18.5" r="2.5"></circle>
            <circle cx="18.5" cy="18.5" r="2.5"></circle>
        </svg>
        Tracking Order
    </h2>
    <p style="color: #6B7280; font-size: 14px; margin-bottom: 32px;">Monitor pengiriman paket Anda secara real-time</p>
    
    <div>
        <!-- Shipping Info Card -->
        <?php if(!empty($order['tracking_number'])): ?>
        <div style="background: linear-gradient(135deg, #E0E7FF 0%, #C7D2FE 100%); border-radius: 12px; padding: 24px; margin-bottom: 32px; border: 2px solid #6366F1;">
            <div style="display: grid; grid-template-columns: 1fr auto; gap: 20px; align-items: center;">
                <div>
                    <div style="font-size: 11px; color: #4338CA; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <?php if(!empty($order['courier_name'])): ?>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                            <?php echo htmlspecialchars($order['courier_name']); ?>
                        <?php else: ?>
                            Tracking Number
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 24px; font-weight: 800; color: #312E81; letter-spacing: 1px; font-family: 'Courier New', monospace;">
                        <?php echo htmlspecialchars($order['tracking_number']); ?>
                    </div>
                    <?php if(!empty($order['courier_phone'])): ?>
                    <div style="margin-top: 8px; display: flex; align-items: center; gap: 6px; font-size: 13px; color: #4338CA;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        <strong>Kurir: <?php echo htmlspecialchars($order['courier_phone']); ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if(!empty($order['current_location'])): ?>
                <div style="background: white; border-radius: 10px; padding: 16px; text-align: center; min-width: 180px;">
                    <div style="font-size: 10px; color: #6B7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                        Lokasi Terkini
                    </div>
                    <div style="font-size: 13px; font-weight: 700; color: #1F2937; display: flex; align-items: center; justify-content: center; gap: 4px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <?php echo htmlspecialchars($order['current_location']); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Progress Bar -->
        <div style="margin-bottom: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-size: 13px; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 1px;">
                    Shipping Progress
                </span>
                <span style="font-size: 18px; font-weight: 800; color: #D97706;">
                    <?php echo $progress; ?>%
                </span>
            </div>
            
            <div style="height: 8px; background: #E5E7EB; border-radius: 10px; overflow: hidden; position: relative;">
                <div style="height: 100%; background: linear-gradient(90deg, #F59E0B 0%, #D97706 100%); width: <?php echo $progress; ?>%; transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 10px rgba(217, 119, 6, 0.5);"></div>
            </div>
            
            <?php if(!empty($order['estimated_delivery_date']) && $order['status'] != 'Delivered'): ?>
            <div style="margin-top: 16px; text-align: center; background: #F9FAFB; border-radius: 8px; padding: 12px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span style="font-size: 12px; color: #6B7280;">Estimasi Tiba:</span>
                <strong style="font-size: 14px; color: #1F2937;"><?php echo date('d M Y', strtotime($order['estimated_delivery_date'])); ?></strong>
            </div>
            <?php endif; ?>
        </div>
            
        <!-- Timeline Title -->
        <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 24px; color: #1F2937; padding-bottom: 12px; border-bottom: 2px solid #E5E7EB; display: flex; align-items: center; gap: 10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                <circle cx="12" cy="10" r="3"></circle>
            </svg>
            Tracking History
        </h3>
        
        <!-- Timeline List with Admin Style -->
        <div class="timeline-list">
            <?php 
            $history_array = [];
            while($history = mysqli_fetch_assoc($tracking_history)) {
                $history_array[] = $history;
            }
            $history_array = array_reverse($history_array); // Show oldest first
            
            foreach($history_array as $index => $history): 
                $status_code = $history['status_detail'] ?? 'order_placed';
                $status_lower = strtolower(trim($history['status']));
            ?>
            <div class="timeline-item">
                <div class="timeline-marker <?php 
                    echo 'marker-' . $status_lower; 
                ?>"></div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <strong class="timeline-status"><?php echo htmlspecialchars($history['status_name_id'] ?? trim($history['status'])); ?></strong>
                        <span class="timeline-date"><?php echo date('d M Y, H:i', strtotime($history['created_at'])); ?></span>
                    </div>
                    
                    <?php if(!empty($history['location'])): ?>
                        <div class="timeline-detail">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            Location: <strong><?php echo htmlspecialchars($history['location']); ?></strong>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($history['tracking_number'])): ?>
                        <div class="timeline-detail">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="3" width="15" height="13"></rect>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                            </svg>
                            Tracking: <strong><?php echo htmlspecialchars($history['tracking_number']); ?></strong>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($history['notes'])): ?>
                        <div class="timeline-notes">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <?php echo nl2br(htmlspecialchars($history['notes'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($history['courier_name'])): ?>
                        <div class="timeline-detail">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            Courier: <strong><?php echo htmlspecialchars($history['courier_name']); ?></strong>
                            <?php if(!empty($history['courier_phone'])): ?>
                                (<?php echo htmlspecialchars($history['courier_phone']); ?>)
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
    
    <!-- Confirm Delivery Button - HANYA muncul saat status Delivered (100%) -->
    <?php if($order_status == 'Delivered'): ?>
    <div style="margin-top: 40px; padding-top: 32px; border-top: 2px solid #E5E7EB;">
        <div style="background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); border: 2px solid #F59E0B; border-radius: 12px; padding: 20px; margin-bottom: 16px;">
            <p style="font-size: 15px; color: #92400E; margin: 0; line-height: 1.7; font-weight: 500;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <strong>Sudah menerima paket Anda?</strong><br>
                <span style="font-size: 13px;">Konfirmasi penerimaan untuk menyelesaikan pesanan ini.</span>
            </p>
        </div>
        <form method="POST">
            <button type="submit" name="confirm_delivery" style="width: 100%; padding: 18px; font-size: 16px; font-weight: 700; border-radius: 12px; border: none; cursor: pointer; background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3); transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(16, 185, 129, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(16, 185, 129, 0.3)';" 129, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(16, 185, 129, 0.3)';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Konfirmasi Pesanan Diterima
            </button>
        </form>
    </div>
    <?php elseif($order_status == 'Completed'): ?>
    <!-- Order Completed Message -->
    <div style="margin-top: 40px; padding-top: 32px; border-top: 2px solid #E5E7EB;">
        <div style="background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%); border: 2px solid #10B981; border-radius: 12px; padding: 24px; text-align: center;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5" style="margin: 0 auto 16px;">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <h4 style="font-size: 20px; font-weight: 800; color: #065F46; margin: 0 0 8px 0; display: flex; align-items: center; justify-content: center; gap: 10px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Pesanan Selesai
            </h4>
            <p style="font-size: 14px; color: #047857; margin: 0;">
                Terima kasih telah berbelanja di RetroLoved!
            </p>
        </div>
    </div>
    <?php endif; ?>
</div>

<link rel="stylesheet" href="../assets/css/tracking.css">
