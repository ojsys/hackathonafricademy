/**
 * HackathonAfrica LMS - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Theme toggle
    initThemeToggle();

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

function initThemeToggle() {
    const html = document.documentElement;
    const btn = document.getElementById('theme-toggle');
    if (!btn) return;

    const saved = localStorage.getItem('theme') || 'dark';
    applyTheme(saved);

    btn.addEventListener('click', () => {
        const next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        localStorage.setItem('theme', next);
        applyTheme(next);
    });

    function applyTheme(theme) {
        html.setAttribute('data-bs-theme', theme);
        const lightIcon = btn.querySelector('.theme-icon-light');
        const darkIcon  = btn.querySelector('.theme-icon-dark');
        if (theme === 'dark') {
            lightIcon.classList.remove('d-none');
            darkIcon.classList.add('d-none');
        } else {
            lightIcon.classList.add('d-none');
            darkIcon.classList.remove('d-none');
        }
    }
}

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
            const hintContent = this.closest('.code-exercise')?.querySelector('.hint-content');
            if (hintContent) {
                hintContent.classList.toggle('show');
                this.innerHTML = hintContent.classList.contains('show')
                    ? '<i class="bi bi-lightbulb-fill me-1"></i> Hide Hint'
                    : '<i class="bi bi-lightbulb me-1"></i> Show Hint';
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
