<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username']);
$lkr_symbol = 'LKR';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardia POS - Void Invoice</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #ec4899;
            --accent: #f59e0b;
            --bg-primary: #0f0f23;
            --bg-secondary: #1a1a2e;
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-primary: #ffffff;
            --text-secondary: #a1a1aa;
            --navbar-bg: #1e40af;
            --purple: #a855f7;
            --purple-dark: #7e22ce;
            --danger: #ef4444;
            --success: #10b981;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: var(--bg-primary);
            color: var(--text-primary);
            overflow-x: hidden;
            position: relative;
        }

        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background: linear-gradient(45deg, #0f0f23, #1a1a2e, #16213e);
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(236, 72, 153, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(245, 158, 11, 0.2) 0%, transparent 50%);
            animation: floatingBg 20s ease-in-out infinite;
        }

        @keyframes floatingBg {
            0%, 100% { transform: translate(-50%, -50%) rotate(0deg) scale(1); }
            33% { transform: translate(-45%, -55%) rotate(120deg) scale(1.1); }
            66% { transform: translate(-55%, -45%) rotate(240deg) scale(0.9); }
        }

        .navbar {
            background: var(--navbar-bg);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .navbar-brand {
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
        }

        .navbar-user {
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-icon {
            width: 24px;
            height: 24px;
            color: var(--text-primary);
        }

        .main-content {
            margin-top: 80px;
            padding: 40px;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }

        .card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--glass-border);
            border-radius: var(--radius);
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
        }

        .search-container {
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            width: 20px;
            height: 20px;
        }

        .search-input {
            padding-left: 40px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            color: var(--text-primary);
            font-size: 1rem;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #dc2626);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626, var(--danger));
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .invoice-details {
            margin-top: 24px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding: 8px 0;
            border-bottom: 1px solid var(--glass-border);
        }

        .detail-label {
            font-weight: 500;
            color: var(--text-secondary);
        }

        .detail-value {
            font-weight: 600;
            color: var(--text-primary);
        }

        .items-list {
            margin: 24px 0;
            max-height: 300px;
            overflow-y: auto;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--glass-border);
        }

        .item-name {
            font-weight: 600;
        }

        .item-meta {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .total-section {
            margin-top: 24px;
            padding: 16px;
            background: var(--bg-secondary);
            border-radius: var(--radius);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .total-label {
            font-weight: 500;
            color: var(--text-secondary);
        }

        .total-value {
            font-weight: 600;
        }

        .void-section {
            margin-top: 24px;
            padding: 16px;
            background: var(--bg-secondary);
            border-radius: var(--radius);
        }

        .void-reason {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--glass-border);
            border-radius: var(--radius);
            background: var(--bg-primary);
            color: var(--text-primary);
            font-size: 1rem;
            resize: vertical;
            min-height: 100px;
            transition: var(--transition);
        }

        .void-reason:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
        }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--success);
            color: var(--text-primary);
            padding: 12px 24px;
            border-radius: var(--radius);
            z-index: 1000;
            opacity: 0;
            transform: translateY(100px);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast.error {
            background: var(--danger);
        }

        .hidden {
            display: none;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
                margin-top: 60px;
            }

            .navbar {
                padding: 12px 16px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>

    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">Guardia POS</a>
        <div class="navbar-user">
            <svg class="user-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            <span>User: <?php echo $username; ?></span>
        </div>
    </nav>

    <div class="main-content">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Void Invoice
                </h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Search Invoice by Number</label>
                    <div class="search-container">
                        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <input type="text" id="invoiceNumberInput" class="form-input search-input" placeholder="Enter invoice number..." autocomplete="off">
                    </div>
                </div>
                <div id="invoiceDetails" class="invoice-details hidden"></div>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        const invoiceNumberInput = document.getElementById('invoiceNumberInput');
        const invoiceDetails = document.getElementById('invoiceDetails');
        const toast = document.getElementById('toast');
        const lkrSymbol = '<?php echo $lkr_symbol; ?>';

        function showToast(message, type = 'success') {
            toast.textContent = message;
            toast.className = `toast ${type} show`;
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        invoiceNumberInput.addEventListener('input', function() {
            const invoiceNumber = this.value.trim();
            if (invoiceNumber.length < 2) {
                invoiceDetails.classList.add('hidden');
                invoiceDetails.innerHTML = '';
                return;
            }

            fetch(`search_invoice.php?invoice_number=${encodeURIComponent(invoiceNumber)}`)
                .then(res => {
                    if (!res.ok) throw new Error('Network error');
                    return res.json();
                })
                .then(data => {
                    if (data.error) {
                        showToast(data.error, 'error');
                        invoiceDetails.classList.add('hidden');
                        invoiceDetails.innerHTML = '';
                        return;
                    }

                    if (!data.invoice) {
                        showToast('Invoice not found', 'error');
                        invoiceDetails.classList.add('hidden');
                        invoiceDetails.innerHTML = '';
                        return;
                    }

                    const invoice = data.invoice;
                    const items = data.items;

                    let extraInfo = '';
                    if (invoice.payment_type === 'credit' && invoice.creditor_name) {
                        extraInfo = `Creditor: ${invoice.creditor_name}`;
                    } else if (invoice.payment_type === 'other_credit' && invoice.other_creditor_name) {
                        extraInfo = `Other Creditor: ${invoice.other_creditor_name}`;
                    } else if (invoice.payment_type === 'foc' && invoice.foc_responsible) {
                        extraInfo = `FOC Responsible: ${invoice.foc_responsible}`;
                    } else if (invoice.payment_type === 'delivery' && invoice.delivery_place) {
                        extraInfo = `Delivery Place: ${invoice.delivery_place}`;
                    }

                    let itemsHtml = items.length > 0 ? '<div class="items-list">' : '<p>No items found</p>';
                    items.forEach(item => {
                        itemsHtml += `
                            <div class="item-row">
                                <div>
                                    <div class="item-name">${item.item_name}</div>
                                    <div class="item-meta">Code: ${item.item_code} | Qty: ${item.quantity} | Unit: ${lkrSymbol} ${parseFloat(item.price).toFixed(2)}</div>
                                </div>
                                <div class="item-meta">${lkrSymbol} ${parseFloat(item.total).toFixed(2)}</div>
                            </div>
                        `;
                    });
                    if (items.length > 0) itemsHtml += '</div>';

                    const isVoidable = invoice.status === 'completed' || invoice.status === 'pending';
                    const voidSection = isVoidable ? `
                        <div class="void-section">
                            <div class="form-group">
                                <label class="form-label">Reason for Void</label>
                                <textarea id="voidReason" class="void-reason" placeholder="Enter reason for voiding the invoice..." required></textarea>
                            </div>
                            <button id="voidButton" class="btn btn-danger">Void Invoice</button>
                        </div>
                    ` : `<p class="detail-row" style="color: var(--danger);">Invoice is already ${invoice.status}</p>`;

                    invoiceDetails.innerHTML = `
                        <div class="detail-row">
                            <span class="detail-label">Invoice Number:</span>
                            <span class="detail-value">${invoice.invoice_number}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Table Number:</span>
                            <span class="detail-value">${invoice.table_number || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Payment Type:</span>
                            <span class="detail-value">${invoice.payment_type.replace('_', ' ').toUpperCase()}</span>
                        </div>
                        ${extraInfo ? `
                            <div class="detail-row">
                                <span class="detail-label">Extra Info:</span>
                                <span class="detail-value">${extraInfo}</span>
                            </div>
                        ` : ''}
                        <div class="detail-row">
                            <span class="detail-label">Cashier:</span>
                            <span class="detail-value">${invoice.cashier}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Created At:</span>
                            <span class="detail-value">${new Date(invoice.created_at).toLocaleString()}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value">${invoice.status.toUpperCase()}</span>
                        </div>
                        ${itemsHtml}
                        <div class="total-section">
                            <div class="total-row">
                                <span class="total-label">Subtotal:</span>
                                <span class="total-value">${lkrSymbol} ${parseFloat(invoice.subtotal).toFixed(2)}</span>
                            </div>
                            <div class="total-row">
                                <span class="total-label">Discount:</span>
                                <span class="total-value">${lkrSymbol} ${parseFloat(invoice.discount).toFixed(2)}</span>
                            </div>
                            <div class="total-row">
                                <span class="total-label">Service Charge:</span>
                                <span class="total-value">${lkrSymbol} ${parseFloat(invoice.service_charge).toFixed(2)}</span>
                            </div>
                            <div class="total-row">
                                <span class="total-label">Delivery Charge:</span>
                                <span class="total-value">${lkrSymbol} ${parseFloat(invoice.delivery_charge || 0).toFixed(2)}</span>
                            </div>
                            <div class="total-row">
                                <span class="total-label">Grand Total:</span>
                                <span class="total-value">${lkrSymbol} ${parseFloat(invoice.grand_total).toFixed(2)}</span>
                            </div>
                        </div>
                        ${voidSection}
                    `;
                    invoiceDetails.classList.remove('hidden');

                    if (isVoidable) {
                        document.getElementById('voidButton').addEventListener('click', () => {
                            const voidReason = document.getElementById('voidReason').value.trim();
                            if (!voidReason) {
                                showToast('Please provide a reason for voiding the invoice.', 'error');
                                return;
                            }

                            fetch('void_invoice.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    invoice_number: invoice.invoice_number,
                                    void_reason: voidReason,
                                    cashier: '<?php echo $username; ?>'
                                })
                            })
                            .then(res => {
                                if (!res.ok) throw new Error('Network error');
                                return res.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    showToast(`Invoice ${invoice.invoice_number} voided successfully!`);
                                    invoiceDetails.innerHTML = '';
                                    invoiceDetails.classList.add('hidden');
                                    invoiceNumberInput.value = '';
                                } else {
                                    showToast('Failed to void invoice: ' + (data.error || 'Unknown error'), 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Void error:', error);
                                showToast('Failed to void invoice. Please try again.', 'error');
                            });
                        });
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    showToast('Failed to fetch invoice details. Please try again.', 'error');
                    invoiceDetails.classList.add('hidden');
                    invoiceDetails.innerHTML = '';
                });
        });
    </script>
</body>
</html>