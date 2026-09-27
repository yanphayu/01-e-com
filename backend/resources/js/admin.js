import {
    ArcElement,
    BarController,
    BarElement,
    BubbleController,
    CategoryScale,
    Chart,
    DoughnutController,
    Filler,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
} from 'chart.js';
import './echo';

Chart.register(
    ArcElement,
    BarController,
    BarElement,
    BubbleController,
    CategoryScale,
    DoughnutController,
    Filler,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
);

const root = document.documentElement;
const themeKey = 'trinity-theme';
const themeToggles = document.querySelectorAll('[data-theme-toggle]');
const sidebar = document.querySelector('[data-admin-sidebar]');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarOverlay = document.querySelector('[data-sidebar-overlay]');
const reportBadge = document.querySelector('[data-admin-report-unread-count]');
const mobileQuery = window.matchMedia('(max-width: 992px)');
const activityCanvas = document.querySelector('[data-admin-activity-chart]');
const activityDataElement = document.getElementById('admin-activity-chart-data');
const chatCanvas = document.querySelector('[data-admin-chat-chart]');
const chatDataElement = document.getElementById('admin-chat-chart-data');
const analyticsDataElement = document.getElementById('admin-analytics-chart-data');
const userActivityCanvas = document.querySelector('[data-admin-user-activity-chart]');
const userActivityDataElement = document.getElementById('admin-user-activity-chart-data');
const toastHost = document.querySelector('[data-admin-toast-host]');

const toastDuration = 4500;
const confirmDuration = 10000;

const closeIcon = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">'
    + '<path d="M18 6 6 18M6 6l12 12" /></svg>';

function dismissToast(toast) {
    if (!toast) {
        return;
    }

    toast.classList.add('is-leaving');
    window.setTimeout(() => toast.remove(), 200);
}

function bindToast(toast, duration = toastDuration) {
    const close = toast.querySelector('[data-admin-toast-close]');

    if (close) {
        close.addEventListener('click', () => dismissToast(toast));
    }

    if (duration > 0) {
        window.setTimeout(() => dismissToast(toast), duration);
    }
}

function buildToast(message, type, actionsHtml = '') {
    const toast = document.createElement('div');
    toast.className = `admin-toast admin-toast--${type}`;
    toast.setAttribute('data-admin-toast', '');
    toast.setAttribute('role', 'status');
    toast.innerHTML = '<div class="admin-toast-body">'
        + '<p class="admin-toast-message"></p>'
        + (actionsHtml ? `<div class="admin-toast-actions">${actionsHtml}</div>` : '')
        + '</div>'
        + `<button type="button" class="admin-toast-close" data-admin-toast-close aria-label="Dismiss">${closeIcon}</button>`;
    toast.querySelector('.admin-toast-message').textContent = message;

    return toast;
}

function initializeToasts() {
    if (!toastHost) {
        return;
    }

    toastHost.querySelectorAll('[data-admin-toast]').forEach((toast) => bindToast(toast));
}

window.adminToast = function adminToast(message, type = 'info') {
    if (!toastHost || !message) {
        return;
    }

    const toast = buildToast(message, type);
    toastHost.appendChild(toast);
    bindToast(toast);
};

window.adminConfirm = function adminConfirm(message, confirmLabel = 'Delete', cancelLabel = 'Cancel') {
    if (!toastHost) {
        return Promise.resolve(window.confirm(message));
    }

    return new Promise((resolve) => {
        const actions = '<button type="button" class="admin-toast-button" data-admin-toast-cancel></button>'
            + '<button type="button" class="admin-toast-button admin-toast-button--danger" data-admin-toast-confirm></button>';
        const toast = buildToast(message, 'warning', actions);

        toast.querySelector('[data-admin-toast-cancel]').textContent = cancelLabel;
        toast.querySelector('[data-admin-toast-confirm]').textContent = confirmLabel;

        const finish = (result) => {
            dismissToast(toast);
            resolve(result);
        };

        toast.querySelector('[data-admin-toast-cancel]').addEventListener('click', () => finish(false));
        toast.querySelector('[data-admin-toast-confirm]').addEventListener('click', () => finish(true));

        toastHost.appendChild(toast);
        bindToast(toast, confirmDuration);
    });
};

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmed === '1') {
        return;
    }

    event.preventDefault();

    window.adminConfirm(form.dataset.confirm).then((confirmed) => {
        if (!confirmed) {
            return;
        }

        form.dataset.confirmed = '1';
        form.submit();
    });
});

initializeToasts();

let activityChart = null;
let chatChart = null;
let analyticsCharts = [];
let userActivityChart = null;
let dashboardChartColors = readDashboardChartColors();

function storedTheme() {
    try {
        return window.localStorage.getItem(themeKey);
    } catch (error) {
        return null;
    }
}

function preferredTheme() {
    const stored = storedTheme();

    if (stored === 'light' || stored === 'dark') {
        return stored;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function updateThemeControls(theme) {
    const nextTheme = theme === 'dark' ? 'light' : 'dark';
    const label = `Switch to ${nextTheme} mode`;

    themeToggles.forEach((toggle) => {
        toggle.setAttribute('aria-label', label);
        toggle.setAttribute('title', label);
        toggle.setAttribute('aria-pressed', String(theme === 'dark'));
        const text = toggle.querySelector('[data-theme-label]');

        if (text) {
            text.textContent = nextTheme === 'dark' ? 'Dark' : 'Light';
        }
    });
}

function setTheme(theme, persist = true) {
    const nextTheme = theme === 'dark' ? 'dark' : 'light';

    root.dataset.theme = nextTheme;
    updateThemeControls(nextTheme);
    updateDashboardCharts();
    updateAnalyticsCharts();
    updateUserActivityChart();

    if (persist) {
        try {
            window.localStorage.setItem(themeKey, nextTheme);
        } catch (error) {
            return;
        }
    }
}

setTheme(preferredTheme(), false);

themeToggles.forEach((toggle) => {
    toggle.addEventListener('click', () => {
        setTheme(root.dataset.theme === 'dark' ? 'light' : 'dark');
    });
});

function setSidebar(open) {
    if (!sidebar || !sidebarToggle) {
        return;
    }

    sidebar.classList.toggle('is-open', open);
    sidebarToggle.setAttribute('aria-expanded', String(open));
    sidebar.setAttribute('aria-hidden', String(!open && mobileQuery.matches));

    if (sidebarOverlay) {
        sidebarOverlay.classList.toggle('is-visible', open);
    }
}

function closeSidebar() {
    if (mobileQuery.matches) {
        setSidebar(false);
    }
}

if (sidebar && sidebarToggle) {
    setSidebar(false);

    sidebarToggle.addEventListener('click', () => {
        setSidebar(!sidebar.classList.contains('is-open'));
    });

    sidebarOverlay?.addEventListener('click', closeSidebar);
    sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeSidebar));
}

mobileQuery.addEventListener('change', () => {
    if (!mobileQuery.matches) {
        sidebar?.classList.remove('is-open');
        sidebar?.setAttribute('aria-hidden', 'false');
        sidebarOverlay?.classList.remove('is-visible');
    } else {
        setSidebar(false);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeSidebar();
    }
});

function setReportCount(count) {
    if (!reportBadge) {
        return;
    }

    reportBadge.textContent = count;
    reportBadge.classList.toggle('hidden', count <= 0);
    reportBadge.setAttribute('aria-label', `${count} unread reports`);
}

async function refreshReportCount() {
    if (!reportBadge) {
        return;
    }

    try {
        const response = await fetch('/admin/notifications/unread-count', {
            headers: {
                Accept: 'application/json',
            },
        });
        const data = await response.json();

        setReportCount(Number(data.count) || 0);
    } catch (error) {
        return;
    }
}

refreshReportCount();
window.setInterval(refreshReportCount, 15000);

if (window.Echo && window.AdminUser?.id) {
    window.Echo.private(`App.Models.User.${window.AdminUser.id}`)
        .notification(() => refreshReportCount())
        .error(() => {});
}

function setInfiniteScrollStatus(container, message, state = 'idle') {
    const status = container.querySelector('[data-infinite-scroll-status]');
    const label = status?.querySelector('[data-infinite-scroll-label]');
    const spinner = status?.querySelector('[data-infinite-scroll-spinner]');
    const button = status?.querySelector('[data-infinite-scroll-button]');

    if (label) {
        label.textContent = message;
    }

    status?.classList.toggle('is-error', state === 'error');

    if (spinner) {
        spinner.hidden = state !== 'loading';
    }

    if (button) {
        button.hidden = state !== 'error';

        if (state === 'error') {
            button.textContent = 'Retry';
        }
    }

    container.setAttribute('aria-busy', String(state === 'loading'));
}

function initializeInfiniteScroll() {
    const supportsObserver = 'IntersectionObserver' in window;

    document.querySelectorAll('[data-admin-infinite-scroll]').forEach((container) => {
        const targetId = container.dataset.infiniteScrollTarget;
        const target = targetId ? document.getElementById(targetId) : null;
        const button = container.querySelector('[data-infinite-scroll-button]');
        let loading = false;

        if (!target) {
            return;
        }

        const loadNextPage = async () => {
            const nextUrl = container.dataset.infiniteScrollNext;

            if (loading || !nextUrl) {
                return;
            }

            loading = true;
            setInfiniteScrollStatus(container, 'Loading more records…', 'loading');

            try {
                const response = await fetch(nextUrl, {
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'text/html',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Unable to load the next page.');
                }

                const html = await response.text();
                const parsed = new DOMParser().parseFromString(html, 'text/html');
                const nextTarget = parsed.getElementById(target.id);
                const nextContainer = parsed.querySelector('[data-admin-infinite-scroll]');

                if (!nextTarget || !nextContainer) {
                    throw new Error('The next page did not contain a table.');
                }

                target.querySelector('.admin-table-empty')?.remove();

                Array.from(nextTarget.children)
                    .filter((row) => !row.classList.contains('admin-table-empty'))
                    .forEach((row) => target.appendChild(document.importNode(row, true)));

                container.dataset.infiniteScrollNext = nextContainer.dataset.infiniteScrollNext || '';
                setInfiniteScrollStatus(
                    container,
                    container.dataset.infiniteScrollNext ? 'Scroll to load more' : 'All records loaded',
                );
            } catch (error) {
                setInfiniteScrollStatus(container, 'Unable to load more records.', 'error');
            } finally {
                loading = false;
            }
        };

        button?.addEventListener('click', loadNextPage);

        if (supportsObserver && container.dataset.infiniteScrollNext) {
            const observer = new IntersectionObserver((entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    loadNextPage();
                }
            }, { rootMargin: '0px 0px 320px' });

            observer.observe(container);
        } else if (container.dataset.infiniteScrollNext) {
            if (button) {
                button.hidden = false;
            }
        }
    });
}

initializeInfiniteScroll();

function readDashboardChartColors() {
    const styles = window.getComputedStyle(root);

    const color = (name, fallback) => styles.getPropertyValue(name).trim() || fallback;

    return {
        primary: color('--primary', '#2563EB'),
        primaryStrong: color('--primary-strong', '#1D4ED8'),
        secondary: color('--chart-secondary', '#10B981'),
        warning: color('--warning', '#D97706'),
        danger: color('--danger', '#DC2626'),
        grid: color('--chart-grid', '#E2E8F0'),
        border: color('--border', '#E2E8F0'),
        surfaceRaised: color('--surface-raised', '#FFFFFF'),
        text: color('--text', '#0F172A'),
        textMuted: color('--text-muted', '#475569'),
        textSubtle: color('--text-subtle', '#94A3B8'),
    };
}

function withAlpha(hex, alpha) {
    const value = hex.replace('#', '');
    const normalized = value.length === 3
        ? value.split('').map((character) => character.repeat(2)).join('')
        : value;

    if (!/^[\da-f]{6}$/i.test(normalized)) {
        return hex;
    }

    const number = Number.parseInt(normalized, 16);

    return `rgba(${number >> 16}, ${(number >> 8) & 255}, ${number & 255}, ${alpha})`;
}

function activityArea(context) {
    const { chart } = context;
    const { chartArea, ctx } = chart;

    if (!chartArea) {
        return withAlpha(dashboardChartColors.primary, 0.12);
    }

    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
    gradient.addColorStop(0, withAlpha(dashboardChartColors.primary, 0.24));
    gradient.addColorStop(1, withAlpha(dashboardChartColors.primary, 0.02));

    return gradient;
}

function chartTooltipOptions(colors) {
    return {
        backgroundColor: colors.surfaceRaised,
        titleColor: colors.text,
        bodyColor: colors.textMuted,
        borderColor: colors.border,
        borderWidth: 1,
        padding: 12,
        cornerRadius: 10,
        boxPadding: 4,
        usePointStyle: true,
    };
}

function chartScaleOptions(colors, compact = false) {
    return {
        x: {
            grid: { display: false },
            border: { color: colors.border },
            ticks: {
                color: colors.textSubtle,
                font: { family: "'JetBrains Mono NF', monospace", size: 10 },
                maxRotation: 0,
                maxTicksLimit: compact ? 7 : 6,
                autoSkip: true,
            },
        },
        y: {
            beginAtZero: true,
            grid: {
                color: colors.grid,
                drawTicks: false,
            },
            border: { display: false },
            ticks: {
                color: colors.textSubtle,
                font: { family: "'JetBrains Mono NF', monospace", size: 10 },
                maxTicksLimit: compact ? 4 : 5,
                precision: 0,
            },
        },
    };
}

function chartLegendOptions(colors) {
    return {
        display: true,
        position: 'bottom',
        labels: {
            color: colors.textMuted,
            usePointStyle: true,
            pointStyle: 'circle',
            padding: 16,
            font: { family: "'JetBrains Mono NF', monospace", size: 10 },
        },
    };
}

function horizontalBarScaleOptions(colors) {
    return {
        x: {
            beginAtZero: true,
            grid: {
                color: colors.grid,
                drawTicks: false,
            },
            border: { display: false },
            ticks: {
                color: colors.textSubtle,
                precision: 0,
                font: { family: "'JetBrains Mono NF', monospace", size: 10 },
            },
        },
        y: {
            grid: { display: false },
            border: { color: colors.border },
            ticks: {
                color: colors.textMuted,
                font: { family: "'JetBrains Mono NF', monospace", size: 10 },
                callback(value) {
                    const label = this.getLabelForValue(value);
                    return label.length > 28 ? `${label.slice(0, 27)}…` : label;
                },
            },
        },
    };
}

function activityDataset(label, values, color, fill = false) {
    return {
        label,
        data: values,
        borderColor: color,
        backgroundColor: fill ? activityArea : 'transparent',
        borderCapStyle: 'round',
        borderJoinStyle: 'round',
        borderWidth: 2,
        fill,
        tension: 0.35,
        pointRadius: 0,
        pointHoverRadius: 4,
        pointBackgroundColor: color,
        pointBorderColor: dashboardChartColors.surfaceRaised,
        pointBorderWidth: 2,
        pointHitRadius: 12,
    };
}

function readChartData(element, valueKeys) {
    if (!(element instanceof HTMLScriptElement)) {
        return null;
    }

    let data;

    try {
        data = JSON.parse(element.textContent || '{}');
    } catch (error) {
        return null;
    }

    if (!data || !Array.isArray(data.labels) || valueKeys.some((key) => !Array.isArray(data[key]))) {
        return null;
    }

    return data;
}

function readAnalyticsData() {
    if (!(analyticsDataElement instanceof HTMLScriptElement)) {
        return null;
    }

    let data;

    try {
        data = JSON.parse(analyticsDataElement.textContent || '{}');
    } catch (error) {
        return null;
    }

    const sections = [
        'activity',
        'cumulative',
        'listingStatuses',
        'reportStatuses',
        'categories',
        'conversations',
    ];

    if (sections.some((section) => !data?.[section] || !Array.isArray(data[section].labels))) {
        return null;
    }

    return data;
}

function createActivityChart() {
    if (!(activityCanvas instanceof HTMLCanvasElement)) {
        return;
    }

    const data = readChartData(activityDataElement, ['users', 'products', 'comments', 'messages']);

    if (!data) {
        return;
    }

    activityChart = new Chart(activityCanvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                activityDataset('New users', data.users, dashboardChartColors.secondary),
                activityDataset('New listings', data.products, dashboardChartColors.primary, true),
                activityDataset('Comments', data.comments, dashboardChartColors.warning),
                activityDataset('Messages', data.messages, dashboardChartColors.danger),
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            normalized: true,
            animation: { duration: 450 },
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: { display: false },
                tooltip: chartTooltipOptions(dashboardChartColors),
            },
            scales: chartScaleOptions(dashboardChartColors),
        },
    });
}

function createChatChart() {
    if (!(chatCanvas instanceof HTMLCanvasElement)) {
        return;
    }

    const data = readChartData(chatDataElement, ['values']);

    if (!data) {
        return;
    }

    chatChart = new Chart(chatCanvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Messages',
                    data: data.values,
                    backgroundColor: (context) => withAlpha(
                        dashboardChartColors.primary,
                        context.raw > 0 ? 0.78 : 0.18,
                    ),
                    borderColor: dashboardChartColors.primary,
                    borderWidth: 1,
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 28,
                    hoverBackgroundColor: dashboardChartColors.primaryStrong,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 450 },
            plugins: {
                legend: { display: false },
                tooltip: chartTooltipOptions(dashboardChartColors),
            },
            scales: chartScaleOptions(dashboardChartColors, true),
        },
    });
}

function createAnalyticsLineChart(data) {
    const canvas = document.querySelector('[data-admin-cumulative-chart]');

    if (!(canvas instanceof HTMLCanvasElement)) {
        return;
    }

    const chart = new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                activityDataset('Cumulative users', data.users, dashboardChartColors.primary, true),
                activityDataset('Cumulative listings', data.products, dashboardChartColors.secondary),
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            normalized: true,
            animation: { duration: 450 },
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: chartLegendOptions(dashboardChartColors),
                tooltip: chartTooltipOptions(dashboardChartColors),
            },
            scales: chartScaleOptions(dashboardChartColors),
        },
    });

    analyticsCharts.push(chart);
}

function createAnalyticsDoughnutChart(selector, data, colors) {
    const canvas = document.querySelector(selector);

    if (!(canvas instanceof HTMLCanvasElement)) {
        return;
    }

    const chart = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [
                {
                    data: data.values,
                    backgroundColor: colors,
                    borderColor: dashboardChartColors.surfaceRaised,
                    borderWidth: 3,
                    hoverBorderColor: dashboardChartColors.surfaceRaised,
                    hoverOffset: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 450 },
            cutout: '68%',
            plugins: {
                legend: chartLegendOptions(dashboardChartColors),
                tooltip: chartTooltipOptions(dashboardChartColors),
            },
        },
    });

    analyticsCharts.push(chart);
}

function createAnalyticsHorizontalBarChart(selector, data, color) {
    const canvas = document.querySelector(selector);

    if (!(canvas instanceof HTMLCanvasElement)) {
        return;
    }

    const chart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    data: data.values,
                    backgroundColor: color,
                    borderColor: color,
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 24,
                },
            ],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            normalized: true,
            animation: { duration: 450 },
            plugins: {
                legend: { display: false },
                tooltip: chartTooltipOptions(dashboardChartColors),
            },
            scales: horizontalBarScaleOptions(dashboardChartColors),
        },
    });

    analyticsCharts.push(chart);
}

function createAnalyticsCharts() {
    const data = readAnalyticsData();

    if (!data) {
        return;
    }

    createAnalyticsLineChart(data.cumulative);
    createAnalyticsDoughnutChart(
        '[data-admin-listing-status-chart]',
        data.listingStatuses,
        [dashboardChartColors.secondary, dashboardChartColors.warning, dashboardChartColors.danger],
    );
    createAnalyticsDoughnutChart(
        '[data-admin-report-status-chart]',
        data.reportStatuses,
        [dashboardChartColors.warning, dashboardChartColors.primary, dashboardChartColors.danger],
    );
    createAnalyticsHorizontalBarChart(
        '[data-admin-category-chart]',
        data.categories,
        dashboardChartColors.primary,
    );
    createAnalyticsHorizontalBarChart(
        '[data-admin-conversation-chart]',
        data.conversations,
        dashboardChartColors.secondary,
    );
}

function updateAnalyticsCharts() {
    analyticsCharts.forEach((chart) => {
        const dataset = chart.data.datasets[0];

        if (chart.config.type === 'doughnut') {
            const colors = chart.canvas.hasAttribute('data-admin-listing-status-chart')
                ? [dashboardChartColors.secondary, dashboardChartColors.warning, dashboardChartColors.danger]
                : [dashboardChartColors.warning, dashboardChartColors.primary, dashboardChartColors.danger];
            dataset.backgroundColor = colors;
            dataset.borderColor = dashboardChartColors.surfaceRaised;
            dataset.hoverBorderColor = dashboardChartColors.surfaceRaised;
            chart.options.plugins.legend = chartLegendOptions(dashboardChartColors);
        } else if (chart.options.indexAxis === 'y') {
            const color = chart.canvas.hasAttribute('data-admin-category-chart')
                ? dashboardChartColors.primary
                : dashboardChartColors.secondary;
            dataset.backgroundColor = color;
            dataset.borderColor = color;
            chart.options.scales = horizontalBarScaleOptions(dashboardChartColors);
        } else {
            const colors = [dashboardChartColors.primary, dashboardChartColors.secondary];
            chart.data.datasets.forEach((lineDataset, index) => {
                lineDataset.borderColor = colors[index];
                lineDataset.pointBackgroundColor = colors[index];
                lineDataset.pointBorderColor = dashboardChartColors.surfaceRaised;
            });
            chart.data.datasets[0].backgroundColor = activityArea;
            chart.options.plugins.legend = chartLegendOptions(dashboardChartColors);
            chart.options.scales = chartScaleOptions(dashboardChartColors);
        }

        chart.options.plugins.tooltip = chartTooltipOptions(dashboardChartColors);
        chart.update('none');
    });
}

function readUserActivityData() {
    return readChartData(userActivityDataElement, ['values']);
}

function bubbleRadius(value, max) {
    if (max <= 0) {
        return 10;
    }

    return 12 + Math.round((value / max) * 26);
}

function userActivityBubbleColors() {
    return [
        withAlpha(dashboardChartColors.primary, 0.55),
        withAlpha(dashboardChartColors.secondary, 0.55),
        withAlpha(dashboardChartColors.warning, 0.55),
    ];
}

function createUserActivityChart() {
    if (!(userActivityCanvas instanceof HTMLCanvasElement)) {
        return;
    }

    const data = readUserActivityData();

    if (!data) {
        return;
    }

    const colors = dashboardChartColors;
    const max = Math.max(...data.values, 0);
    const bubbles = data.values.map((value, index) => ({
        x: index,
        y: value,
        r: bubbleRadius(value, max),
    }));
    const bubbleColors = userActivityBubbleColors();

    userActivityChart = new Chart(userActivityCanvas, {
        type: 'bubble',
        data: {
            datasets: data.labels.map((label, index) => ({
                label,
                data: [bubbles[index]],
                backgroundColor: withAlpha(bubbleColors[index], 0.75),
                borderColor: colors.surfaceRaised,
                borderWidth: 2,
                hoverBorderWidth: 3,
            })),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: 8,
            },
            plugins: {
                legend: chartLegendOptions(colors),
                tooltip: {
                    ...chartTooltipOptions(colors),
                    callbacks: {
                        label(context) {
                            const label = context.dataset.label || '';
                            const value = context.raw?.y ?? 0;

                            return `${label}: ${value}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { color: colors.border },
                    min: -0.6,
                    max: data.labels.length - 0.4,
                    ticks: {
                        stepSize: 1,
                        color: colors.textSubtle,
                        font: { family: "'JetBrains Mono NF', monospace", size: 10 },
                        callback(value) {
                            return data.labels[value] ?? '';
                        },
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: colors.grid,
                        drawTicks: false,
                    },
                    border: { display: false },
                    ticks: {
                        color: colors.textSubtle,
                        precision: 0,
                        maxTicksLimit: 5,
                        font: { family: "'JetBrains Mono NF', monospace", size: 10 },
                    },
                },
            },
        },
    });
}

function updateUserActivityChart() {
    if (!userActivityChart) {
        return;
    }

    const colors = dashboardChartColors;
    const bubbleColors = userActivityBubbleColors();

    userActivityChart.data.datasets.forEach((dataset, index) => {
        dataset.backgroundColor = withAlpha(bubbleColors[index], 0.75);
        dataset.borderColor = colors.surfaceRaised;
    });

    userActivityChart.options.plugins.legend = chartLegendOptions(colors);
    userActivityChart.options.plugins.tooltip = chartTooltipOptions(colors);
    userActivityChart.options.scales.x.border.color = colors.border;
    userActivityChart.options.scales.x.ticks.color = colors.textSubtle;
    userActivityChart.options.scales.y.grid.color = colors.grid;
    userActivityChart.options.scales.y.ticks.color = colors.textSubtle;
    userActivityChart.update('none');
}

function updateDashboardCharts() {
    dashboardChartColors = readDashboardChartColors();

    if (activityChart) {
        const colors = [
            dashboardChartColors.secondary,
            dashboardChartColors.primary,
            dashboardChartColors.warning,
            dashboardChartColors.danger,
        ];

        activityChart.data.datasets.forEach((dataset, index) => {
            dataset.borderColor = colors[index];
            dataset.pointBackgroundColor = colors[index];
            dataset.pointBorderColor = dashboardChartColors.surfaceRaised;
        });
        activityChart.data.datasets[1].backgroundColor = activityArea;
        activityChart.options.plugins.tooltip = chartTooltipOptions(dashboardChartColors);
        activityChart.options.scales = chartScaleOptions(dashboardChartColors);
        activityChart.update('none');
    }

    if (chatChart) {
        const dataset = chatChart.data.datasets[0];
        dataset.backgroundColor = (context) => withAlpha(
            dashboardChartColors.primary,
            context.raw > 0 ? 0.78 : 0.18,
        );
        dataset.borderColor = dashboardChartColors.primary;
        dataset.hoverBackgroundColor = dashboardChartColors.primaryStrong;
        chatChart.options.plugins.tooltip = chartTooltipOptions(dashboardChartColors);
        chatChart.options.scales = chartScaleOptions(dashboardChartColors, true);
        chatChart.update('none');
    }
}

function initializeDashboardCharts() {
    createActivityChart();
    createChatChart();
    createAnalyticsCharts();
    createUserActivityChart();
}

initializeDashboardCharts();
