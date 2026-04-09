/**
 * HackathonAfrica LMS - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars on page load
    initProgressBars();
    
    // Initialize confirm dialogs
    initConfirmDialogs();
    
    // Initialize code exercises
    initCodeExercises();
    
    // Initialize hint toggles
    initHintToggles();
    
    // Track user activity
    trackActivity();
});

/**
 * Animate progress bars when they come into view
 */
function initProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar[data-width]');
    
    progressBars.forEach(bar => {
        const targetWidth = bar.getAttribute('data-width');
        setTimeout(() => {
            bar.style.width = targetWidth + '%';
        }, 200);
    });
}

/**
 * Add confirmation dialogs to dangerous actions
 */
function initConfirmDialogs() {
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Initialize code exercise editors with live preview
 */
function initCodeExercises() {
    const editors = document.querySelectorAll('.code-editor-textarea');
    
    editors.forEach(editor => {
        const previewFrame = editor.closest('.code-exercise')?.querySelector('.code-preview-iframe');
        
        if (previewFrame) {
            // Initial preview
            updatePreview(editor, previewFrame);
            
            // Live preview on input
            editor.addEventListener('input', function() {
                updatePreview(this, previewFrame);
            });
        }
    });
}

/**
 * Update the preview iframe with the editor content
 */
function updatePreview(editor, previewFrame) {
    const code = editor.value;
    const exerciseType = editor.getAttribute('data-exercise-type') || 'html';
    
    let htmlContent = '';
    
    if (exerciseType === 'html' || exerciseType === 'combined') {
        htmlContent = code;
    } else if (exerciseType === 'css') {
        htmlContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <style>${code}</style>
            </head>
            <body>
                <div class="preview-container">
                    <h1>Preview</h1>
                    <p>This is a paragraph.</p>
                    <button>Click Me</button>
                    <a href="#">A Link</a>
                    <ul>
                        <li>List Item 1</li>
                        <li>List Item 2</li>
                    </ul>
                </div>
            </body>
            </html>
        `;
    } else if (exerciseType === 'javascript') {
        htmlContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: system-ui; padding: 20px; background: #1e1e1e; color: #fff; }
                    #output { background: #0a0a0a; padding: 15px; border-radius: 4px; font-family: monospace; white-space: pre-wrap; }
                </style>
            </head>
            <body>
                <div id="output">Console output will appear here...</div>
                <script>
                    const originalLog = console.log;
                    const output = document.getElementById('output');
                    output.innerHTML = '';
                    console.log = function(...args) {
                        output.innerHTML += args.map(a => typeof a === 'object' ? JSON.stringify(a, null, 2) : a).join(' ') + '\\n';
                        originalLog.apply(console, args);
                    };
                    try {
                        ${code}
                    } catch(e) {
                        output.innerHTML += '<span style="color:#ff3b30;">Error: ' + e.message + '</span>';
                    }
                </script>
            </body>
            </html>
        `;
    }
    
    const blob = new Blob([htmlContent], { type: 'text/html' });
    previewFrame.src = URL.createObjectURL(blob);
}

/**
 * Initialize hint toggle buttons
 */
function initHintToggles() {
    const hintToggles = document.querySelectorAll('.hint-toggle');
    
    hintToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const hintContent = this.closest('.code-exercise-footer')?.querySelector('.hint-content');
            if (hintContent) {
                hintContent.classList.toggle('show');
                this.textContent = hintContent.classList.contains('show') ? 'Hide Hint' : 'Show Hint';
            }
        });
    });
}

/**
 * Track user activity for analytics
 */
function trackActivity() {
    // Update last activity timestamp
    if (document.body.dataset.userId) {
        // Could send an AJAX request here to update activity
    }
}

/**
 * Run code exercise and check against expected output
 */
function runExercise(exerciseId) {
    const exercise = document.querySelector(`[data-exercise-id="${exerciseId}"]`);
    if (!exercise) return;
    
    const editor = exercise.querySelector('.code-editor-textarea');
    const feedback = exercise.querySelector('.exercise-feedback');
    const code = editor.value.trim();
    
    // Basic validation - can be enhanced with actual test cases
    if (code.length < 10) {
        showFeedback(feedback, 'error', 'Please write more code to complete this exercise.');
        return;
    }
    
    // For HTML exercises, check for required elements
    const exerciseType = editor.getAttribute('data-exercise-type');
    
    if (exerciseType === 'html') {
        if (!code.includes('<!DOCTYPE') && !code.includes('<!doctype')) {
            showFeedback(feedback, 'warning', 'Don\'t forget the DOCTYPE declaration!');
            return;
        }
        if (!code.includes('<html') || !code.includes('</html>')) {
            showFeedback(feedback, 'warning', 'Make sure to include the <html> tags.');
            return;
        }
    }
    
    showFeedback(feedback, 'success', 'Great job! Your code looks correct.');
}

/**
 * Show feedback message
 */
function showFeedback(element, type, message) {
    if (!element) return;
    
    const colors = {
        success: 'var(--success)',
        error: 'var(--danger)',
        warning: 'var(--warning)'
    };
    
    element.innerHTML = `<div style="color: ${colors[type]}; padding: 10px; border-radius: var(--radius); background: ${colors[type]}20; margin-top: 10px;">
        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'exclamation-triangle'}-fill me-2"></i>
        ${message}
    </div>`;
}

/**
 * Reset exercise to starter code
 */
function resetExercise(exerciseId) {
    const exercise = document.querySelector(`[data-exercise-id="${exerciseId}"]`);
    if (!exercise) return;
    
    const editor = exercise.querySelector('.code-editor-textarea');
    const starterCode = editor.getAttribute('data-starter-code') || '';
    
    if (confirm('Reset to starter code? Your changes will be lost.')) {
        editor.value = starterCode;
        const previewFrame = exercise.querySelector('.code-preview-iframe');
        if (previewFrame) {
            updatePreview(editor, previewFrame);
        }
    }
}

/**
 * Submit exercise for grading
 */
async function submitExercise(exerciseId) {
    const exercise = document.querySelector(`[data-exercise-id="${exerciseId}"]`);
    if (!exercise) return;
    
    const editor = exercise.querySelector('.code-editor-textarea');
    const submitBtn = exercise.querySelector('.submit-exercise-btn');
    const feedback = exercise.querySelector('.exercise-feedback');
    const code = editor.value;
    
    // Disable button during submission
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Checking...';
    }
    
    try {
        // In a real implementation, this would submit to the server
        // For now, do basic client-side validation
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        runExercise(exerciseId);
        
    } catch (error) {
        showFeedback(feedback, 'error', 'An error occurred. Please try again.');
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check2 me-1"></i> Submit';
        }
    }
}

// Quiz timer functionality
function initQuizTimer(duration, displayElement, formElement) {
    let timeRemaining = duration * 60; // Convert to seconds
    
    const timer = setInterval(() => {
        const minutes = Math.floor(timeRemaining / 60);
        const seconds = timeRemaining % 60;
        
        displayElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeRemaining <= 300) { // 5 minutes warning
            displayElement.classList.add('text-danger');
        }
        
        if (timeRemaining <= 0) {
            clearInterval(timer);
            alert('Time is up! Your quiz will be submitted.');
            formElement.submit();
        }
        
        timeRemaining--;
    }, 1000);
    
    return timer;
}
