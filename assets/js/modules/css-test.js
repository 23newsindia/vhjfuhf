/**
 * Handles the unused CSS test functionality
 */
class CSSTest {
    constructor() {
        this.button = document.getElementById('test-unused-css');
        this.urlInput = document.getElementById('test-url');
        this.results = document.getElementById('test-results');
        this.status = document.querySelector('.test-status');
        this.resultsBody = document.querySelector('.results-body');
        
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        if (this.button) {
            this.button.addEventListener('click', () => this.runTest());
        }
    }

    async runTest() {
        try {
            this.setLoading(true);
            this.clearResults();
            
            const url = this.urlInput.value || window.location.origin;
            const response = await this.sendTestRequest(url);
            
            if (response.success) {
                this.displayResults(response.data);
            } else {
                this.showError(response.data);
            }
        } catch (error) {
            this.showError('Failed to test unused CSS removal. Please try again.');
        } finally {
            this.setLoading(false);
        }
    }

    async sendTestRequest(url) {
        const formData = new FormData();
        formData.append('action', 'macp_test_unused_css');
        formData.append('url', url);
        formData.append('nonce', macpAdmin.nonce);

        const response = await fetch(ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        });

        return await response.json();
    }

    displayResults(data) {
        this.results.style.display = 'block';
        this.status.className = 'test-status success';
        this.status.innerHTML = `Successfully analyzed CSS for <strong>${data.url}</strong>`;

        data.results.forEach(result => {
            const reduction = ((result.originalSize - result.optimizedSize) / result.originalSize * 100).toFixed(1);
            this.resultsBody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${result.file}</td>
                    <td>${this.formatBytes(result.originalSize)}</td>
                    <td>${this.formatBytes(result.optimizedSize)}</td>
                    <td>${reduction}%</td>
                    <td class="file-status ${result.success ? 'success' : 'error'}">
                        ${result.success ? '✓ Optimized' : '✗ ' + (result.error || 'Failed')}
                    </td>
                </tr>
            `);
        });
    }

    showError(message) {
        this.results.style.display = 'block';
        this.status.className = 'test-status error';
        this.status.textContent = `Error: ${message}`;
    }

    setLoading(isLoading) {
        this.button.disabled = isLoading;
        this.button.textContent = isLoading ? 'Testing...' : 'Test Unused CSS Removal';
    }

    clearResults() {
        this.status.className = 'test-status';
        this.status.textContent = '';
        this.resultsBody.innerHTML = '';
    }

    formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => new CSSTest());