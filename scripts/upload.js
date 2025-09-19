document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    if (!isLoggedIn()) {
        window.location.href = 'login.html';
        return;
    }

    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('pdfFile');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeFile = document.getElementById('removeFile');
    const processButton = document.getElementById('processButton');
    const uploadForm = document.getElementById('uploadForm');
    const uploadHistory = document.getElementById('uploadHistory');
    const disclaimer = document.getElementById('disclaimer');

    // Initialize disclaimer
    disclaimer.innerHTML = `
        <div class="disclaimer-message">
            <i class="fas fa-shield-alt"></i>
            <p>Files are processed locally and not stored on our servers. Your data remains private and secure.</p>
        </div>
    `;

    // Constants for file validation
    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB in bytes
    const ALLOWED_FILE_TYPE = 'application/pdf';

    // File validation function
    function validateFile(file) {
        if (file.type !== ALLOWED_FILE_TYPE) {
            showError('Please upload a PDF file only.');
            return false;
        }
        
        if (file.size > MAX_FILE_SIZE) {
            showError('File size must be less than 10MB.');
            return false;
        }
        
        return true;
    }

    // Show error message
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        uploadForm.insertBefore(errorDiv, uploadForm.firstChild);
        setTimeout(() => errorDiv.remove(), 5000);
    }

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Update file info display
    function updateFileInfo(file) {
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        fileInfo.style.display = 'flex';
        processButton.disabled = false;
    }

    // Clear file info
    function clearFileInfo() {
        fileInput.value = '';
        fileInfo.style.display = 'none';
        processButton.disabled = true;
        sessionStorage.removeItem('currentFile');
    }

    // Handle file selection
    function handleFileSelect(file) {
        if (!validateFile(file)) {
            clearFileInfo();
            return;
        }

        // Store file info in sessionStorage
        const fileInfo = {
            name: file.name,
            size: file.size,
            type: file.type,
            lastModified: file.lastModified
        };
        sessionStorage.setItem('currentFile', JSON.stringify(fileInfo));

        updateFileInfo(file);
        updateUploadHistory(file);
    }

    // Update upload history
    function updateUploadHistory(file) {
        let history = JSON.parse(sessionStorage.getItem('uploadHistory') || '[]');
        history.unshift({
            name: file.name,
            size: file.size,
            date: new Date().toISOString()
        });
        // Keep only last 5 uploads
        history = history.slice(0, 5);
        sessionStorage.setItem('uploadHistory', JSON.stringify(history));
        displayUploadHistory();
    }

    // Display upload history
    function displayUploadHistory() {
        const history = JSON.parse(sessionStorage.getItem('uploadHistory') || '[]');
        if (history.length === 0) {
            uploadHistory.innerHTML = '<p>No recent uploads</p>';
            return;
        }

        uploadHistory.innerHTML = history.map(file => `
            <div class="history-item">
                <div class="file-info">
                    <span class="file-name">${file.name}</span>
                    <span class="file-size">${formatFileSize(file.size)}</span>
                </div>
                <span class="upload-date">${new Date(file.date).toLocaleDateString()}</span>
            </div>
        `).join('');
    }

    // Drag and drop handlers
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });

    // Click to upload
    dropZone.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });

    // Remove file
    removeFile.addEventListener('click', () => {
        clearFileInfo();
    });

    // Form submission
    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const file = fileInput.files[0];
        if (!file) {
            showError('Please select a file first.');
            return;
        }

        if (!validateFile(file)) {
            return;
        }

        // Show loading state
        const buttonText = processButton.querySelector('.button-text');
        const loadingSpinner = processButton.querySelector('.loading-spinner');
        buttonText.style.display = 'none';
        loadingSpinner.style.display = 'inline-block';
        processButton.disabled = true;

        try {
            // Process the file (mock implementation)
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            // Clear session storage after successful processing
            sessionStorage.removeItem('currentFile');
            
            // Redirect to comparison page
            window.location.href = 'comparison.html';
        } catch (error) {
            showError('Error processing file. Please try again.');
            buttonText.style.display = 'inline';
            loadingSpinner.style.display = 'none';
            processButton.disabled = false;
        }
    });

    // Initialize upload history on page load
    displayUploadHistory();
    
    // Check for existing file in sessionStorage
    const savedFile = sessionStorage.getItem('currentFile');
    if (savedFile) {
        const fileInfo = JSON.parse(savedFile);
        const file = new File([], fileInfo.name, { type: fileInfo.type });
        Object.defineProperty(file, 'size', { value: fileInfo.size });
        updateFileInfo(file);
    }
});

// Mock grammar check function
function mockGrammarCheck(text) {
    // Simulate API delay
    return new Promise((resolve) => {
        setTimeout(() => {
            // Sample grammar errors and suggestions
            const errors = [
                {
                    position: [0, 5],
                    suggestion: "swift",
                    type: "word_choice",
                    message: "Consider using a more precise word"
                },
                {
                    position: [10, 15],
                    suggestion: "demonstration",
                    type: "word_choice",
                    message: "More formal alternative available"
                },
                {
                    position: [20, 25],
                    suggestion: "climate",
                    type: "word_choice",
                    message: "Consider context-appropriate term"
                }
            ];

            resolve({
                success: true,
                errors: errors,
                suggestions: errors.map(error => ({
                    original: text.slice(error.position[0], error.position[1]),
                    correction: error.suggestion,
                    type: error.type,
                    message: error.message
                }))
            });
        }, 1500);
    });
}

// Enhanced processPDF function
async function processPDF(formData) {
    try {
        // Get the text content from the PDF
        const text = "The quick brown fox jumps over the lazy dog. This is a sample text with some intentional errors.";
        
        // Perform grammar check
        const grammarResult = await mockGrammarCheck(text);
        
        if (!grammarResult.success) {
            throw new Error('Grammar check failed');
        }

        // Create PDF with corrections
        const { PDFDocument, rgb } = PDFLib;
        const pdfDoc = await PDFDocument.create();
        const page = pdfDoc.addPage();
        const { width, height } = page.getSize();

        // Add text with corrections
        page.drawText(text, {
            x: 50,
            y: height - 50,
            size: 12,
            color: rgb(0, 0, 0)
        });

        // Add corrections as annotations
        grammarResult.suggestions.forEach((suggestion, index) => {
            page.drawRectangle({
                x: 50 + (suggestion.original.length * 7),
                y: height - 70 - (index * 20),
                width: 100,
                height: 20,
                color: rgb(1, 0, 0),
                opacity: 0.1
            });

            page.drawText(`Suggestion: ${suggestion.correction}`, {
                x: 55 + (suggestion.original.length * 7),
                y: height - 65 - (index * 20),
                size: 10,
                color: rgb(1, 0, 0)
            });
        });

        // Save the PDF
        const pdfBytes = await pdfDoc.save();

        return {
            success: true,
            file: {
                name: formData.get('customName') || formData.get('pdfFile').name,
                size: formData.get('pdfFile').size,
                processed: true,
                timestamp: new Date().toISOString(),
                corrections: grammarResult.suggestions,
                pdfData: pdfBytes
            }
        };
    } catch (error) {
        console.error('PDF processing error:', error);
        return {
            success: false,
            message: 'Failed to process PDF'
        };
    }
} 