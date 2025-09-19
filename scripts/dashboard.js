// Dashboard specific JavaScript

document.addEventListener('DOMContentLoaded', () => {
    // Load dashboard data
    loadDashboardData();
});

async function loadDashboardData() {
    try {
        // *** Replace with actual API calls to fetch data from your PHP backend ***

        // Mock data for demonstration
        const mockData = {
            stats: {
                totalProcessed: 156,
                todayProcessed: 12,
                processingRate: 98.5,
                avgTime: '2.3s'
            },
            recentFiles: [
                {
                    name: 'Document_1.pdf',
                    date: '2024-03-20 14:30',
                    status: 'completed'
                },
                {
                    name: 'Report_2024.pdf',
                    date: '2024-03-20 13:15',
                    status: 'processing'
                },
                {
                    name: 'Analysis_Q1.pdf',
                    date: '2024-03-20 11:45',
                    status: 'completed'
                },
                {
                    name: 'Meeting_Notes.pdf',
                    date: '2024-03-19 16:20',
                    status: 'failed'
                },
                {
                    name: 'Project_Plan.pdf',
                    date: '2024-03-19 15:00',
                    status: 'completed'
                }
            ]
        };

        updateDashboard(mockData);
    } catch (error) {
        console.error('Error loading dashboard data:', error);
        // You could implement a more sophisticated error display system on the dashboard
        alert('Failed to load dashboard data');
    }
}

function updateDashboard(data) {
    // Update statistics
    const totalProcessedEl = document.getElementById('totalProcessed');
    const todayProcessedEl = document.getElementById('todayProcessed');
    const processingRateEl = document.getElementById('processingRate');
    const avgTimeEl = document.getElementById('avgTime');

    if (totalProcessedEl) totalProcessedEl.textContent = data.stats.totalProcessed;
    if (todayProcessedEl) todayProcessedEl.textContent = data.stats.todayProcessed;
    if (processingRateEl) processingRateEl.textContent = `${data.stats.processingRate}%`;
    if (avgTimeEl) avgTimeEl.textContent = data.stats.avgTime;

    // Update recent files
    const recentFilesList = document.getElementById('recentFilesList');
    if (recentFilesList) {
        if (data.recentFiles.length === 0) {
            recentFilesList.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center">No recent files</td>
                </tr>
            `;
        } else {
            recentFilesList.innerHTML = data.recentFiles.map(file => `
                <tr>
                    <td>${file.custom_name || file.original_filename}</td>
                    <td>${new Date(file.upload_date).toLocaleString()}</td>
                    <td>
                        <span class="file-status status-${file.status}">
                            ${file.status.charAt(0).toUpperCase() + file.status.slice(1)}
                        </span>
                    </td>
                    <td>
                        <div class="file-actions">
                            ${file.status === 'completed' ? `
                                <button class="btn-icon view-btn" onclick="viewFile(${file.upload_id})" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon download-btn" onclick="downloadFile(${file.upload_id})" title="Download">
                                    <i class="fas fa-download"></i>
                                </button>
                            ` : ''}
                            <button class="btn-icon delete-btn" onclick="deleteFile(${file.upload_id})" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
    }
}

function viewFile(uploadId) {
    // Implement file viewing functionality - navigate to comparison page with uploadId
    console.log(`Viewing file with ID: ${uploadId}`);
    // Redirect to comparison page, passing the upload ID
    window.location.href = `comparison.php?upload_id=${uploadId}`;
}

function downloadFile(uploadId) {
    // Implement file download functionality
    console.log(`Downloading file with ID: ${uploadId}`);
    // You would typically make an API call here that prompts a file download
     window.location.href = `../api/download.php?id=${uploadId}`;
}

function deleteFile(uploadId) {
    if (confirm(`Are you sure you want to delete upload ID ${uploadId}?`)) {
        // Implement file deletion functionality
        console.log(`Deleting file with ID: ${uploadId}`);
        // You would typically make an API call here
         fetch('../api/delete_upload.php', {
             method: 'POST',
             headers: {
                 'Content-Type': 'application/json',
             },
             body: JSON.stringify({ upload_id: uploadId })
         })
         .then(response => response.json())
         .then(data => {
             if (data.success) {
                 console.log('File deleted successfully');
                 loadRecentFiles(); // Refresh the list
             } else {
                 console.error('Failed to delete file:', data.message);
                 alert('Failed to delete file: ' + (data.message || 'Unknown error'));
             }
         })
         .catch(error => {
             console.error('Error deleting file:', error);
             alert('An error occurred while deleting the file.');
         });
    }
}

// Function to show errors (can be more sophisticated)
function showError(message) {
    console.error('Dashboard Error:', message);
    // Example: Display error in a dedicated area on the dashboard
    // const errorDiv = document.getElementById('dashboardErrorArea');
    // if (errorDiv) { errorDiv.textContent = message; }
    alert(message); // Using alert for simplicity
}

// Removed the isLoggedIn function as session state is handled by PHP.

// Removed the logout function as it's handled by an API call (already present in the HTML) and PHP.


// Consider adding a separate script for common functions like logout if needed across pages.
// Or move the logout function directly into the <script> tag in each PHP file if it's small.

// Example: Basic logout function calling an API endpoint
/*
function logout() {
    fetch('../api/logout.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '../index.php';
            } else {
                alert('Logout failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error during logout:', error);
            alert('An error occurred during logout.');
        });
}
*/

// Add event listeners for quick action buttons
document.querySelectorAll('.quick-actions a').forEach(button => {
    button.addEventListener('click', (e) => {
        // You could add additional logic here before navigation
        // For example, checking if the user has the necessary permissions
    });
}); 