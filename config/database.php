<?php
/**
 * Database Configuration
 */

// Set timezone ke Asia/Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

// Database connection settings
$host = 'localhost';
$username = 'root';          // Your MySQL username
$password = '';              // Your MySQL password
$database = 'retroloved';    // Database name

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Set MySQL timezone to match PHP timezone
mysqli_query($conn, "SET time_zone = '+07:00'"); // WIB (UTC+7)

/**
 * Helper function untuk query
 */
function query($query) {
    global $conn;
    return mysqli_query($conn, $query);
}

/**
 * Helper function untuk escape string
 */
function escape($string) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($string));
}

/**
 * Helper function untuk validasi email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Helper function untuk set message
 */
function set_message($type, $text) {
    $_SESSION['message'] = [
        'type' => $type,
        'text' => $text
    ];
}

/**
 * Helper function untuk pagination
 */
function paginate($table, $where = '1=1', $per_page = 12) {
    global $conn;
    
    // Get current page from URL
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = max(1, $page); // Ensure page is at least 1
    
    // Count total items
    $count_query = "SELECT COUNT(*) as total FROM {$table} WHERE {$where}";
    $count_result = mysqli_query($conn, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    $total_items = $count_row['total'];
    
    // Calculate pagination values
    $total_pages = ceil($total_items / $per_page);
    $offset = ($page - 1) * $per_page;
    
    return [
        'page' => $page,
        'per_page' => $per_page,
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'offset' => $offset
    ];
}

/**
 * Helper function untuk get unread notifications count
 */
function get_unread_notifications_count($user_id) {
    global $conn;
    
    $user_id = mysqli_real_escape_string($conn, $user_id);
    
    $query = "SELECT COUNT(*) as count 
              FROM notifications 
              WHERE user_id = '$user_id' 
              AND is_read = 0";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    
    return 0;
}

/**
 * Helper function untuk display flash message
 */
function display_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $message['type']; // success, error, warning, info
        $text = $message['text'];
        
        // Map type to toast function
        $toast_function = 'toastSuccess';
        if ($type === 'error') {
            $toast_function = 'toastError';
        } elseif ($type === 'warning') {
            $toast_function = 'toastWarning';
        } elseif ($type === 'info') {
            $toast_function = 'toastInfo';
        }
        
        // Output JavaScript to show toast
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof {$toast_function} === 'function') {
                    {$toast_function}('" . addslashes($text) . "');
                }
            });
        </script>";
        
        // Clear message after displaying
        unset($_SESSION['message']);
    }
}

/**
 * Helper function untuk create notification
 * @param int $user_id - ID user yang akan menerima notifikasi
 * @param string $type - Tipe notifikasi (order, payment, shipping, system)
 * @param string $title - Judul notifikasi
 * @param string $message - Isi pesan notifikasi
 * @param int $order_id - ID order terkait (optional)
 * @return bool - True jika berhasil, false jika gagal
 */
function create_notification($user_id, $type, $title, $message, $order_id = null) {
    global $conn;
    
    // Escape semua input
    $user_id = mysqli_real_escape_string($conn, $user_id);
    $type = mysqli_real_escape_string($conn, $type);
    $title = mysqli_real_escape_string($conn, $title);
    $message = mysqli_real_escape_string($conn, $message);
    
    // Handle order_id (bisa NULL)
    if ($order_id !== null) {
        $order_id = mysqli_real_escape_string($conn, $order_id);
        $order_id_sql = "'$order_id'";
    } else {
        $order_id_sql = "NULL";
    }
    
    // Insert notification
    $query = "INSERT INTO notifications (user_id, order_id, type, title, message, is_read, created_at) 
              VALUES ('$user_id', $order_id_sql, '$type', '$title', '$message', 0, NOW())";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        return true;
    } else {
        error_log("Failed to create notification: " . mysqli_error($conn));
        return false;
    }
}

/**
 * Helper function untuk get order history
 * @param int $order_id - ID order yang ingin diambil historynya
 * @return mysqli_result|false - Result set berisi history order atau false jika gagal
 */
function get_order_history($order_id) {
    global $conn;
    
    // Escape input
    $order_id = mysqli_real_escape_string($conn, $order_id);
    
    // Query untuk mengambil history dengan informasi user yang mengubah
    $query = "SELECT oh.*, 
                     u.full_name as changed_by_name,
                     u.full_name as admin_name,
                     u.role as changed_by_role
              FROM order_history oh
              LEFT JOIN users u ON oh.changed_by = u.user_id
              WHERE oh.order_id = '$order_id'
              ORDER BY oh.created_at DESC";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        error_log("Failed to get order history: " . mysqli_error($conn));
        return false;
    }
    
    return $result;
}

/**
 * Helper function untuk add order history
 * @param int $order_id - ID order
 * @param string $status - Status baru
 * @param string $status_detail - Detail status (optional)
 * @param string $notes - Catatan (optional)
 * @param int $changed_by - ID user yang mengubah (optional)
 * @param array $additional_data - Data tambahan seperti tracking_number, location, dll (optional)
 * @return bool - True jika berhasil, false jika gagal
 */
function add_order_history($order_id, $status, $status_detail = null, $notes = null, $changed_by = null, $additional_data = []) {
    global $conn;
    
    // Escape input
    $order_id = mysqli_real_escape_string($conn, $order_id);
    $status = mysqli_real_escape_string($conn, $status);
    
    // Handle optional parameters
    $status_detail_sql = $status_detail ? "'" . mysqli_real_escape_string($conn, $status_detail) . "'" : "NULL";
    $notes_sql = $notes ? "'" . mysqli_real_escape_string($conn, $notes) . "'" : "NULL";
    $changed_by_sql = $changed_by ? "'" . mysqli_real_escape_string($conn, $changed_by) . "'" : "NULL";
    
    // Handle additional data
    $tracking_number_sql = isset($additional_data['tracking_number']) ? "'" . mysqli_real_escape_string($conn, $additional_data['tracking_number']) . "'" : "NULL";
    $location_sql = isset($additional_data['location']) ? "'" . mysqli_real_escape_string($conn, $additional_data['location']) . "'" : "NULL";
    $courier_name_sql = isset($additional_data['courier_name']) ? "'" . mysqli_real_escape_string($conn, $additional_data['courier_name']) . "'" : "NULL";
    $courier_phone_sql = isset($additional_data['courier_phone']) ? "'" . mysqli_real_escape_string($conn, $additional_data['courier_phone']) . "'" : "NULL";
    $estimated_arrival_sql = isset($additional_data['estimated_arrival']) ? "'" . mysqli_real_escape_string($conn, $additional_data['estimated_arrival']) . "'" : "NULL";
    
    // Insert history
    $query = "INSERT INTO order_history 
              (order_id, status, status_detail, tracking_number, notes, location, courier_name, courier_phone, estimated_arrival, changed_by, created_at) 
              VALUES 
              ('$order_id', '$status', $status_detail_sql, $tracking_number_sql, $notes_sql, $location_sql, $courier_name_sql, $courier_phone_sql, $estimated_arrival_sql, $changed_by_sql, NOW())";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        return true;
    } else {
        error_log("Failed to add order history: " . mysqli_error($conn));
        return false;
    }
}

/**
 * Helper function untuk update order status
 * @param int $order_id - ID order
 * @param string $new_status - Status baru
 * @param string $tracking_number - Nomor resi (optional)
 * @param string $notes - Catatan admin (optional)
 * @param int $changed_by - ID user yang mengubah (optional)
 * @return bool - True jika berhasil, false jika gagal
 */
function update_order_status($order_id, $new_status, $tracking_number = null, $notes = null, $changed_by = null) {
    global $conn;
    
    // Escape input
    $order_id = mysqli_real_escape_string($conn, $order_id);
    $new_status = mysqli_real_escape_string($conn, $new_status);
    
    // AUTO-ASSIGN COURIER: Jika status diubah ke Shipped
    if ($new_status == 'Shipped') {
        // Load shipping.php untuk akses fungsi auto_assign_courier
        if (file_exists(__DIR__ . '/shipping.php')) {
            require_once __DIR__ . '/shipping.php';
            
            // Auto-assign courier driver (akan generate random courier)
            if (function_exists('auto_assign_courier')) {
                auto_assign_courier($order_id);
            }
            
            // Jika belum ada tracking number, generate juga
            if (empty($tracking_number)) {
                $check_query = "SELECT tracking_number FROM orders WHERE order_id = '$order_id'";
                $check_result = mysqli_query($conn, $check_query);
                $check_data = mysqli_fetch_assoc($check_result);
                
                if (empty($check_data['tracking_number'])) {
                    if (function_exists('auto_generate_tracking_and_courier')) {
                        auto_generate_tracking_and_courier($order_id);
                        
                        // Ambil tracking number yang baru di-generate
                        $new_check = mysqli_query($conn, $check_query);
                        $new_data = mysqli_fetch_assoc($new_check);
                        $tracking_number = $new_data['tracking_number'];
                    }
                }
            }
        }
    }
    
    // Update order status
    $update_query = "UPDATE orders SET status = '$new_status'";
    
    // Add tracking number if provided
    if ($tracking_number) {
        $tracking_number = mysqli_real_escape_string($conn, $tracking_number);
        $update_query .= ", tracking_number = '$tracking_number'";
    }
    
    $update_query .= " WHERE order_id = '$order_id'";
    
    $update_result = mysqli_query($conn, $update_query);
    
    if ($update_result) {
        // Add to order history
        $additional_data = [];
        if ($tracking_number) {
            $additional_data['tracking_number'] = $tracking_number;
        }
        
        add_order_history($order_id, $new_status, null, $notes, $changed_by, $additional_data);
        
        // Send notification to customer
        send_order_status_notification($order_id, $new_status);
        
        return true;
    } else {
        error_log("Failed to update order status: " . mysqli_error($conn));
        return false;
    }
}

/**
 * Helper function untuk send order status notification
 * @param int $order_id - ID order
 * @param string $status - Status order
 * @return bool - True jika berhasil, false jika gagal
 */
function send_order_status_notification($order_id, $status) {
    global $conn;
    
    // Escape input
    $order_id = mysqli_real_escape_string($conn, $order_id);
    
    // Get order details
    $order_query = "SELECT o.*, u.full_name, u.email 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.user_id 
                    WHERE o.order_id = '$order_id'";
    $order_result = mysqli_query($conn, $order_query);
    
    if (!$order_result || mysqli_num_rows($order_result) == 0) {
        error_log("Order not found for notification: $order_id");
        return false;
    }
    
    $order = mysqli_fetch_assoc($order_result);
    
    // Prepare notification message based on status
    $notification_messages = [
        'Pending' => [
            'title' => 'Pesanan Menunggu Pembayaran',
            'message' => 'Pesanan #' . $order_id . ' menunggu pembayaran. Total: Rp ' . number_format($order['total_amount'], 0, ',', '.') . '. Silakan upload bukti pembayaran.'
        ],
        'Payment Uploaded' => [
            'title' => 'Bukti Pembayaran Diterima',
            'message' => 'Bukti pembayaran untuk pesanan #' . $order_id . ' sedang diverifikasi oleh admin.'
        ],
        'Processing' => [
            'title' => 'Pesanan Sedang Diproses',
            'message' => 'Pesanan #' . $order_id . ' sedang diproses. Kami akan segera mengirimkan pesanan Anda.'
        ],
        'Shipped' => [
            'title' => 'Pesanan Sedang Dikirim',
            'message' => 'Pesanan #' . $order_id . ' sedang dalam perjalanan.' . ($order['tracking_number'] ? ' Nomor resi: ' . $order['tracking_number'] : '')
        ],
        'Delivered' => [
            'title' => 'Pesanan Telah Sampai',
            'message' => 'Pesanan #' . $order_id . ' telah sampai di tujuan. Silakan konfirmasi penerimaan.'
        ],
        'Completed' => [
            'title' => 'Pesanan Selesai',
            'message' => 'Terima kasih! Pesanan #' . $order_id . ' telah selesai. Kami harap Anda puas dengan produk kami.'
        ],
        'Cancelled' => [
            'title' => 'Pesanan Dibatalkan',
            'message' => 'Pesanan #' . $order_id . ' telah dibatalkan. Jika ada pertanyaan, silakan hubungi customer service.'
        ]
    ];
    
    // Get notification content
    $notification = $notification_messages[$status] ?? [
        'title' => 'Update Status Pesanan',
        'message' => 'Status pesanan #' . $order_id . ' telah diupdate menjadi: ' . $status
    ];
    
    // Create notification with proper type based on status
    $notification_type = 'order_' . strtolower($status); // e.g., 'order_pending', 'order_confirmed'
    
    return create_notification(
        $order['user_id'],
        $notification_type,
        $notification['title'],
        $notification['message'],
        $order_id
    );
}

/**
 * Helper function untuk log order history (alias dari add_order_history)
 * @param int $order_id - ID order
 * @param string $status - Status baru
 * @param string $status_detail - Detail status (optional)
 * @param string $notes - Catatan (optional)
 * @param int $changed_by - ID user yang mengubah (optional)
 * @param array $additional_data - Data tambahan (optional)
 * @return bool - True jika berhasil, false jika gagal
 */
function log_order_history($order_id, $status, $status_detail = null, $notes = null, $changed_by = null, $additional_data = []) {
    // Alias untuk add_order_history
    return add_order_history($order_id, $status, $status_detail, $notes, $changed_by, $additional_data);
}

/**
 * Convert timestamp to relative time (e.g., "2 jam yang lalu")
 * @param string $datetime - Datetime string
 * @return string - Relative time in Indonesian
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $current_time = time();
    $diff = $current_time - $timestamp;
    
    if ($diff < 60) {
        return 'Baru saja';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' menit yang lalu';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' jam yang lalu';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' hari yang lalu';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' minggu yang lalu';
    } elseif ($diff < 31536000) {
        $months = floor($diff / 2592000);
        return $months . ' bulan yang lalu';
    } else {
        $years = floor($diff / 31536000);
        return $years . ' tahun yang lalu';
    }
}
?>
