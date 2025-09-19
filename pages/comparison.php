<?php
session_start();
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
    <title>SmartTextPDF - Compare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/main.css">
    <script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
</head>

<body>
    <div id="myModal" class="modal">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h2 style="margin: 0;">Compare PDFs</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Left PDF: Original -->
                <div style="flex: 1; padding-right: 5px;">
                    <h3 style="text-align: center; margin-bottom: 10px;">Original PDF</h3>
                    <iframe id="pdfIframe1"></iframe>
                </div>

                <!-- Right PDF: Proofread -->
                <div style="flex: 1; padding-left: 5px;">
                    <h3 style="text-align: center; margin-bottom: 10px;">Proofread PDF</h3>
                    <iframe id="pdfIframe2"></iframe>
                </div>
            </div>
        </div>
    </div>



    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo">
                <img src="../assets/SmarText PDF_sidebar-logo.svg" alt="SmarTextPDF Logo" class="sidebar-logo">
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="upload.php">Upload PDF</a></li>
                <li class="active"><a href="comparison.php" aria-current="page">Compare PDFs</a></li>
                <li><a href="#" onclick="logout()" class="logout-link">Logout</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <header class="top-bar">
                <h1>Compare PDFs</h1>
                <div class="user-info">
                    <span class="user-name">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></span>
                </div>
            </header>

            <div class="comparison-container" id="comparison_main_container" hidden>
                <div id="notify" class="alert" style="text-align: center;" hidden></div>
                <div class="comparison-header">
                    <div class="comparison-title">
                        <h3>Document Comparison</h3>
                        <p class="file-name" id="filenamePDF"></p>
                    </div>
                    <div class="comparison-actions">
                        <button class="btn-primary" id="acceptAllChangesBtn" disabled>
                            <span class="button-text">Apply Changes</span>
                        </button>
                        <button class="btn-primary" id="previewBtn" onclick="displayPDF()">
                            <span class="button-text">Preview PDFs</span>
                        </button>
                        <!-- <button class="btn-primary" id="downloadBtn">
                            <span class="button-text">Download PDF</span>
                        </button> -->
                    </div>
                </div>

                <div class="comparison-content">
                    <div class="comparison-panel original">
                        <h4>Original Text</h4>
                        <div class="text-content" id="originalText">
                        </div>
                    </div>

                    <div class="comparison-panel revised">
                        <h4>Revised Text</h4>
                        <div class="text-content" id="revisedText">
                        </div>
                    </div>
                </div>

                <div class="tts-controls">
                    <div class="tts-header">
                        <h4>Text-to-Speech</h4>
                        <div class="tts-actions">
                            <button class="btn-secondary" id="ttsPlayBtn" aria-label="Play text">
                                <span class="button-text">Play</span>
                            </button>
                            <button class="btn-secondary" id="ttsPauseBtn" disabled aria-label="Pause text">
                                <span class="button-text">Pause</span>
                            </button>
                            <button class="btn-secondary" id="ttsStopBtn" disabled aria-label="Stop text">
                                <span class="button-text">Stop</span>
                            </button>
                        </div>
                    </div>
                    <div class="tts-settings">
                        <div class="speed-control">
                            <label for="ttsSpeed">Speed:</label>
                            <input type="range" id="ttsSpeed" min="0.5" max="2" step="0.1" value="1" aria-label="Speech speed">
                            <span id="speedValue">1x</span>
                        </div>
                        <div class="voice-select">
                            <label for="ttsVoice">Voice:</label>
                            <select id="ttsVoice" aria-label="Select voice"></select>
                        </div>
                    </div>
                    <div class="tts-progress">
                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress" id="ttsProgress"></div>
                        </div>
                        <div class="time-display">
                            <span id="currentTime">0:00</span> / <span id="totalTime">0:00</span>
                        </div>
                    </div>
                </div>
                <div class="comparison-legend" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem">
                    <div style="display: flex; gap: 1rem">
                        <div class="legend-item">
                            <span class="legend-color error"></span>
                            <span>Error</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color suggestion"></span>
                            <span>Suggestion</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color final"></span>
                            <span>Accepted</span>
                        </div>
                    </div>
                    <div class="legend-item" style="font-weight: bold;">
                        <span>Total Improvements: </span><span id="totalimprovementslegend">0</span>
                    </div>
                </div>
            </div>
            <div class="comparison-container" id="comparison_secondary_container" style="display: flex; justify-content: center; align-items: center; height: 50vh;" hidden>
                <div class="comparison-header" style="text-align: center;">
                    <div class="comparison-title" style="margin: 20px;">
                        <h3>Direct to Dashboard to Specify File for Comparison</h3>
                        <button class="btn btn-primary" style="display: flex; align-items: center; justify-content: center; padding: 10px 20px; cursor: pointer; border-radius: 4px; transition: background-color 0.3s; font-size: 16px;" onclick="window.location.href='./dashboard.php'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-house-door" viewBox="0 0 16 16" style="margin-right: 8px;">
                                <path d="M8 3.293l3.5 3.5V9h2v5H3V9h2V6.793L8 3.293zM7 8v3H5V8H4V5h1V3.5l4-4 4 4V5h1v2H9z" />
                            </svg>
                            Go to Dashboard
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/diff@5.1.0/dist/diff.min.js"></script>
    <script>
        let processedFileInformation = null
        let originalJsonData = null;
        let loadedJSONfile = null;


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

        async function fetchFileInformation(id) {
            try {
                const response = await fetch(`../api/get_current_file_data.php?processed_id=${id}`)

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`)
                }

                const data = await response.json()
                if (data.message === 'success') {
                    console.log('Fetched File Data:', data.info)
                    processedFileInformation = data.info
                    displayComparison(data.info[0]?.proof_data_path)
                    loadedJSONfile = data.info[0]?.proof_data_path
                    document.getElementById('filenamePDF').innerHTML = processedFileInformation[0]?.processed_file_path
                    document.getElementById('totalimprovementslegend').innerHTML = processedFileInformation[0]?.error_count
                } else {
                    console.error('Error from API:', data.message)
                }

            } catch (error) {
                console.error('Fetch error:', error)
            }
        }

        function displayPDF() {
            if (processedFileInformation) {
                console.log(processedFileInformation[0]?.processed_file_path)
                openModal(processedFileInformation[0]?.processed_file_path, processedFileInformation[0]?.file_path)
                document.getElementById('filenamePDF').innerHTML = processedFileInformation[0]?.processed_file_path
            }
        }



        function openModal(pdfPath1, pdfPath2) {
            document.getElementById("myModal").style.display = 'block';
            document.getElementById("pdfIframe2").src = '../processed_pdfs/' + pdfPath1;
            document.getElementById("pdfIframe1").src = pdfPath2;
        }

        function closeModal() {
            document.getElementById("myModal").style.display = 'none';
            document.getElementById("pdfIframe1").src = '';
            document.getElementById("pdfIframe2").src = '';
        }

        function getChangedParagraphs() {
            const revisedParagraphs = document.querySelectorAll('#revisedText p[data-changed="true"]');
            const changed = [];

            revisedParagraphs.forEach((p) => {
                const paragraphIndex = [...document.querySelectorAll('#revisedText p')].indexOf(p);
                const originalTokens = originalJsonData.paragraphs[paragraphIndex]?.proofread_token || [];

                const changedTokens = [];
                const reconstructedTokens = [];

                p.childNodes.forEach((node) => {
                    let word = null;
                    let idx = null;

                    if (node.nodeType === Node.ELEMENT_NODE) {
                        if (node.classList.contains('suggestion-container')) {
                            word = node.querySelector('.suggestion-word')?.textContent?.trim();
                            idx = parseInt(node.getAttribute('data-idx'), 10);
                        } else {
                            word = node.textContent.trim();
                            idx = parseInt(node.getAttribute('data-idx'), 10);
                        }

                        if (!isNaN(idx) && word !== null) {
                            reconstructedTokens.push({
                                idx,
                                word
                            });

                            const originalToken = originalTokens.find(t => t.idx === idx);
                            if (originalToken && originalToken.word !== word) {
                                changedTokens.push({
                                    word,
                                    idx
                                });
                            }
                        }
                    }
                });

                // Sort by idx and build the cleaned-up paragraph text
                reconstructedTokens.sort((a, b) => a.idx - b.idx);
                const paragraphText = reconstructedTokens.map(t => t.word).join(' ');

                if (changedTokens.length > 0) {
                    changed.push({
                        paragraph_index: paragraphIndex + 1,
                        changed_tokens: changedTokens,
                        paragraph_text: paragraphText
                    });
                }
            });

            return changed;
        }




        async function acceptAllChanges() {
            const changedParagraphs = getChangedParagraphs();

            try {
                const response = await fetch('../api/submit_accept_changes.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        json_file: loadedJSONfile,
                        changed: changedParagraphs,
                        pdf_file: processedFileInformation[0]?.processed_file_path
                    })
                });

                const result = await response.json();
                console.log('Server response:', result);
                return result;
            } catch (error) {
                console.error('Error submitting changes:', error);
                return {
                    error: true
                };
            }
        }





        async function displayComparison(json_file) {
            try {
                const response = await fetch(`../jsons/${json_file}`);
                if (!response.ok) throw new Error('Network response was not ok');

                const data = await response.json();
                originalJsonData = data;

                const originalTextField = document.getElementById('originalText');
                const revisedTextField = document.getElementById('revisedText');

                // Clear existing content
                originalTextField.innerHTML = '';
                revisedTextField.innerHTML = '';

                for (const paragraph of data.paragraphs) {
                    console.log(paragraph.proofread_token)
                    const originalErrors = paragraph.original_text || [];
                    const revisedCorrections = paragraph.revised_text || [];

                    // ORIGINAL TEXT - Highlight errors
                    const originalTokens = paragraph.original_token.map((token) => {
                        const error = originalErrors.find(err => err.index === token.idx && err.type === 'error');
                        if (error) {
                            return `<span class="error">${token.word}</span>`;
                        }
                        return token.word;
                    });

                    // PROOFREAD TEXT - Handle inserted, replaced, corrected
                    const revisedTokens = paragraph.proofread_token.map((token) => {
                        const correction = revisedCorrections.find(c => c.index === token.idx);

                        // Default display is always the token's original word
                        const displayWord = token.word;

                        if (correction) {
                            // Case: replaced — show dropdown if multiple suggestions
                            if (correction.type === 'replaced' && correction.suggestions.length > 1) {
                                const options = [
                                    '<option value="">suggestion(s):</option>',
                                    ...correction.suggestions.map(s => `<option value="${s}">${s}</option>`)
                                ].join('');
                                return `
        <span class="suggestion-container" data-idx="${token.idx}">
            <span class="suggestion-word">${displayWord}</span>
            <select class="floating-select" style="display:none;">
                ${options}
            </select>
        </span>
    `;
                            }

                            // Case: replaced — use accepted word directly if there is only one suggestion
                            if (correction.type === 'replaced' && correction.suggestions.length === 1) {
                                return `<span class="accepted" data-idx="${token.idx}">${displayWord}</span>`;
                            }

                            // Case: corrected — wrap with accepted class
                            if (correction.type === 'corrected') {
                                return `<span class="accepted" data-idx="${token.idx}">${displayWord}</span>`;
                            }

                            // Case: inserted — wrap with accepted class (still use token.word)
                            if (correction.type === 'inserted') {
                                return `<span class="accepted" data-idx="${token.idx}">${displayWord}</span>`;
                            }

                            // Case: removed — skip rendering
                            if (correction.type === 'removed') {
                                return '';
                            }

                        }

                        // Default case — show original word
                        return `<span data-idx="${token.idx}">${displayWord}</span>`;
                    });

                    originalTextField.innerHTML += `<p>${originalTokens.join(' ')}</p><br>`;
                    revisedTextField.innerHTML += `<p>${revisedTokens.join(' ')}</p><br>`;
                }

                // Click to toggle dropdown
                document.querySelectorAll('.suggestion-word').forEach((wordElement) => {
                    wordElement.addEventListener('click', function(event) {
                        document.querySelectorAll('.floating-select').forEach(dropdown => {
                            dropdown.style.display = 'none';
                        });

                        const select = this.nextElementSibling;
                        select.style.display = select.style.display === 'block' ? 'none' : 'block';
                        event.stopPropagation();
                    });
                });

                // Dropdown selection changes text
                document.querySelectorAll('.floating-select').forEach((dropdown) => {
                    dropdown.addEventListener('change', function() {

                        if (this.value === '') {
                            return
                        }

                        const selectedOption = this.value;
                        const suggestionWord = this.previousElementSibling;
                        suggestionWord.textContent = selectedOption;
                        this.style.display = 'none';

                        const paragraph = this.closest('p');
                        paragraph?.setAttribute('data-changed', 'true');

                        document.getElementById('acceptAllChangesBtn')?.removeAttribute('disabled');
                    });

                });

                // Hide dropdowns on outside click
                document.addEventListener('click', function(event) {
                    document.querySelectorAll('.floating-select').forEach((dropdown) => {
                        if (!dropdown.contains(event.target) && !dropdown.previousElementSibling.contains(event.target)) {
                            dropdown.style.display = 'none';
                        }
                    });
                });

            } catch (error) {
                console.error('There was a problem with the fetch operation:', error);
            }
        }


        document.getElementById('acceptAllChangesBtn').addEventListener('click', async function() {
            this.disabled = true;
            this.innerHTML = 'Saving changes...';
            document.getElementById('previewBtn').disabled = true;
            // document.getElementById('downloadBtn').disabled = true;

            let acceptChanges = await acceptAllChanges()

            console.log(acceptChanges)

            if (acceptChanges.success) {
                const params = new URLSearchParams(window.location.search);
                const id = params.get('id');

                const mainContainer = document.getElementById('comparison_main_container');
                const secondaryContainer = document.getElementById('comparison_secondary_container');

                if (mainContainer && secondaryContainer) {
                    if (id) {
                        // Show the main container and hide the secondary container
                        mainContainer.style.display = 'block';
                        secondaryContainer.style.display = 'none';
                        // await displayComparison(id);
                        fetchFileInformation(id)
                    } else {
                        // Hide the main container and show the secondary container
                        mainContainer.style.display = 'none';
                        secondaryContainer.style.display = 'flex';
                    }
                }
                showAlert('success', 'Applying changes is successful!')
                document.getElementById('acceptAllChangesBtn').innerHTML = 'Apply Changes';
                document.getElementById('previewBtn').disabled = false;
                // document.getElementById('downloadBtn').disabled = false;
            } else {
                showAlert('error', 'Applying changes is unsuccessful! <br> Please try again')
            }

        })


        window.onload = async () => {
            const params = new URLSearchParams(window.location.search);
            const id = params.get('id');

            const mainContainer = document.getElementById('comparison_main_container');
            const secondaryContainer = document.getElementById('comparison_secondary_container');

            if (mainContainer && secondaryContainer) {
                if (id) {
                    // Show the main container and hide the secondary container
                    mainContainer.style.display = 'block';
                    secondaryContainer.style.display = 'none';
                    // await displayComparison(id);
                    fetchFileInformation(id)
                } else {
                    // Hide the main container and show the secondary container
                    mainContainer.style.display = 'none';
                    secondaryContainer.style.display = 'flex';
                }
            }
        };
    </script>
    <script src="../scripts/auth.js"></script>
    <script src="../assets/showAlert.js"></script>
    <script src="../scripts/tts.js"></script>
</body>

</html>

<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
    }

    /* Modal Content */
    .modal-content {
        border-radius: 5px;
        background-color: #fefefe;
        margin: 1% auto;
        padding: 0;
        border: 1px solid #888;
        width: 90%;
        height: 95%;
        display: flex;
        flex-direction: column;
    }

    /* Modal Header */
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px;
        border-bottom: 1px solid #ccc;
        background-color: #f1f1f1;
    }

    /* Modal Body for PDFs */
    .modal-body {
        display: flex;
        gap: 10px;
        flex: 1;
        padding: 10px;
        overflow: hidden;
    }

    /* PDF iframes */
    .modal-content iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    /* Labels above the PDFs */
    .modal-body h3 {
        font-size: 18px;
        margin-bottom: 10px;
        color: #333;
        text-align: center;
    }

    /* The Close Button */
    .close {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
    }
</style>