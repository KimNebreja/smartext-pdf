<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmarTextPDF - Upload</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>


    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo">
                <img src="../assets/SmarText PDF_sidebar-logo.svg" alt="SmarTextPDF Logo" class="sidebar-logo">
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li class="active"><a href="upload.php" aria-current="page">Upload PDF</a></li>
                <li><a href="comparison.php">Compare PDFs</a></li>
                <li><a href="#" onclick="logout()" class="logout-link">Logout</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <header class="top-bar">
                <div class="header-left">
                    <h1>Upload PDF</h1>
                </div>
                <div class="user-info">
                    <span class="user-name">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                </div>
            </header>

            <div class="upload-container">
                <div id="notify" class="alert" style="text-align: center;" hidden></div>

                <div class="upload-card">
                    <div class="upload-header">
                        <img src="../assets/SmarText PDF_main-logo.svg" alt="SmarTextPDF Logo" class="main-logo">
                    </div>

                    <form method="POST" action="" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                        <div class="upload-area" id="dropZone">
                            <div class="hourglassBackground" id="hourglassLoader" hidden>
                                <div class="hourglassContainer">
                                    <div class="hourglassCurves"></div>
                                    <div class="hourglassCapTop"></div>
                                    <div class="hourglassGlassTop"></div>
                                    <div class="hourglassSand"></div>
                                    <div class="hourglassSandStream"></div>
                                    <div class="hourglassCapBottom"></div>
                                    <div class="hourglassGlass"></div>
                                </div>
                            </div>
                            <input type="file" id="pdf_file" name="pdf_file" accept=".pdf"
                                class="file-input" onchange="handleFileSelect(event)">
                            <p class="upload-text">Click to Upload or Drag and drop your PDF here</p>
                            <div class="upload-icon">
                                <img src="../assets/SmarText PDF_folder-icon.svg" alt="Upload Icon" class="folder-icon">
                            </div>
                            <div class="upload-btn-container">
                                <button type="button" class="btn-secondary upload-btn" onclick="document.getElementById('pdf_file').click()">
                                    <img src="../assets/SmarText PDF_upload-btn.svg" alt="Upload Button" class="upload-btn-icon">
                                </button>
                            </div>
                            <p class="file-info" id="fileInfo"></p>
                        </div>

                        <button type="submit" class="btn-primary btn-block" id="uploadBtn" disabled>
                            Upload and Process
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="../scripts/auth.js"></script>
    <script src="../assets/showAlert.js"></script>
    <script>
        // File upload handling
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('pdf_file');
        const fileInfo = document.getElementById('fileInfo');
        const uploadBtn = document.getElementById('uploadBtn');
        const uploadForm = document.getElementById('uploadForm');

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        // Highlight drop zone when dragging over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        // Handle dropped files
        dropZone.addEventListener('drop', handleDrop, false);

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight(e) {
            dropZone.classList.add('highlight');
        }

        function unhighlight(e) {
            dropZone.classList.remove('highlight');
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            // Set the files to the input element
            fileInput.files = files;
            // Trigger change event so 'required' is satisfied
            const event = new Event('change', {
                bubbles: true
            });
            fileInput.dispatchEvent(event);
            handleFiles(files);
        }

        // This function handles the file selection
        function handleFileSelect(e) {
            const files = e.target.files; // Get the files from the input
            handleFiles(files); // Handle the selected files
        }

        function handleFiles(files) {
            if (files.length > 0) {
                const file = files[0];
                if (file.type === 'application/pdf') {
                    fileInfo.textContent = `Selected file: ${file.name} (${formatFileSize(file.size)})`;
                    uploadBtn.disabled = false;
                } else {
                    fileInfo.textContent = 'Please select a PDF file';
                    uploadBtn.disabled = true;
                }
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function openModal() {
            document.getElementById("myModal").style.display = 'block';
        }

        function closeModal() {
            document.getElementById("myModal").style.display = 'none';
        }

        async function processProofReadingByGPT(filename) {
            const url = `../api/submit_proofreading.php?dbFilename=${encodeURIComponent(filename)}`;
            try {
                const response = await fetch(url, {
                    method: 'GET',
                })
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Error processing proofreading:', error);
                return {
                    error: true,
                    message: error.message
                };
            }
        }

        async function updateProcessedFiles(upload_id, pdf, json, time, improvements) {
            const url = `../api/submit_processed_files.php`;

            const data = {
                id: upload_id,
                pdf: pdf,
                json: json,
                time: time,
                improvements: improvements
            };

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const responseData = await response.json();
                return responseData;

            } catch (error) {
                console.error('Error processing proofreading:', error);
                return {
                    error: true,
                    message: error.message
                };
            }
        }


        uploadForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const file = fileInput.files[0];
            const formData = new FormData();

            if (!file) {
                alert('Please select a file to upload');
                return;
            }

            if (file.type !== 'application/pdf') {
                alert('Only PDF files are allowed');
                return;
            }

            uploadBtn.innerHTML = 'Proofreading the File. Please wait...';
            uploadForm.style.pointerEvents = 'none';



            formData.append('file', file);
            document.getElementById('hourglassLoader').hidden = false
            try {
                const response = await fetch('../api/submit_upload_files.php', {
                    method: 'POST',
                    body: formData
                });

                const responseText = await response.text();

                const data = JSON.parse(responseText);

                if (data.success) {
                    const proofread = await processProofReadingByGPT(data.generatedfilename);

                    if (proofread?.message === 'success') {

                        let upload_id = data.upload_id
                        let elapsed_time = proofread?.info.elapsed_time_seconds
                        let generated_pdf = proofread?.info.final_pdf_filename
                        let generated_json = proofread?.info.json_filename
                        let errors = proofread?.info.total_improvements

                        let updateProcessFile = await updateProcessedFiles(
                            upload_id,
                            generated_pdf,
                            generated_json,
                            elapsed_time,
                            errors
                        )

                        if (updateProcessFile.message == 'success') {
                            let secondsLeft = 5;
                            showAlert('success', `File processed successfully! <br> Redirecting to Compare PDF in ${secondsLeft}...`);

                            uploadBtn.innerHTML = 'Upload and Process';
                            document.getElementById('uploadBtn').disabled = true;
                            document.getElementById('pdf_file').disabled = false;
                            document.getElementById('pdf_file').value = '';
                            fileInfo.textContent = '';

                            const countdownInterval = setInterval(() => {
                                secondsLeft--;
                                showAlert('success', `File processed successfully! <br> Redirecting to Compare PDF in ${secondsLeft}...`);
                            }, 1000);

                            uploadBtn.innerHTML = 'Upload and Process';
                            setTimeout(() => {
                                clearInterval(countdownInterval);
                                    fetch(`../api/hash_id.php?processed_id=${updateProcessFile.process_id}`)
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.encoded_id) {
                                                    window.location.href = `./comparison.php?id=${encodeURIComponent(data.encoded_id)}`;
                                                } else {
                                                    console.error('Error hashing processed_id:', data.error);
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error fetching hashed ID:', error);
                                            });
                            }, 5000);
                        } else {
                            showAlert('error', 'Proofreading failed please try again.');
                            uploadBtn.innerHTML = 'Upload and Process';
                            uploadForm.style.pointerEvents = 'auto';
                        }

                    } else {
                        showAlert('error', 'Proofreading failed due to: ', proofread?.error);
                        uploadBtn.innerHTML = 'Upload and Process';
                        uploadForm.style.pointerEvents = 'auto';
                    }
                } else {
                    showAlert('error', data.message);
                    uploadBtn.innerHTML = 'Upload and Process';
                    uploadForm.style.pointerEvents = 'auto';
                }
                 document.getElementById('hourglassLoader').hidden = true
            } catch (error) {
                showAlert('error', 'An error occurred while uploading the file! ' + error);
                uploadBtn.innerHTML = 'Upload and Process';
                uploadForm.style.pointerEvents = 'auto';
                 document.getElementById('hourglassLoader').hidden = true
            }
        });

        // Logout function
        function logout() {
            fetch('../api/logout.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '../index.php';
                    }
                })
                .catch(error => console.error('Error logging out:', error));
        }
    </script>
</body>

</html>

<style>
    .modal {
        display: none;
        /* Set to block when opening modal */
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 50;
        width: 100%;
        height: 100%;
        /* Make it cover the entire viewport */
        background-color: rgba(0, 0, 0, 0.4);
    }

    /* Modal Content */
    .modal-content {
        border-radius: 5px;
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        width: auto;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        display: flex;
        justify-content: center;
        align-items: center;
        /* Could be more or less, depending on screen size */
    }

    .modal-content iframe {
        height: 100%;
        padding: 2rem;
    }

    /* The Close Button */
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    /* From Uiverse.io by SouravBandyopadhyay */
    .hourglassBackground {
        position: relative;
        background-color: rgb(71, 60, 60);
        height: 130px;
        width: 130px;
        border-radius: 50%;
        margin: 30px auto;
    }

    .hourglassContainer {
        position: absolute;
        top: 30px;
        left: 40px;
        width: 50px;
        height: 70px;
        -webkit-animation: hourglassRotate 2s ease-in 0s infinite;
        animation: hourglassRotate 2s ease-in 0s infinite;
        transform-style: preserve-3d;
        perspective: 1000px;
    }

    .hourglassContainer div,
    .hourglassContainer div:before,
    .hourglassContainer div:after {
        transform-style: preserve-3d;
    }

    @-webkit-keyframes hourglassRotate {
        0% {
            transform: rotateX(0deg);
        }

        50% {
            transform: rotateX(180deg);
        }

        100% {
            transform: rotateX(180deg);
        }
    }

    @keyframes hourglassRotate {
        0% {
            transform: rotateX(0deg);
        }

        50% {
            transform: rotateX(180deg);
        }

        100% {
            transform: rotateX(180deg);
        }
    }

    .hourglassCapTop {
        top: 0;
    }

    .hourglassCapTop:before {
        top: -25px;
    }

    .hourglassCapTop:after {
        top: -20px;
    }

    .hourglassCapBottom {
        bottom: 0;
    }

    .hourglassCapBottom:before {
        bottom: -25px;
    }

    .hourglassCapBottom:after {
        bottom: -20px;
    }

    .hourglassGlassTop {
        transform: rotateX(90deg);
        position: absolute;
        top: -16px;
        left: 3px;
        border-radius: 50%;
        width: 44px;
        height: 44px;
        background-color: #999999;
    }

    .hourglassGlass {
        perspective: 100px;
        position: absolute;
        top: 32px;
        left: 20px;
        width: 10px;
        height: 6px;
        background-color: #999999;
        opacity: 0.5;
    }

    .hourglassGlass:before,
    .hourglassGlass:after {
        content: '';
        display: block;
        position: absolute;
        background-color: #999999;
        left: -17px;
        width: 44px;
        height: 28px;
    }

    .hourglassGlass:before {
        top: -27px;
        border-radius: 0 0 25px 25px;
    }

    .hourglassGlass:after {
        bottom: -27px;
        border-radius: 25px 25px 0 0;
    }

    .hourglassCurves:before,
    .hourglassCurves:after {
        content: '';
        display: block;
        position: absolute;
        top: 32px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #333;
        animation: hideCurves 2s ease-in 0s infinite;
    }

    .hourglassCurves:before {
        left: 15px;
    }

    .hourglassCurves:after {
        left: 29px;
    }

    @-webkit-keyframes hideCurves {
        0% {
            opacity: 1;
        }

        25% {
            opacity: 0;
        }

        30% {
            opacity: 0;
        }

        40% {
            opacity: 1;
        }

        100% {
            opacity: 1;
        }
    }

    @keyframes hideCurves {
        0% {
            opacity: 1;
        }

        25% {
            opacity: 0;
        }

        30% {
            opacity: 0;
        }

        40% {
            opacity: 1;
        }

        100% {
            opacity: 1;
        }
    }

    .hourglassSandStream:before {
        content: '';
        display: block;
        position: absolute;
        left: 24px;
        width: 3px;
        background-color: white;
        -webkit-animation: sandStream1 2s ease-in 0s infinite;
        animation: sandStream1 2s ease-in 0s infinite;
    }

    .hourglassSandStream:after {
        content: '';
        display: block;
        position: absolute;
        top: 36px;
        left: 19px;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-bottom: 6px solid #fff;
        animation: sandStream2 2s ease-in 0s infinite;
    }

    @-webkit-keyframes sandStream1 {
        0% {
            height: 0;
            top: 35px;
        }

        50% {
            height: 0;
            top: 45px;
        }

        60% {
            height: 35px;
            top: 8px;
        }

        85% {
            height: 35px;
            top: 8px;
        }

        100% {
            height: 0;
            top: 8px;
        }
    }

    @keyframes sandStream1 {
        0% {
            height: 0;
            top: 35px;
        }

        50% {
            height: 0;
            top: 45px;
        }

        60% {
            height: 35px;
            top: 8px;
        }

        85% {
            height: 35px;
            top: 8px;
        }

        100% {
            height: 0;
            top: 8px;
        }
    }

    @-webkit-keyframes sandStream2 {
        0% {
            opacity: 0;
        }

        50% {
            opacity: 0;
        }

        51% {
            opacity: 1;
        }

        90% {
            opacity: 1;
        }

        91% {
            opacity: 0;
        }

        100% {
            opacity: 0;
        }
    }

    @keyframes sandStream2 {
        0% {
            opacity: 0;
        }

        50% {
            opacity: 0;
        }

        51% {
            opacity: 1;
        }

        90% {
            opacity: 1;
        }

        91% {
            opacity: 0;
        }

        100% {
            opacity: 0;
        }
    }

    .hourglassSand:before,
    .hourglassSand:after {
        content: '';
        display: block;
        position: absolute;
        left: 6px;
        background-color: white;
        perspective: 500px;
    }

    .hourglassSand:before {
        top: 8px;
        width: 39px;
        border-radius: 3px 3px 30px 30px;
        animation: sandFillup 2s ease-in 0s infinite;
    }

    .hourglassSand:after {
        border-radius: 30px 30px 3px 3px;
        animation: sandDeplete 2s ease-in 0s infinite;
    }

    @-webkit-keyframes sandFillup {
        0% {
            opacity: 0;
            height: 0;
        }

        60% {
            opacity: 1;
            height: 0;
        }

        100% {
            opacity: 1;
            height: 17px;
        }
    }

    @keyframes sandFillup {
        0% {
            opacity: 0;
            height: 0;
        }

        60% {
            opacity: 1;
            height: 0;
        }

        100% {
            opacity: 1;
            height: 17px;
        }
    }

    @-webkit-keyframes sandDeplete {
        0% {
            opacity: 0;
            top: 45px;
            height: 17px;
            width: 38px;
            left: 6px;
        }

        1% {
            opacity: 1;
            top: 45px;
            height: 17px;
            width: 38px;
            left: 6px;
        }

        24% {
            opacity: 1;
            top: 45px;
            height: 17px;
            width: 38px;
            left: 6px;
        }

        25% {
            opacity: 1;
            top: 41px;
            height: 17px;
            width: 38px;
            left: 6px;
        }

        50% {
            opacity: 1;
            top: 41px;
            height: 17px;
            width: 38px;
            left: 6px;
        }

        90% {
            opacity: 1;
            top: 41px;
            height: 0;
            width: 10px;
            left: 20px;
        }
    }

    @keyframes sandDeplete {
        0% {
            opacity: 0;
            top: 45px;
            height: 17px;
            width: 38px;
            left: 6px;
        }

        1% {
            opacity: 1;
            top: 45px;
            height: 17px;
            width: 38px;
            left: 6px;
        }

        24% {
            opacity: 1;
            top: 45px;
            height: 17px;
            width: 38px;
            left: 6px;
        }

        25% {
            opacity: 1;
            top: 41px;
            height: 17px;
            width: 38px;
            left: 6px;
        }

        50% {
            opacity: 1;
            top: 41px;
            height: 17px;
            width: 38px;
            left: 6px;
        }

        90% {
            opacity: 1;
            top: 41px;
            height: 0;
            width: 10px;
            left: 20px;
        }
    }
</style>