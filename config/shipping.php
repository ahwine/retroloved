<?php
/**
 * Shipping System Helper Functions
 * RetroLoved E-Commerce - Consistent with existing code style
 */

// ===== GET ALL ACTIVE SHIPPING SERVICES =====
function get_shipping_services() {
    $query = "SELECT ss.*, sc.courier_name, sc.courier_code, sc.logo_url 
              FROM shipping_services ss
              JOIN shipping_couriers sc ON ss.courier_id = sc.courier_id
              WHERE ss.is_active = 1 AND sc.is_active = 1
              ORDER BY ss.display_order ASC, ss.base_cost ASC";
    return query($query);
}

// ===== GET SHIPPING SERVICE BY ID =====
function get_shipping_service_by_id($service_id) {
    $service_id = escape($service_id);
    $query = "SELECT ss.*, sc.courier_name, sc.courier_code 
              FROM shipping_services ss
              JOIN shipping_couriers sc ON ss.courier_id = sc.courier_id
              WHERE ss.service_id = '$service_id'";
    $result = query($query);
    return mysqli_fetch_assoc($result);
}

// ===== CALCULATE ESTIMATED DELIVERY DATE =====
function calculate_estimated_delivery($days_min, $days_max = null) {
    $days = $days_max ?? $days_min;
    $current_date = new DateTime();
    $delivery_date = clone $current_date;
    
    $added_days = 0;
    while ($added_days < $days) {
        $delivery_date->modify('+1 day');
        // Skip weekends (Saturday & Sunday)
        if ($delivery_date->format('N') < 6) {
            $added_days++;
        }
    }
    
    return $delivery_date->format('Y-m-d H:i:s');
}

// ===== GET ALL TRACKING STATUSES =====
function get_tracking_statuses() {
    $query = "SELECT * FROM tracking_statuses WHERE is_active = 1 ORDER BY step_order ASC";
    return query($query);
}

// ===== GET TRACKING STATUS BY CODE =====
function get_tracking_status($status_code) {
    $status_code = escape($status_code);
    $query = "SELECT * FROM tracking_statuses WHERE status_code = '$status_code'";
    $result = query($query);
    return mysqli_fetch_assoc($result);
}

// ===== UPDATE ORDER WITH TRACKING DETAILS =====
function update_order_tracking($order_id, $tracking_data) {
    $order_id = escape($order_id);
    
    $status_detail = isset($tracking_data['status_detail']) ? escape($tracking_data['status_detail']) : null;
    $location = isset($tracking_data['location']) ? escape($tracking_data['location']) : null;
    $courier_name = isset($tracking_data['courier_name']) ? escape($tracking_data['courier_name']) : null;
    $courier_phone = isset($tracking_data['courier_phone']) ? escape($tracking_data['courier_phone']) : null;
    $notes = isset($tracking_data['notes']) ? escape($tracking_data['notes']) : null;
    $tracking_number = isset($tracking_data['tracking_number']) ? escape($tracking_data['tracking_number']) : null;
    $estimated_arrival = isset($tracking_data['estimated_arrival']) ? escape($tracking_data['estimated_arrival']) : null;
    
    // Update order table
    $update_parts = [];
    if ($status_detail) $update_parts[] = "current_status_detail = '$status_detail'";
    if ($location) $update_parts[] = "current_location = '$location'";
    if ($courier_name) $update_parts[] = "courier_name = '$courier_name'";
    if ($courier_phone) $update_parts[] = "courier_phone = '$courier_phone'";
    if ($tracking_number) $update_parts[] = "tracking_number = '$tracking_number'";
    if ($estimated_arrival) $update_parts[] = "estimated_delivery_date = '$estimated_arrival'";
    
    // Special handling for shipped_at and delivered_at
    if ($status_detail === 'picked_up' || $status_detail === 'in_transit') {
        $update_parts[] = "shipped_at = NOW()";
    }
    if ($status_detail === 'delivered') {
        $update_parts[] = "delivered_at = NOW()";
    }
    
    if (!empty($update_parts)) {
        $update_query = "UPDATE orders SET " . implode(", ", $update_parts) . " WHERE order_id = '$order_id'";
        query($update_query);
    }
    
    // Insert into order_history
    $history_query = "INSERT INTO order_history (order_id, status, status_detail, location, courier_name, courier_phone, notes, tracking_number, estimated_arrival, changed_by) 
                      VALUES ('$order_id', 
                              (SELECT status FROM orders WHERE order_id = '$order_id'), 
                              " . ($status_detail ? "'$status_detail'" : "NULL") . ", 
                              " . ($location ? "'$location'" : "NULL") . ", 
                              " . ($courier_name ? "'$courier_name'" : "NULL") . ", 
                              " . ($courier_phone ? "'$courier_phone'" : "NULL") . ", 
                              " . ($notes ? "'$notes'" : "NULL") . ", 
                              " . ($tracking_number ? "'$tracking_number'" : "NULL") . ", 
                              " . ($estimated_arrival ? "'$estimated_arrival'" : "NULL") . ", 
                              " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "NULL") . ")";
    
    return query($history_query);
}

// ===== GET ORDER TRACKING HISTORY WITH DETAILS =====
function get_order_tracking_history($order_id) {
    $order_id = escape($order_id);
    $query = "SELECT oh.*, 
                     ts.status_name_id, ts.color, ts.step_order,
                     u.full_name as admin_name
              FROM order_history oh
              LEFT JOIN tracking_statuses ts ON oh.status_detail = ts.status_code
              LEFT JOIN users u ON oh.changed_by = u.user_id
              WHERE oh.order_id = '$order_id'
              ORDER BY oh.created_at DESC, oh.history_id DESC";
    return query($query);
}

// ===== CALCULATE TRACKING PROGRESS PERCENTAGE (BASED ON ORDER STATUS) =====
function calculate_tracking_progress($order_status) {
    // Progress based on main order status
    // Pending (15% - payment uploaded), Processing (25%), Shipped (75%), Delivered (100%), Completed (100%), Cancelled (0%)
    $progress_map = [
        'Pending' => 15,          // Customer uploaded payment, waiting for confirmation
        'Processing' => 25,       // Payment confirmed, order being processed
        'Shipped' => 75,          // In delivery
        'Delivered' => 100,       // Delivered to customer
        'Completed' => 100,       // Order completed and confirmed by customer
        'Cancelled' => 0          // Order cancelled
    ];
    
    return isset($progress_map[$order_status]) ? $progress_map[$order_status] : 0;
}

// ===== GET SVG ICON FOR TRACKING STATUS =====
function get_tracking_icon_svg($status_code) {
    $icons = [
        'order_placed' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
        'payment_confirmed' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>',
        'processing' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
        'picked_up' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>',
        'in_sorting' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>',
        'in_transit' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path><polygon points="12 15 17 21 7 21 12 15"></polygon></svg>',
        'arrived_destination' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
        'out_for_delivery' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
        'delivered' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'
    ];
    
    return isset($icons[$status_code]) ? $icons[$status_code] : $icons['order_placed'];
}

// ===== FORMAT RELATIVE TIME =====
function format_relative_time($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 172800) {
        return 'Yesterday at ' . date('H:i', $time);
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('d M Y, H:i', $time);
    }
}

// ===== AUTO GENERATE TRACKING NUMBER & COURIER =====
/**
 * Generate tracking number berdasarkan courier code
 * @param string $courier_code - Kode kurir (JNE, JNT, SICEPAT, dll)
 * @return string - Tracking number yang di-generate
 */
function generate_tracking_number($courier_code = 'JNE') {
    $prefix = strtoupper(substr($courier_code, 0, 3));
    $timestamp = date('ymd'); // Format: YYMMDD
    $random = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $check = rand(10, 99);
    
    return $prefix . $timestamp . $random . $check;
}

/**
 * Get random courier driver data
 * @return array - Array dengan 'name' dan 'phone'
 */
function get_random_courier_driver() {
    $courier_drivers = [
        ['name' => 'Budi Santoso', 'phone' => '0812-3456-7890'],
        ['name' => 'Ahmad Ridwan', 'phone' => '0813-9876-5432'],
        ['name' => 'Siti Rahayu', 'phone' => '0821-1122-3344'],
        ['name' => 'Dedi Kurniawan', 'phone' => '0857-6677-8899'],
        ['name' => 'Rina Wati', 'phone' => '0878-9988-7766'],
        ['name' => 'Agus Setiawan', 'phone' => '0819-2233-4455'],
        ['name' => 'Dewi Lestari', 'phone' => '0856-3344-5566'],
        ['name' => 'Rudi Hartono', 'phone' => '0822-4455-6677'],
        ['name' => 'Indah Permata', 'phone' => '0877-5566-7788'],
        ['name' => 'Joko Widodo', 'phone' => '0838-6677-8899']
    ];
    
    return $courier_drivers[array_rand($courier_drivers)];
}

/**
 * Auto-generate tracking number ketika payment di-confirm (Processing)
 * HANYA generate tracking number, courier akan di-assign saat Shipped
 * @param int $order_id - ID order
 * @return bool - True jika berhasil
 */
function auto_generate_tracking_and_courier($order_id) {
    $order_id = escape($order_id);
    
    // Get order data dengan JOIN ke shipping_couriers untuk dapat courier_code
    $order_query = query("SELECT o.*, 
                                 ss.estimated_days_max, 
                                 sc.courier_code 
                          FROM orders o 
                          LEFT JOIN shipping_services ss ON o.shipping_service_id = ss.service_id 
                          LEFT JOIN shipping_couriers sc ON ss.courier_id = sc.courier_id
                          WHERE o.order_id = '$order_id'");
    $order = mysqli_fetch_assoc($order_query);
    
    if (!$order) return false;
    
    // Jika sudah ada tracking number, jangan generate lagi
    if (!empty($order['tracking_number'])) {
        return true; // Already has tracking
    }
    
    // Generate tracking number ONLY (courier akan di-assign saat Shipped)
    $courier_code = $order['courier_code'] ?? 'JNE';
    $tracking_number = generate_tracking_number($courier_code);
    
    // Calculate estimated delivery
    $estimated_days = $order['estimated_days_max'] ?? 3;
    if ($estimated_days < 1) $estimated_days = 1;
    $estimated_delivery = calculate_estimated_delivery($estimated_days, $estimated_days);
    
    // Update order dengan tracking number SAJA (tanpa courier)
    $update_query = "UPDATE orders SET 
                     tracking_number = '$tracking_number',
                     estimated_delivery_date = '$estimated_delivery',
                     current_status_detail = 'processing'
                     WHERE order_id = '$order_id'";
    
    if (query($update_query)) {
        // Log ke order_history
        $history_query = "INSERT INTO order_history 
                         (order_id, status, status_detail, tracking_number, notes, estimated_arrival, changed_by) 
                         VALUES 
                         ('$order_id', 
                          'Processing', 
                          'processing',
                          '$tracking_number',
                          'Tracking number telah di-generate otomatis. Kurir akan di-assign saat status Shipped.',
                          '$estimated_delivery',
                          " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "NULL") . ")";
        
        query($history_query);
        return true;
    }
    
    return false;
}

/**
 * Auto-assign courier driver ketika status berubah ke Shipped
 * @param int $order_id - ID order
 * @return bool - True jika berhasil
 */
function auto_assign_courier($order_id) {
    $order_id = escape($order_id);
    
    // Get order data
    $order = mysqli_fetch_assoc(query("SELECT * FROM orders WHERE order_id = '$order_id'"));
    
    if (!$order) return false;
    
    // Jika sudah ada courier, jangan assign lagi
    if (!empty($order['courier_name'])) {
        return true; // Already has courier
    }
    
    // Get random courier driver
    $courier = get_random_courier_driver();
    $courier_name = escape($courier['name']);
    $courier_phone = escape($courier['phone']);
    
    // Update order dengan courier info
    $update_query = "UPDATE orders SET 
                     courier_name = '$courier_name',
                     courier_phone = '$courier_phone',
                     current_status_detail = 'in_transit',
                     shipped_at = NOW()
                     WHERE order_id = '$order_id'";
    
    if (query($update_query)) {
        // Log ke order_history
        $history_query = "INSERT INTO order_history 
                         (order_id, status, status_detail, courier_name, courier_phone, notes, changed_by) 
                         VALUES 
                         ('$order_id', 
                          'Shipped', 
                          'in_transit',
                          '$courier_name',
                          '$courier_phone',
                          'Kurir telah di-assign otomatis. Paket dalam perjalanan.',
                          " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "NULL") . ")";
        
        query($history_query);
        return true;
    }
    
    return false;
}
?>
