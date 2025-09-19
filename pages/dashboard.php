<?php
session_start();

// Temporary debugging line
error_log("Dashboard Session Check: user_id = " . ($_SESSION['user_id'] ?? 'NOT SET'));

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmarTextPDF - Dashboard</title>
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
                <li class="active"><a href="dashboard.php" aria-current="page">Dashboard</a></li>
                <li><a href="upload.php">Upload PDF</a></li>
                <li><a href="comparison.php">Compare PDFs</a></li>
                <li><a href="#" onclick="logout()" class="logout-link">Logout</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <header class="top-bar">
                <div class="header-left">
                    <h1>Dashboard</h1>
                </div>
                <div class="user-info">
                    <span class="user-name">Welcome, <span id="userName"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span></span>
                </div>
            </header>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-icon"><i class="fas fa-chart-bar"></i></div>
                    <div class="card-content">
                        <h3>Total Processed</h3>
                        <div class="stat-value" id="totalProcessed">0</div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="card-content">
                        <h3>Average Improvements</h3>
                        <div class="stat-value" id="avgImprovements">0</div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-icon"><i class="fas fa-clock"></i></div>
                    <div class="card-content">
                        <h3>Average Time</h3>
                        <div class="stat-value" id="avgTime">0s</div>
                    </div>
                </div>
            </div>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Recent PDFs</h2>
                    <div style="float: right;">
                        <a href="upload.php" class="btn-primary">Upload New</a>
                    </div>
                </div>
                <div class="recent-files">
                    <div class="table-scroll-container">
                        <table class="files-table">
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Proofread File</th>
                                    <th>File Size</th>
                                    <th>Processing Time</th>
                                    <th>Upload Date</th>
                                    <th>Comparison</th>
                                </tr>
                            </thead>
                            <tbody id="recentFilesList" style="overflow: auto;">

                            </tbody>
                        </table>
                    </div>
                </div>
        </main>
    </div>
    <script src="../scripts/auth.js"></script>
    <script>
        // Load dashboard statistics
        function loadDashboardStats() {
            fetch('../api/get_dashboard_stats.php')
                .then(response => response.json()) // Parse JSON
                .then(data => {
                    // Check if the response is successful
                    if (data.message === 'success') {
                        document.getElementById('totalProcessed').textContent = data.stats.totalProcessed;
                        document.getElementById('avgImprovements').textContent = data.stats.averageErrorCount;
                        document.getElementById('avgTime').textContent = data.stats.averageTime;
                    } else {
                        console.error('Failed to load stats:', data.message);
                    }
                })
                .catch(error => console.error('Error loading stats:', error));
        }


        function formatFileSize(bytes) {
            if (bytes < 1024) {
                return bytes + 'B'; // bytes
            } else if (bytes < 1024 * 1024) {
                return (bytes / 1024).toFixed(2) + 'KB'; // kilobytes
            } else if (bytes < 1024 * 1024 * 1024) {
                return (bytes / (1024 * 1024)).toFixed(2) + 'MB'; // megabytes
            } else {
                return (bytes / (1024 * 1024 * 1024)).toFixed(2) + 'GB'; // gigabytes
            }
        }


        function loadRecentFiles() {
            fetch('../api/get_recent_files.php')
                .then(response => response.json())
                .then(data => {
                    if (data.message === 'success') {
                        document.getElementById('recentFilesList').innerHTML = '';
                        data.result.forEach(element => {
                            const row = document.createElement('tr');
                            let excludeKeys = ['processed_id', 'upload_id']; // keys to exclude from display

                            for (const key in element) {

                                // Only display keys that are not in the exclude list
                                if (element.hasOwnProperty(key) && !excludeKeys.includes(key)) {
                                    let cell = document.createElement('td');

                                    if (key === 'original_filename') {
                                        const fileLink = document.createElement('a');
                                        fileLink.style.cursor = 'grab';
                                        fileLink.style.color = '#332219';
                                        fileLink.style.fontWeight = 'bold';
                                        fileLink.href = element['original_file'];
                                        fileLink.textContent = element[key];
                                        fileLink.target = "_blank";
                                        cell.appendChild(fileLink);
                                    } else if (key === 'file_size') {
                                        cell.textContent = formatFileSize(element[key]);
                                    } else if (key === 'json_data') {
                                        const button = document.createElement('button');
                                        button.textContent = 'VIEW';
                                        button.classList.add('btn-primary');

                                        // Fetch the hashed version of processed_id from the server
                                        fetch(`../api/hash_id.php?processed_id=${element['processed_id']}`)
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.encoded_id) {
                                                    // Use the hashed ID in the URL
                                                    button.addEventListener('click', () => {
                                                        window.location.href = `./comparison.php?id=${encodeURIComponent(data.encoded_id)}`;
                                                    });
                                                } else {
                                                    console.error('Error hashing processed_id:', data.error);
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error fetching hashed ID:', error);
                                            });

                                        cell.appendChild(button);
                                    } else {
                                        cell.textContent = element[key];
                                    }
                                    row.appendChild(cell);
                                }
                            }
                            document.getElementById('recentFilesList').appendChild(row);
                        });
                    } else {
                        console.error('Error loading files:', data.message);
                    }
                })
                .catch(error => console.error('Error loading files:', error));
        }


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

        // File actions
        function viewFile(uploadId) {
            window.location.href = `view.php?id=${uploadId}`;
        }

        function downloadFile(uploadId) {
            window.location.href = `../api/download.php?id=${uploadId}`;
        }

        function deleteFile(uploadId) {
            if (confirm('Are you sure you want to delete this file?')) {
                fetch('../api/delete_file.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            upload_id: uploadId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadRecentFiles();
                        } else {
                            alert(data.message || 'Failed to delete file');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to delete file');
                    });
            }
        }

        // Load data on page load
        loadDashboardStats();
        loadRecentFiles();

        // Refresh data every 30 seconds
        setInterval(() => {
            loadDashboardStats();
            loadRecentFiles();
        }, 30000);
    </script>
</body>

</html>