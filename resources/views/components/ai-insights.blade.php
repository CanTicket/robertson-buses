<div class="ai-insights-panel" id="aiInsightsPanel">
    <div class="ai-header">
        <div class="ai-icon">
            <i class="bi bi-robot"></i>
        </div>
        <div>
            <h5 class="mb-0">AI Insights</h5>
            <small class="text-muted">Powered by AI Analytics</small>
        </div>
        <button class="btn-close" onclick="toggleAIInsights()"></button>
    </div>
    
    <div class="ai-content" id="aiContent">
        <div class="ai-loading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading insights...</span>
            </div>
            <p class="mt-2 text-muted">Analyzing data with AI...</p>
        </div>
    </div>
</div>

<style>
.ai-insights-panel {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 380px;
    max-height: 600px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    color: white;
    z-index: 1050;
    transform: translateY(calc(100% - 60px));
    transition: transform 0.3s ease;
    overflow: hidden;
}

.ai-insights-panel.open {
    transform: translateY(0);
}

.ai-header {
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
}

.ai-icon {
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.ai-content {
    padding: 1.5rem;
    max-height: 520px;
    overflow-y: auto;
}

.ai-loading {
    text-align: center;
    padding: 2rem;
}

.ai-insight-card {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1rem;
    border-left: 4px solid;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.ai-insight-card.success { border-left-color: #10b981; }
.ai-insight-card.warning { border-left-color: #f59e0b; }
.ai-insight-card.critical { border-left-color: #ef4444; }
.ai-insight-card.info { border-left-color: #3b82f6; }

.ai-insight-card h6 {
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ai-insight-item {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 0.75rem;
    margin-top: 0.5rem;
    font-size: 0.9rem;
}

.ai-toggle-btn {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 50%;
    color: white;
    font-size: 1.5rem;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
    z-index: 1051;
    cursor: pointer;
    transition: transform 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-toggle-btn:hover {
    transform: scale(1.1);
}

.ai-toggle-btn.hidden {
    display: none;
}

.ai-content::-webkit-scrollbar {
    width: 6px;
}

.ai-content::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}

.ai-content::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 10px;
}

.ai-content::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}
</style>

<script>
let aiInsightsOpen = false;

function toggleAIInsights() {
    const panel = document.getElementById('aiInsightsPanel');
    const btn = document.getElementById('aiToggleBtn');
    aiInsightsOpen = !aiInsightsOpen;
    
    if (aiInsightsOpen) {
        panel.classList.add('open');
        if (btn) btn.classList.add('hidden');
        loadAIInsights();
    } else {
        panel.classList.remove('open');
        if (btn) btn.classList.remove('hidden');
    }
}

function loadAIInsights() {
    const content = document.getElementById('aiContent');
    content.innerHTML = `
        <div class="ai-loading">
            <div class="spinner-border text-white" role="status">
                <span class="visually-hidden">Loading insights...</span>
            </div>
            <p class="mt-2">Analyzing data with AI...</p>
        </div>
    `;
    
    fetch('{{ route("ai.insights") }}')
        .then(response => response.json())
        .then(data => {
            renderInsights(data);
        })
        .catch(error => {
            content.innerHTML = `
                <div class="ai-insight-card">
                    <p class="mb-0">Unable to load insights at this time.</p>
                </div>
            `;
        });
}

function renderInsights(insights) {
    const content = document.getElementById('aiContent');
    let html = '';
    
    // Maintenance Insights
    if (insights.maintenance?.items?.length > 0) {
        html += `
            <div class="ai-insight-card warning">
                <h6><i class="bi ${insights.maintenance.icon}"></i> ${insights.maintenance.title}</h6>
                ${insights.maintenance.items.map(item => `
                    <div class="ai-insight-item">
                        <strong>${item.vehicle}</strong>: ${item.flags} flags - ${item.recommendation}
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    // Pattern Insights
    if (insights.patterns) {
        html += `
            <div class="ai-insight-card ${insights.patterns.type}">
                <h6><i class="bi ${insights.patterns.icon}"></i> ${insights.patterns.title}</h6>
                <p class="mb-0">${insights.patterns.message}</p>
            </div>
        `;
    }
    
    // Safety Insights
    if (insights.safety) {
        html += `
            <div class="ai-insight-card ${insights.safety.type}">
                <h6><i class="bi ${insights.safety.icon}"></i> ${insights.safety.title}</h6>
                <p class="mb-0">${insights.safety.message}</p>
            </div>
        `;
    }
    
    // Recommendations
    if (insights.recommendations?.items?.length > 0) {
        html += `
            <div class="ai-insight-card info">
                <h6><i class="bi ${insights.recommendations.icon}"></i> ${insights.recommendations.title}</h6>
                ${insights.recommendations.items.map(item => `
                    <div class="ai-insight-item">
                        <strong>${item.type === 'urgent' ? '⚠️ ' : ''}</strong>${item.message}
                        ${item.action ? `<br><small>→ ${item.action}</small>` : ''}
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    if (!html) {
        html = `
            <div class="ai-insight-card success">
                <h6><i class="bi bi-check-circle"></i> All Good!</h6>
                <p class="mb-0">Everything looks great. No issues detected.</p>
            </div>
        `;
    }
    
    content.innerHTML = html;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('aiToggleBtn')) {
        const btn = document.createElement('button');
        btn.id = 'aiToggleBtn';
        btn.className = 'ai-toggle-btn';
        btn.innerHTML = '<i class="bi bi-robot"></i>';
        btn.onclick = toggleAIInsights;
        btn.title = 'AI Insights';
        document.body.appendChild(btn);
    }
});
</script>

