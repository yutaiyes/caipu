// AJAX页面加载器
class AjaxLoader {
    constructor() {
        this.init();
    }
    
    init() {
        // 拦截所有链接点击
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && this.shouldIntercept(link)) {
                e.preventDefault();
                this.loadPage(link.href);
            }
        });
        
        // 处理浏览器前进后退
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.url) {
                this.loadPage(e.state.url, false);
            }
        });
    }
    
    shouldIntercept(link) {
        const href = link.href;
        const hostname = window.location.hostname;
        
        // 只拦截同域名的链接
        if (!href || href.indexOf(hostname) === -1) return false;
        
        // 排除特殊链接
        if (href.includes('#') || 
            href.includes('logout') || 
            href.includes('download') ||
            link.target === '_blank') return false;
        
        // 排除文件下载链接
        if (href.match(/\.(zip|pdf|jpg|png|gif)$/i)) return false;
        
        return true;
    }
    
    async loadPage(url, addToHistory = true) {
        // 显示加载指示器
        this.showLoader();
        
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const html = await response.text();
            
            // 使用requestAnimationFrame减少闪烁
            requestAnimationFrame(() => {
                if (addToHistory) {
                    history.pushState({url: url}, '', url);
                }
                
                this.updateContent(html);
                
                setTimeout(() => this.hideLoader(), 100);
            });
            
        } catch (error) {
            console.error('加载失败:', error);
            this.hideLoader();
            // 降级到普通导航
            window.location.href = url;
        }
    }
    
    updateContent(html) {
        // 创建临时DOM
        const temp = document.createElement('div');
        temp.innerHTML = html;
        
        // 更新主要内容
        const mainContent = temp.querySelector('.main-content, .content-wrapper, main');
        const currentMain = document.querySelector('.main-content, .content-wrapper, main');
        
        if (mainContent && currentMain) {
            currentMain.innerHTML = mainContent.innerHTML;
            this.executeScripts(currentMain);
        }
        
        // 更新标题
        const title = temp.querySelector('title');
        if (title) {
            document.title = title.textContent;
        }
        
        // 重新初始化组件
        this.reinitializeComponents();
    }
    
    reinitializeComponents() {
        // 重新初始化Bootstrap组件
        if (window.bootstrap) {
            // 重新初始化工具提示
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
        
        // 重新绑定事件
        if (typeof reinitEvents === 'function') {
            reinitEvents();
        }
    }

    executeScripts(container) {
        const scripts = Array.from(container.querySelectorAll('script'));
        scripts.forEach((script) => {
            const newScript = document.createElement('script');
            const attrs = Array.from(script.attributes);
            attrs.forEach((attr) => {
                if (attr.name !== 'src') {
                    newScript.setAttribute(attr.name, attr.value);
                }
            });
            if (script.src) {
                if (document.querySelector(`script[src="${script.src}"]`)) {
                    script.remove();
                    return;
                }
                newScript.async = false;
                newScript.src = script.src;
            } else {
                newScript.async = false;
                newScript.textContent = script.textContent;
            }
            script.parentNode.replaceChild(newScript, script);
        });
    }
    
    showLoader() {
        // 避免重复创建
        if (document.getElementById('ajax-loader')) return;
        
        const loader = document.createElement('div');
        loader.id = 'ajax-loader';
        loader.innerHTML = `
            <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; 
                        background: rgba(255,255,255,0.8); z-index: 9999; 
                        display: flex; align-items: center; justify-content: center;">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">加载中...</span>
                    </div>
                    <div class="text-muted">正在加载...</div>
                </div>
            </div>
        `;
        document.body.appendChild(loader);
    }
    
    hideLoader() {
        const loader = document.getElementById('ajax-loader');
        if (loader) {
            loader.remove();
        }
    }
}

// 初始化AJAX加载器
document.addEventListener('DOMContentLoaded', () => {
    new AjaxLoader();
});
