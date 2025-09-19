document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    // if (!isLoggedIn()) {
    //     window.location.href = 'login.html';
    //     return;
    // }

    // const acceptAllBtn = document.getElementById('acceptAllBtn');
    const downloadBtn = document.getElementById('downloadBtn');
    const originalText = document.getElementById('originalText');
    const revisedText = document.getElementById('revisedText');

    // Handle Accept All button click
    // acceptAllBtn.addEventListener('click', () => {
    //     const suggestions = revisedText.querySelectorAll('.suggestion');
    //     suggestions.forEach(suggestion => {
    //         suggestion.classList.remove('suggestion');
    //         suggestion.classList.add('final');
    //     });

    //     const errors = originalText.querySelectorAll('.error');
    //     errors.forEach(error => {
    //         error.classList.remove('error');
    //         error.classList.add('final');
    //     });

    //     // Disable the Accept All button after use
    //     acceptAllBtn.disabled = true;
    //     acceptAllBtn.style.opacity = '0.5';
    // });

    // Handle Download button click
    downloadBtn.addEventListener('click', async () => {
        try {
            // Show loading state
            downloadBtn.disabled = true;
            const buttonText = downloadBtn.querySelector('.button-text');
            const originalText = buttonText.textContent;
            buttonText.textContent = 'Preparing download...';

            // Simulate PDF generation delay
            await new Promise(resolve => setTimeout(resolve, 2000));

            // Mock PDF download
            const link = document.createElement('a');
            link.href = '#';
            link.download = 'revised_document.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Show success message
            alert('PDF downloaded successfully!');
        } catch (error) {
            console.error('Download error:', error);
            alert('Failed to download PDF. Please try again.');
        } finally {
            // Reset button state
            downloadBtn.disabled = false;
            const buttonText = downloadBtn.querySelector('.button-text');
            buttonText.textContent = 'Download PDF';
        }
    });

    // Add click handlers to suggestions for individual acceptance
    revisedText.addEventListener('click', (e) => {
        const suggestion = e.target.closest('.suggestion');
        if (suggestion) {
            suggestion.classList.remove('suggestion');
            suggestion.classList.add('final');
            
            // Find and update corresponding error in original text
            const originalErrors = originalText.querySelectorAll('.error');
            originalErrors.forEach(error => {
                if (error.textContent === suggestion.textContent) {
                    error.classList.remove('error');
                    error.classList.add('final');
                }
            });
        }
    });

    // Add hover effect to show which suggestions correspond to which errors
    revisedText.addEventListener('mouseover', (e) => {
        const suggestion = e.target.closest('.suggestion');
        if (suggestion) {
            const originalErrors = originalText.querySelectorAll('.error');
            originalErrors.forEach(error => {
                if (error.textContent === suggestion.textContent) {
                    error.style.backgroundColor = '#ff000050';
                }
            });
        }
    });

    revisedText.addEventListener('mouseout', (e) => {
        const suggestion = e.target.closest('.suggestion');
        if (suggestion) {
            const originalErrors = originalText.querySelectorAll('.error');
            originalErrors.forEach(error => {
                if (error.textContent === suggestion.textContent) {
                    error.style.backgroundColor = '#ff000030';
                }
            });
        }
    });
}); 