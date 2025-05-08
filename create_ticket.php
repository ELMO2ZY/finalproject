<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Function to generate a random 16-digit account number
function generateAccountNumber() {
    $accountNumber = '';
    for ($i = 0; $i < 16; $i++) {
        $accountNumber .= mt_rand(0, 9);
    }
    return $accountNumber;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? 'other';
    $priority = 'medium'; // Default priority
    $user_id = $_SESSION['user_id'];

    // Validate required fields
    if (empty($subject) || empty($description)) {
        $error = "Subject and description are required.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO tickets (user_id, subject, description, category, priority, status) VALUES (?, ?, ?, ?, ?, 'open')");
            if ($stmt->execute([$user_id, $subject, $description, $category, $priority])) {
                $_SESSION['ticket_success'] = "Ticket created successfully!";
                header("Location: view_tickets.php");
                exit();
            } else {
                $error = "Failed to create ticket. Please try again.";
            }
        } catch(PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ticket - Support Ticket System</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="STStr.svg">
    <link rel="icon" type="image/png" href="STStr.png">
    <link rel="apple-touch-icon" href="STStr.png">
    <link rel="stylesheet" href="theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .ticket-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 2rem auto;
            position: relative;
            overflow: hidden;
        }

        .ticket-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #5e7eb6, #447cf5);
        }

        .ticket-form h1 {
            color: #333;
            margin-bottom: 2rem;
            font-size: 2rem;
            position: relative;
            padding-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 2rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #5e7eb6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(94, 126, 182, 0.2);
            background: white;
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .category-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .category-option {
            position: relative;
            cursor: pointer;
        }

        .category-option input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .category-label {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .category-label i {
            margin-right: 10px;
            font-size: 1.2rem;
            color: #5e7eb6;
        }

        .category-option input:checked + .category-label {
            background: #5e7eb6;
            color: white;
            border-color: #5e7eb6;
        }

        .category-option input:checked + .category-label i {
            color: white;
        }

        .submit-btn {
            background: #5e7eb6;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin-top: 2rem;
        }

        .submit-btn:hover {
            background: #447cf5;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(94, 126, 182, 0.3);
        }

        .submit-btn i {
            font-size: 1.2rem;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: none;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .back-btn {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: #5e7eb6;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            color: #447cf5;
            transform: translateX(-5px);
        }

        .form-group .char-count {
            position: absolute;
            right: 10px;
            bottom: 10px;
            color: #666;
            font-size: 0.9rem;
        }
        
        /* New styles for enhanced features */
        .progress-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .progress-bar::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 3px;
            background: #e1e1e1;
            transform: translateY(-50%);
            z-index: 1;
        }
        
        .progress-step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: white;
            border: 2px solid #e1e1e1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #666;
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
        }
        
        .progress-step.active {
            background: #5e7eb6;
            border-color: #5e7eb6;
            color: white;
        }
        
        .progress-step.completed {
            background: #5e7eb6;
            border-color: #5e7eb6;
            color: white;
        }
        
        .progress-step.completed::after {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            font-size: 0.8rem;
        }
        
        .file-upload {
            position: relative;
            margin-top: 1rem;
        }
        
        .file-upload-label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            background: #f8f9fa;
            border: 2px dashed #e1e1e1;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-upload-label:hover {
            border-color: #5e7eb6;
            background: #f0f4ff;
        }
        
        .file-upload-label i {
            color: #5e7eb6;
            font-size: 1.2rem;
        }
        
        .file-upload input[type="file"] {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }
        
        .file-list {
            margin-top: 1rem;
            display: none;
        }
        
        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 5px;
        }
        
        .file-item i {
            color: #5e7eb6;
            margin-right: 8px;
        }
        
        .file-item .remove-file {
            color: #dc3545;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-item .remove-file:hover {
            color: #c82333;
        }
        
        .form-section {
            display: none;
            opacity: 0;
            transform: translateX(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        
        .form-section.active {
            display: block;
            opacity: 1;
            transform: translateX(0);
        }
        
        .form-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        
        .nav-btn {
            background: #f8f9fa;
            color: #5e7eb6;
            border: 2px solid #5e7eb6;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-btn:hover {
            background: #5e7eb6;
            color: white;
        }
        
        .nav-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .nav-btn:disabled:hover {
            background: #f8f9fa;
            color: #5e7eb6;
        }
        
        .form-section-title {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-section-title i {
            color: #5e7eb6;
            margin-right: 8px;
        }
        
        .priority-selector {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .priority-option {
            flex: 1;
            position: relative;
        }
        
        .priority-option input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }
        
        .priority-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .priority-label i {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }
        
        .priority-option input:checked + .priority-label {
            background: #5e7eb6;
            color: white;
            border-color: #5e7eb6;
        }
        
        .priority-option input:checked + .priority-label i {
            color: white;
        }
        
        .priority-label.low i {
            color: #28a745;
        }
        
        .priority-label.medium i {
            color: #ffc107;
        }
        
        .priority-label.high i {
            color: #dc3545;
        }
        
        .priority-option input:checked + .priority-label.low i,
        .priority-option input:checked + .priority-label.medium i,
        .priority-option input:checked + .priority-label.high i {
            color: white;
        }
        
        .form-group .help-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.5rem;
        }
        
        .form-group .required-indicator {
            color: #dc3545;
            margin-left: 4px;
        }
        
        .skip-attachments {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e1e1e1;
        }
        
        .skip-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            color: #555;
            font-size: 0.95rem;
        }
        
        .skip-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .skip-checkbox span {
            transition: color 0.3s ease;
        }
        
        .skip-checkbox:hover span {
            color: #5e7eb6;
        }

        /* Loading Spinner */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #5e7eb6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        .loading-text {
            color: #5e7eb6;
            font-size: 1.2rem;
            margin-top: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Progress Indicator */
        .progress-indicator {
            display: none;
        }

        .account-number-display {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: monospace;
            font-size: 1.1rem;
            color: #2d3748;
            border: 1px solid #e2e8f0;
        }

        .account-number-display i {
            color: #5e7eb6;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="home.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <div class="ticket-form">
            <h1>Create New Ticket</h1>
            
            <!-- Loading Overlay -->
            <div class="loading-overlay">
                <div class="spinner"></div>
                <div class="loading-text">Submitting your ticket...</div>
            </div>
            
            <div class="progress-bar">
                <div class="progress-step active" data-step="1">1</div>
                <div class="progress-step" data-step="2">2</div>
                <div class="progress-step" data-step="3">3</div>
            </div>
            
            <div id="successMessage" class="success-message">
                <i class="fas fa-check-circle"></i> Ticket created successfully!
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form id="ticketForm" action="create_ticket.php" method="POST" enctype="multipart/form-data">
                <!-- Step 1: Basic Information -->
                <div class="form-section active" data-step="1">
                    <div class="form-section-title">
                        <i class="fas fa-info-circle"></i> Basic Information
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject<span class="required-indicator">*</span></label>
                        <input type="text" name="subject" id="subject" required placeholder="Brief description of your issue">
                        <div class="help-text">A concise title that describes your issue</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category<span class="required-indicator">*</span></label>
                        <div class="category-selector">
                            <div class="category-option">
                                <input type="radio" name="category" id="technical" value="technical" required>
                                <label for="technical" class="category-label">
                                    <i class="fas fa-laptop-code"></i> Technical
                                </label>
                            </div>
                            <div class="category-option">
                                <input type="radio" name="category" id="billing" value="billing">
                                <label for="billing" class="category-label">
                                    <i class="fas fa-credit-card"></i> Billing
                                </label>
                            </div>
                            <div class="category-option">
                                <input type="radio" name="category" id="account" value="account">
                                <label for="account" class="category-label">
                                    <i class="fas fa-user-circle"></i> Account
                                </label>
                            </div>
                            <div class="category-option">
                                <input type="radio" name="category" id="other" value="other">
                                <label for="other" class="category-label">
                                    <i class="fas fa-question-circle"></i> Other
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="nav-btn" id="nextBtn1">
                            Next <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Step 2: Details -->
                <div class="form-section" data-step="2">
                    <div class="form-section-title">
                        <i class="fas fa-clipboard-list"></i> Ticket Details
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description<span class="required-indicator">*</span></label>
                        <textarea name="description" id="description" required placeholder="Please provide detailed information about your issue"></textarea>
                        <span class="char-count">0/500</span>
                        <div class="help-text">Include any relevant details that will help us resolve your issue</div>
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="nav-btn" id="prevBtn2">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>
                        <button type="button" class="nav-btn" id="nextBtn2">
                            Next <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Step 3: Attachments & Submit -->
                <div class="form-section" data-step="3">
                    <div class="form-section-title">
                        <i class="fas fa-paperclip"></i> Attachments & Submit
                    </div>
                    
                    <div class="form-group">
                        <label for="attachments">Attachments</label>
                        <div class="file-upload">
                            <label for="attachments" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Click to upload files or drag and drop</span>
                            </label>
                            <input type="file" id="attachments" name="attachments[]" multiple>
                        </div>
                        <div class="file-list" id="fileList"></div>
                        <div class="help-text">Upload screenshots, documents, or any files that might help us understand your issue (max 5MB each)</div>
                        
                        <div class="skip-attachments">
                            <label class="skip-checkbox">
                                <input type="checkbox" id="skipAttachments">
                                <span>I don't have files to attach</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="nav-btn" id="prevBtn3">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i> Submit Ticket
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Character counter for description
        const description = document.getElementById('description');
        const charCount = document.querySelector('.char-count');
        
        description.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = `${count}/500`;
            
            if (count > 500) {
                this.value = this.value.substring(0, 500);
                charCount.textContent = '500/500';
            }
        });

        // Form navigation
        const formSections = document.querySelectorAll('.form-section');
        const progressSteps = document.querySelectorAll('.progress-step');
        const nextBtn1 = document.getElementById('nextBtn1');
        const nextBtn2 = document.getElementById('nextBtn2');
        const prevBtn2 = document.getElementById('prevBtn2');
        const prevBtn3 = document.getElementById('prevBtn3');
        
        function showSection(step) {
            formSections.forEach(section => {
                section.classList.remove('active');
                if (parseInt(section.dataset.step) === step) {
                    section.classList.add('active');
                }
            });
            
            progressSteps.forEach(stepEl => {
                const stepNum = parseInt(stepEl.dataset.step);
                stepEl.classList.remove('active', 'completed');
                
                if (stepNum === step) {
                    stepEl.classList.add('active');
                } else if (stepNum < step) {
                    stepEl.classList.add('completed');
                }
            });
        }
        
        nextBtn1.addEventListener('click', function() {
            const subject = document.getElementById('subject').value;
            const category = document.querySelector('input[name="category"]:checked');
            
            if (subject && category) {
                showSection(2);
            } else {
                alert('Please fill in all required fields');
            }
        });
        
        nextBtn2.addEventListener('click', function() {
            const description = document.getElementById('description').value;
            
            if (description) {
                showSection(3);
            } else {
                alert('Please fill in all required fields');
            }
        });
        
        prevBtn2.addEventListener('click', function() {
            showSection(1);
        });
        
        prevBtn3.addEventListener('click', function() {
            showSection(2);
        });
        
        // File upload handling
        const fileInput = document.getElementById('attachments');
        const fileList = document.getElementById('fileList');
        const skipAttachments = document.getElementById('skipAttachments');
        const fileUploadLabel = document.querySelector('.file-upload-label');
        
        skipAttachments.addEventListener('change', function() {
            if (this.checked) {
                fileInput.disabled = true;
                fileUploadLabel.style.opacity = '0.5';
                fileUploadLabel.style.cursor = 'not-allowed';
                fileList.style.display = 'none';
                fileList.innerHTML = '';
            } else {
                fileInput.disabled = false;
                fileUploadLabel.style.opacity = '1';
                fileUploadLabel.style.cursor = 'pointer';
            }
        });
        
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                skipAttachments.checked = false;
                skipAttachments.disabled = true;
            }
            
            fileList.style.display = 'block';
            fileList.innerHTML = '';
            
            Array.from(this.files).forEach(file => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                
                const fileIcon = document.createElement('i');
                fileIcon.className = 'fas fa-file';
                
                const fileName = document.createElement('span');
                fileName.textContent = file.name;
                
                const removeBtn = document.createElement('span');
                removeBtn.className = 'remove-file';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.addEventListener('click', function() {
                    fileItem.remove();
                    if (fileList.children.length === 0) {
                        fileList.style.display = 'none';
                        skipAttachments.disabled = false;
                    }
                });
                
                fileItem.appendChild(fileIcon);
                fileItem.appendChild(fileName);
                fileItem.appendChild(removeBtn);
                fileList.appendChild(fileItem);
            });
        });
        
        // Drag and drop functionality
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
        
        function highlight() {
            fileUploadLabel.style.borderColor = '#5e7eb6';
            fileUploadLabel.style.background = '#f0f4ff';
        }
        
        function unhighlight() {
            fileUploadLabel.style.borderColor = '#e1e1e1';
            fileUploadLabel.style.background = '#f8f9fa';
        }
        
        fileUploadLabel.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            fileInput.files = files;
            
            // Trigger the change event
            const event = new Event('change');
            fileInput.dispatchEvent(event);
        }

        // Form submission handling
        document.getElementById('ticketForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading overlay
            const loadingOverlay = document.querySelector('.loading-overlay');
            loadingOverlay.style.display = 'flex';
            
            // Submit the form
            this.submit();
        });

        // Add animation to form inputs
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-5px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>