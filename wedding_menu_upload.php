<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Management System</title>
    
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
            max-width: 800px;
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
        
        .nav-tabs {
            display: flex;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        .nav-tab {
            flex: 1;
            padding: 1rem;
            text-align: center;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: #666;
            transition: all 0.3s ease;
        }
        
        .nav-tab.active {
            background: white;
            color: #667eea;
            border-bottom: 3px solid #667eea;
        }
        
        .nav-tab:hover {
            background: rgba(102, 126, 234, 0.1);
        }
        
        .tab-content {
            padding: 2rem;
        }
        
        .tab-pane {
            display: none;
        }
        
        .tab-pane.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }
        
        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus, input[type="file"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-upload input[type="file"] {
            opacity: 0;
            position: absolute;
            z-index: -1;
        }
        
        .file-upload-label {
            display: block;
            padding: 12px;
            border: 2px dashed #667eea;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(102, 126, 234, 0.05);
        }
        
        .file-upload-label:hover {
            border-color: #5a6fd8;
            background: rgba(102, 126, 234, 0.1);
        }
        
        .file-info {
            margin-top: 0.5rem;
            color: #666;
            font-size: 14px;
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
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .btn-back {
    background-color: #fbbf24; /* yellow-500 */
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    text-decoration: none;
}
.btn-back:hover {
    background-color: #d97706; /* yellow-600 */
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
        
        .progress {
            width: 100%;
            height: 4px;
            background: #e1e5e9;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 1rem;
            display: none;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .revision-info {
            background: rgba(102, 126, 234, 0.1);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .revision-info h4 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .file-list {
            margin-top: 2rem;
        }
        
        .file-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
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
        
        .download-btn {
            background: #28a745;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            margin-left: 10px;
        }
        
        .download-btn:hover {
            background: #218838;
        }
        
        .no-files {
            text-align: center;
            color: #666;
            padding: 2rem;
            font-style: italic;
        }
        
        .search-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .input-group {
            display: flex;
            gap: 10px;
        }
        
        .input-group input {
            flex: 1;
        }
        
        .input-group button {
            flex: 0 0 auto;
            width: auto;
        }
    </style>
</head>
<body>
    <div class="mb-4">
    <a href="Backoffice.php" class="btn-back">Back</a>
</div>

    <div class="container">
       
        <div class="header">
            <h1>📁 Document Management System</h1>
            <p>Upload, manage, and track document revisions</p>
        </div>
        
        <div class="nav-tabs">
            <button class="nav-tab active" onclick="switchTab('upload')">📤 Upload</button>
            <button class="nav-tab" onclick="switchTab('search')">🔍 Search</button>
        </div>
        
        <div class="tab-content">
            <!-- Upload Tab -->
            <div class="tab-pane active" id="upload-tab">
                <div id="upload-message"></div>
                
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="bookingCode">Booking Code *</label>
                        <input type="text" id="bookingCode" name="bookingCode" required placeholder="Enter your booking code" onchange="checkBookingRevisions()">
                    </div>
                    
                    <div id="revision-info" style="display: none;"></div>
                    
                    <div class="form-group">
                        <label>Upload Document *</label>
                        <div class="file-upload">
                            <input type="file" id="fileInput" name="document" accept=".pdf,.doc,.docx" required>
                            <label for="fileInput" class="file-upload-label">
                                <span id="fileText">Click to select or drag and drop your file</span>
                            </label>
                        </div>
                        <div class="file-info">
                            Supported formats: PDF, DOC, DOCX (Max size: 10MB)<br>
                            Maximum 3 revisions allowed per booking code
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        📤 Upload Document
                    </button>
                    
                    <div class="progress" id="progressBar">
                        <div class="progress-bar" id="progressFill"></div>
                    </div>
                </form>
            </div>
            
            <!-- Search Tab -->
            <div class="tab-pane" id="search-tab">
                <div class="search-section">
                    <h3 style="margin-bottom: 15px;">Search Documents</h3>
                    <div class="input-group">
                        <input type="text" id="searchBookingCode" placeholder="Enter booking code to search">
                        <button type="button" class="btn btn-secondary" onclick="searchDocuments()">🔍 Search</button>
                    </div>
                </div>
                
                <div id="search-message"></div>
                <div id="search-results"></div>
            </div>
        </div>
    </div>

    <script>
        let currentRevisionCount = 0;

        // Tab switching
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
            document.querySelectorAll('.nav-tab').forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }

        // File upload handling
        const uploadForm = document.getElementById('uploadForm');
        const fileInput = document.getElementById('fileInput');
        const fileText = document.getElementById('fileText');
        const uploadMessageDiv = document.getElementById('upload-message');
        const submitBtn = document.getElementById('submitBtn');
        const progressBar = document.getElementById('progressBar');
        const progressFill = document.getElementById('progressFill');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                fileText.textContent = file.name;
                
                if (file.size > 10 * 1024 * 1024) {
                    showUploadMessage('File size must be less than 10MB', 'error');
                    this.value = '';
                    fileText.textContent = 'Click to select or drag and drop your file';
                    return;
                }
                
                const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                if (!allowedTypes.includes(file.type)) {
                    showUploadMessage('Please select a PDF or Word document', 'error');
                    this.value = '';
                    fileText.textContent = 'Click to select or drag and drop your file';
                    return;
                }
            }
        });

        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const bookingCode = document.getElementById('bookingCode').value.trim();
            const file = fileInput.files[0];
            
            if (!bookingCode) {
                showUploadMessage('Please enter a booking code', 'error');
                return;
            }
            
            if (!file) {
                showUploadMessage('Please select a file to upload', 'error');
                return;
            }
            
            if (currentRevisionCount >= 3) {
                showUploadMessage('Maximum revision limit (3) reached for this booking code', 'error');
                return;
            }
            
            uploadFile();
        });

        function uploadFile() {
            const formData = new FormData(uploadForm);
            
            submitBtn.disabled = true;
            submitBtn.textContent = '📤 Uploading...';
            progressBar.style.display = 'block';
            progressFill.style.width = '0%';
            
            const xhr = new XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentage = (e.loaded / e.total) * 100;
                    progressFill.style.width = percentage + '%';
                }
            });
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = '📤 Upload Document';
                    progressBar.style.display = 'none';
                    
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.success) {
                            showUploadMessage(response.message, 'success');
                            uploadForm.reset();
                            fileText.textContent = 'Click to select or drag and drop your file';
                            checkBookingRevisions(); // Refresh revision count
                        } else {
                            showUploadMessage(response.message, 'error');
                        }
                    } catch (e) {
                        showUploadMessage('An error occurred during upload', 'error');
                    }
                }
            };
            
            xhr.open('POST', 'upload_handler.php', true);
            xhr.send(formData);
        }

        function checkBookingRevisions() {
            const bookingCode = document.getElementById('bookingCode').value.trim();
            if (!bookingCode) {
                document.getElementById('revision-info').style.display = 'none';
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        displayRevisionInfo(response);
                    } catch (e) {
                        console.error('Error checking revisions');
                    }
                }
            };
            
            xhr.open('GET', 'upload_handler.php?action=check_revisions&booking_code=' + encodeURIComponent(bookingCode), true);
            xhr.send();
        }

        function displayRevisionInfo(data) {
            const revisionDiv = document.getElementById('revision-info');
            currentRevisionCount = data.revision_count;
            
            if (data.revision_count > 0) {
                let statusClass = 'success';
                let statusText = 'Available';
                
                if (data.revision_count >= 3) {
                    statusClass = 'error';
                    statusText = 'Limit Reached';
                    submitBtn.disabled = true;
                } else if (data.revision_count >= 2) {
                    statusClass = 'warning';
                    statusText = 'Last Revision';
                    submitBtn.disabled = false;
                } else {
                    submitBtn.disabled = false;
                }
                
                revisionDiv.innerHTML = `
                    <div class="revision-info">
                        <h4>📋 Booking Code Status</h4>
                        <p><strong>Current Revisions:</strong> ${data.revision_count}/3</p>
                        <p><strong>Status:</strong> <span class="${statusClass}">${statusText}</span></p>
                        <p><strong>Last Upload:</strong> ${data.last_upload || 'N/A'}</p>
                    </div>
                `;
                revisionDiv.style.display = 'block';
            } else {
                revisionDiv.style.display = 'none';
                submitBtn.disabled = false;
            }
        }

        function searchDocuments() {
            const bookingCode = document.getElementById('searchBookingCode').value.trim();
            const searchResults = document.getElementById('search-results');
            const searchMessage = document.getElementById('search-message');
            
            if (!bookingCode) {
                showSearchMessage('Please enter a booking code', 'error');
                return;
            }
            
            searchMessage.innerHTML = '';
            searchResults.innerHTML = '<div style="text-align: center; padding: 20px;">🔍 Searching...</div>';
            
            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        displaySearchResults(response);
                    } catch (e) {
                        showSearchMessage('Error occurred while searching', 'error');
                        searchResults.innerHTML = '';
                    }
                }
            };
            
            xhr.open('GET', 'upload_handler.php?action=search&booking_code=' + encodeURIComponent(bookingCode), true);
            xhr.send();
        }

        function displaySearchResults(data) {
            const searchResults = document.getElementById('search-results');
            
            if (!data.success || data.files.length === 0) {
                searchResults.innerHTML = '<div class="no-files">📂 No documents found for this booking code</div>';
                return;
            }
            
            let html = '<div class="file-list">';
            html += `<h3>📋 Documents for Booking Code: ${data.booking_code}</h3>`;
            
            data.files.forEach(file => {
                const icon = getFileIcon(file.extension);
                html += `
                    <div class="file-item">
                        <div class="file-icon">${icon}</div>
                        <div class="file-details">
                            <div class="file-name">${file.name}</div>
                            <div class="file-meta">
                                Size: ${formatFileSize(file.size)} | 
                                Uploaded: ${file.upload_date}
                                <span class="revision-badge">Rev ${file.revision}</span>
                            </div>
                        </div>
                        <a href="upload_handler.php?action=download&file=${encodeURIComponent(file.path)}" class="download-btn">📥 Download</a>
                    </div>
                `;
            });
            
            html += '</div>';
            searchResults.innerHTML = html;
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

        function showUploadMessage(text, type) {
            uploadMessageDiv.innerHTML = `<div class="message ${type}">${text}</div>`;
            setTimeout(() => {
                uploadMessageDiv.innerHTML = '';
            }, 5000);
        }

        function showSearchMessage(text, type) {
            document.getElementById('search-message').innerHTML = `<div class="message ${type}">${text}</div>`;
            setTimeout(() => {
                document.getElementById('search-message').innerHTML = '';
            }, 5000);
        }

        // Drag and drop functionality
        const fileUploadLabel = document.querySelector('.file-upload-label');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUploadLabel.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            fileUploadLabel.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            fileUploadLabel.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight(e) {
            fileUploadLabel.style.background = 'rgba(102, 126, 234, 0.2)';
        }
        
        function unhighlight(e) {
            fileUploadLabel.style.background = 'rgba(102, 126, 234, 0.05)';
        }
        
        fileUploadLabel.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                fileInput.files = files;
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
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