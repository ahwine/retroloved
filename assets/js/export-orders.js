/**
 * Export Orders to Excel and PDF
 * Mengexport data orders ke format Excel (CSV) dan PDF
 * Untuk keperluan laporan admin
 */

/**
 * Export orders ke Excel (CSV format)
 * @param {Array} orders - Array of order objects
 * @param {string} filename - Nama file yang akan didownload
 */
function exportToExcel(orders, filename = 'orders_export') {
    if (!orders || orders.length === 0) {
        toastError('Tidak ada data untuk diexport!');
        return;
    }

    // Header CSV
    const headers = [
        'Order ID',
        'Customer Name',
        'Email',
        'Phone',
        'Product Name',
        'Price',
        'Status',
        'Order Date',
        'Shipping Address',
        'City',
        'Province',
        'Postal Code'
    ];

    // Convert orders ke CSV format
    let csvContent = headers.join(',') + '\n';

    orders.forEach(order => {
        const row = [
            order.order_id || '-',
            `"${order.customer_name || '-'}"`,
            order.email || '-',
            order.phone || '-',
            `"${order.product_name || '-'}"`,
            order.price || '0',
            order.status || '-',
            order.order_date || '-',
            `"${order.shipping_address || '-'}"`,
            order.city || '-',
            order.province || '-',
            order.postal_code || '-'
        ];
        csvContent += row.join(',') + '\n';
    });

    // Create blob dan download
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', `${filename}_${new Date().getTime()}.csv`);
    link.style.visibility = 'hidden';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    toastSuccess('Orders berhasil diexport ke Excel!');
}

/**
 * Export orders ke PDF menggunakan HTML print
 * @param {Array} orders - Array of order objects
 * @param {string} title - Judul laporan
 */
function exportToPDF(orders, title = 'Orders Report') {
    if (!orders || orders.length === 0) {
        toastError('Tidak ada data untuk diexport!');
        return;
    }

    // Buat window baru untuk print
    const printWindow = window.open('', '_blank');
    
    if (!printWindow) {
        toastError('Popup diblokir! Mohon izinkan popup untuk export PDF.');
        return;
    }

    // Generate HTML untuk print
    let html = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>${title}</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: Arial, sans-serif;
                    padding: 20px;
                    font-size: 12px;
                }
                
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #333;
                    padding-bottom: 15px;
                }
                
                .header h1 {
                    color: #D97706;
                    font-size: 24px;
                    margin-bottom: 5px;
                }
                
                .header p {
                    color: #666;
                    font-size: 14px;
                }
                
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                
                th {
                    background-color: #D97706;
                    color: white;
                    font-weight: bold;
                }
                
                tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                
                tr:hover {
                    background-color: #f5f5f5;
                }
                
                .status {
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 11px;
                    font-weight: bold;
                }
                
                .status-pending { background: #FEF3C7; color: #92400E; }
                .status-processing { background: #DBEAFE; color: #1E40AF; }
                .status-shipped { background: #D1FAE5; color: #065F46; }
                .status-delivered { background: #D1FAE5; color: #065F46; }
                .status-cancelled { background: #FEE2E2; color: #991B1B; }
                
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 11px;
                    color: #666;
                    border-top: 1px solid #ddd;
                    padding-top: 15px;
                }
                
                @media print {
                    body { padding: 10px; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>RetroLoved</h1>
                <p>${title} - Generated on ${new Date().toLocaleString('id-ID')}</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
    `;

    // Add rows
    orders.forEach(order => {
        const statusClass = `status status-${(order.status || 'pending').toLowerCase()}`;
        html += `
                    <tr>
                        <td>${order.order_id || '-'}</td>
                        <td>${order.customer_name || '-'}<br>
                            <small>${order.email || '-'}</small></td>
                        <td>${order.product_name || '-'}</td>
                        <td>Rp ${parseInt(order.price || 0).toLocaleString('id-ID')}</td>
                        <td><span class="${statusClass}">${order.status || 'Pending'}</span></td>
                        <td>${order.order_date || '-'}</td>
                    </tr>
        `;
    });

    html += `
                </tbody>
            </table>
            
            <div class="footer">
                <p>Total Orders: ${orders.length}</p>
                <p>RetroLoved - Thrift Fashion E-Commerce Platform</p>
            </div>
            
            <script>
                // Auto print saat halaman dimuat
                window.onload = function() {
                    window.print();
                    // Close window setelah print (opsional)
                    // setTimeout(() => window.close(), 1000);
                };
            </script>
        </body>
        </html>
    `;

    printWindow.document.write(html);
    printWindow.document.close();
    
    toastSuccess('Membuka preview PDF...');
}

/**
 * Ambil data orders dari tabel HTML dan convert ke array
 * Fungsi utility untuk mengambil data dari tabel yang sudah ada
 * @param {string} tableSelector - CSS selector untuk tabel orders
 * @returns {Array} - Array of order objects
 */
function getOrdersFromTable(tableSelector = '#ordersTable') {
    const table = document.querySelector(tableSelector);
    
    if (!table) {
        console.error('Table not found:', tableSelector);
        return [];
    }

    const orders = [];
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        
        if (cells.length > 0) {
            orders.push({
                order_id: cells[0]?.textContent.trim() || '-',
                customer_name: cells[1]?.textContent.trim() || '-',
                email: cells[2]?.textContent.trim() || '-',
                phone: cells[3]?.textContent.trim() || '-',
                product_name: cells[4]?.textContent.trim() || '-',
                price: cells[5]?.textContent.replace(/[^\d]/g, '') || '0',
                status: cells[6]?.textContent.trim() || '-',
                order_date: cells[7]?.textContent.trim() || '-',
                shipping_address: cells[8]?.textContent.trim() || '-',
                city: cells[9]?.textContent.trim() || '-',
                province: cells[10]?.textContent.trim() || '-',
                postal_code: cells[11]?.textContent.trim() || '-'
            });
        }
    });

    return orders;
}

/**
 * Setup export buttons
 * Otomatis attach event listener ke tombol export
 */
function setupExportButtons() {
    // Export to Excel button
    const excelBtn = document.querySelector('[data-export="excel"]');
    if (excelBtn) {
        excelBtn.addEventListener('click', function() {
            const orders = getOrdersFromTable();
            exportToExcel(orders);
        });
    }

    // Export to PDF button
    const pdfBtn = document.querySelector('[data-export="pdf"]');
    if (pdfBtn) {
        pdfBtn.addEventListener('click', function() {
            const orders = getOrdersFromTable();
            exportToPDF(orders);
        });
    }
}

// Auto setup saat DOM loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupExportButtons);
} else {
    setupExportButtons();
}

// Export functions untuk digunakan di file lain
window.exportToExcel = exportToExcel;
window.exportToPDF = exportToPDF;
window.getOrdersFromTable = getOrdersFromTable;
window.setupExportButtons = setupExportButtons;

console.log('Export Orders module loaded successfully');
