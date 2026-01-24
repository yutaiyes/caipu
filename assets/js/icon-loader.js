// 图标字体预加载和优化脚本
(function() {
    'use strict';
    
    // 图标字体配置
    const iconConfig = {
        fontUrl: 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        criticalIcons: ['fa-home', 'fa-utensils', 'fa-search', 'fa-eye', 'fa-edit', 'fa-trash'],
        deferredIcons: ['fa-cog', 'fa-globe', 'fa-chart-line', 'fa-tag', 'fa-plus'],
        fallbackClass: 'icon-fallback'
    };
    
    // 创建预加载链接
    function preloadFont(url) {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'style';
        link.href = url;
        link.onload = function() {
            this.rel = 'stylesheet';
        };
        document.head.appendChild(link);
    }
    
    // 添加后备样式
    function addFallbackStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .${iconConfig.fallbackClass}::before {
                font-family: system-ui, -apple-system, sans-serif !important;
                speak: none;
            }
            .fa-home.${iconConfig.fallbackClass}::before { content: "🏠"; }
            .fa-utensils.${iconConfig.fallbackClass}::before { content: "🍽️"; }
            .fa-search.${iconConfig.fallbackClass}::before { content: "🔍"; }
            .fa-eye.${iconConfig.fallbackClass}::before { content: "👁️"; }
            .fa-edit.${iconConfig.fallbackClass}::before { content: "✏️"; }
            .fa-trash.${iconConfig.fallbackClass}::before { content: "🗑️"; }
            .fa-cog.${iconConfig.fallbackClass}::before { content: "⚙️"; }
            .fa-globe.${iconConfig.fallbackClass}::before { content: "🌐"; }
            .fa-chart-line.${iconConfig.fallbackClass}::before { content: "📈"; }
            .fa-tag.${iconConfig.fallbackClass}::before { content: "🏷️"; }
            .fa-plus.${iconConfig.fallbackClass}::before { content: "➕"; }
            .fa-arrow-left.${iconConfig.fallbackClass}::before { content: "⬅️"; }
            .fa-arrow-right.${iconConfig.fallbackClass}::before { content: "➡️"; }
            .fa-print.${iconConfig.fallbackClass}::before { content: "🖨️"; }
            .fa-save.${iconConfig.fallbackClass}::before { content: "💾"; }
            .fa-times.${iconConfig.fallbackClass}::before { content: "❌"; }
            .fa-check.${iconConfig.fallbackClass}::before { content: "✅"; }
        `;
        document.head.appendChild(style);
    }
    
    // 标记需要后备的图标
    function markFallbackIcons() {
        const icons = document.querySelectorAll('.fas, .fa-solid, .fa-regular, .fa-light, .fa-duotone');
        icons.forEach(icon => {
            // 添加后备类
            icon.classList.add(iconConfig.fallbackClass);
            
            // 添加加载状态
            icon.classList.add('icon-loading');
            
            // 检测图标类型
            const iconClass = Array.from(icon.classList).find(c => c.startsWith('fa-') && c !== 'fas' && c !== 'fa-solid');
            
            // 关键图标立即加载
            if (iconConfig.criticalIcons.includes(iconClass)) {
                icon.classList.add('fa-priority');
            } else {
                icon.classList.add('fa-deferred');
            }
        });
    }
    
    // 字体加载完成后的处理
    function onFontLoaded() {
        const icons = document.querySelectorAll('.icon-loading');
        icons.forEach(icon => {
            icon.classList.remove('icon-loading', iconConfig.fallbackClass);
            icon.classList.add('icon-loaded');
        });
    }
    
    // 监听字体加载
    function monitorFontLoading() {
        if ('fonts' in document) {
            document.fonts.ready.then(onFontLoaded);
        } else {
            // 降级处理：延迟一段时间后移除后备
            setTimeout(onFontLoaded, 1000);
        }
    }
    
    // 初始化
    function init() {
        // 立即添加后备样式
        addFallbackStyles();
        
        // 标记图标
        markFallbackIcons();
        
        // 预加载字体
        preloadFont(iconConfig.fontUrl);
        
        // 监听加载
        monitorFontLoading();
    }
    
    // DOM加载完成后执行
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // 导出配置供其他脚本使用
    window.IconLoader = {
        config: iconConfig,
        preloadFont: preloadFont,
        onFontLoaded: onFontLoaded
    };
})();
