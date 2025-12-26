<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Menu</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #b846e1ff 0%, #d54266ff 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 1200px;
            margin: 0 auto;
            backdrop-filter: blur(10px);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .btn-back {
            background-color: #fbbf24;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            text-decoration: none;
        }
        
        .btn-back:hover {
            background-color: #d97706;
        }
        
        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .success {
            background: rgba(72, 187, 120, 0.1);
            color: #2f855a;
            border: 1px solid rgba(72, 187, 120, 0.3);
        }
        
        .error {
            background: rgba(245, 101, 101, 0.1);
            color: #c53030;
            border: 1px solid rgba(245, 101, 101, 0.3);
        }
        
        .warning {
            background: rgba(245, 158, 11, 0.1);
            color: #d69e2e;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        
        .search-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px;
        }
        
        .input-group {
            display: flex;
            gap: 10px;
        }
        
        .input-group input {
            flex: 1;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .content-row {
            display: flex;
            min-height: 500px;
        }
        
        .left-column {
            width: 40%;
            padding: 20px;
            border-right: 1px solid #dee2e6;
            overflow-y: auto;
        }
        
        .right-column {
            width: 60%;
            padding: 20px;
        }
        
        .file-list {
            margin-top: 1rem;
        }
        
        .file-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
            cursor: pointer;
        }
        
        .file-item:hover {
            background: rgba(102, 126, 234, 0.1);
        }
        
        .file-icon {
            font-size: 24px;
            margin-right: 15px;
        }
        
        .file-details {
            flex: 1;
        }
        
        .file-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }
        
        .file-meta {
            font-size: 14px;
            color: #666;
        }
        
        .revision-badge {
            background: #667eea;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .no-files {
            text-align: center;
            color: #666;
            padding: 2rem;
            font-style: italic;
        }
        
        iframe {
            width: 100%;
            height: 600px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        
        .preview-loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="mb-4">
        <a href="audit.php" class="btn-back">Back</a>
    </div>

    <div class="container">
        <div class="header">
            <h1>🔍 Audit Menu</h1>
            <p>View and audit documents related to booking codes without downloading</p>
        </div>
        
        <div class="search-section">
            <h3 style="margin-bottom: 15px;">Search Booking Code</h3>
            <div class="input-group">
                <input type="text" id="searchBookingCode" placeholder="Enter booking code to search">
                <button type="button" class="btn btn-secondary" onclick="searchDocuments()">🔍 Search</button>
            </div>
        </div>
        
        <div id="search-message" style="margin: 0 20px;"></div>
        
        <div class="content-row">
            <div class="left-column">
                <h3>📋 Documents</h3>
                <div id="document-list"></div>
            </div>
            <div class="right-column">
                <h3>👁️ Preview</h3>
                <div id="preview-area">
                    <p id="preview-message">Select a document from the left to preview (PDF files only; other formats may not display inline).</p>
                    <div id="preview-loading" class="preview-loading" style="display: none;">🔄 Loading preview...</div>
                    <iframe id="preview-iframe" src="" style="display: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script>
        function searchDocuments() {
            const bookingCode = document.getElementById('searchBookingCode').value.trim();
            const documentList = document.getElementById('document-list');
            const searchMessage = document.getElementById('search-message');
            const previewIframe = document.getElementById('preview-iframe');
            const previewMessage = document.getElementById('preview-message');
            const previewLoading = document.getElementById('preview-loading');
            
            if (!bookingCode) {
                showMessage('Please enter a booking code', 'error');
                documentList.innerHTML = '';
                resetPreview();
                return;
            }
            
            searchMessage.innerHTML = '';
            documentList.innerHTML = '<div style="text-align: center; padding: 20px;">🔍 Searching...</div>';
            resetPreview();
            
            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            displayDocuments(response);
                        } catch (e) {
                            showMessage('Error parsing search results', 'error');
                            documentList.innerHTML = '';
                        }
                    } else {
                        showMessage('Error occurred while searching', 'error');
                        documentList.innerHTML = '';
                    }
                }
            };
            
            xhr.open('GET', 'upload_handler.php?action=search&booking_code=' + encodeURIComponent(bookingCode), true);
            xhr.send();
        }

        function displayDocuments(data) {
            const documentList = document.getElementById('document-list');
            
            if (!data.success || data.files.length === 0) {
                documentList.innerHTML = '<div class="no-files">📂 No documents found for this booking code</div>';
                resetPreview();
                return;
            }
            
            let html = '<div class="file-list">';
            html += `<h4>Documents for Booking Code: ${data.booking_code}</h4>`;
            
            data.files.forEach(file => {
                const icon = getFileIcon(file.extension);
                const isPDF = file.extension.toLowerCase() === 'pdf';
                
                html += `
                    <div class="file-item" onclick="previewDocument('${encodeURIComponent(file.path)}', ${isPDF}, '${file.name}')">
                        <div class="file-icon">${icon}</div>
                        <div class="file-details">
                            <div class="file-name">${file.name}</div>
                            <div class="file-meta">
                                Size: ${formatFileSize(file.size)} | 
                                Uploaded: ${file.upload_date}
                                <span class="revision-badge">Rev ${file.revision}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            documentList.innerHTML = html;
        }

        function previewDocument(filePath, isPDF, fileName) {
            const previewIframe = document.getElementById('preview-iframe');
            const previewMessage = document.getElementById('preview-message');
            const previewLoading = document.getElementById('preview-loading');
            
            previewIframe.src = '';
            previewIframe.style.display = 'none';
            previewMessage.style.display = 'none';
            previewLoading.style.display = 'block';
            
            if (!isPDF) {
                previewLoading.style.display = 'none';
                previewMessage.textContent = `Preview not available for ${fileName} (non-PDF file).`;
                previewMessage.style.display = 'block';
                return;
            }
            
            previewIframe.src = 'upload_handler.php?action=view&file=' + filePath;
            previewIframe.onload = () => {
                previewLoading.style.display = 'none';
                previewIframe.style.display = 'block';
            };
            previewIframe.onerror = () => {
                previewLoading.style.display = 'none';
                previewMessage.textContent = 'Error loading preview for ' + fileName;
                previewMessage.style.display = 'block';
            };
        }

        function resetPreview() {
            const previewIframe = document.getElementById('preview-iframe');
            const previewMessage = document.getElementById('preview-message');
            const previewLoading = document.getElementById('preview-loading');
            
            previewIframe.src = '';
            previewIframe.style.display = 'none';
            previewLoading.style.display = 'none';
            previewMessage.textContent = 'Select a document from the left to preview (PDF files only; other formats may not display inline).';
            previewMessage.style.display = 'block';
        }

        function getFileIcon(extension) {
            switch (extension.toLowerCase()) {
                case 'pdf': return '📄';
                case 'doc':
                case 'docx': return '📝';
                default: return '📄';
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function showMessage(text, type) {
            const searchMessage = document.getElementById('search-message');
            searchMessage.innerHTML = `<div class="message ${type}">${text}</div>`;
            setTimeout(() => {
                searchMessage.innerHTML = '';
            }, 5000);
        }

        // Enter key support for search
        document.getElementById('searchBookingCode').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchDocuments();
            }
        });
    </script>
</body>
</html>