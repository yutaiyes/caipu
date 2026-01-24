</div>
</div>
</div>
</div>
<!-- 返回顶部按钮 -->
<button id="backToTop" class="btn btn-primary" style="display: none; position: fixed; bottom: 30px; right: 30px; z-index: 1000; border-radius: 50%; width: 50px; height: 50px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);" title="返回顶部">
<i class="fas fa-arrow-up"></i>
</button>
<style>
#messageContainer {
position: fixed;
top: 20px;
right: 20px;
z-index: 9999;
max-width: 400px;
}
#messageContainer .alert {
animation: slideIn 0.3s ease-out;
margin-bottom: 10px;
box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
@keyframes slideIn {
from {
transform: translateX(100%);
opacity: 0;
}
to {
transform: translateX(0);
opacity: 1;
}
}
@keyframes slideOut {
from {
transform: translateX(0);
opacity: 1;
}
to {
transform: translateX(100%);
opacity: 0;
}
}
</style>
<script>
// 消息提示函数
function showMessage(message, type = 'success') {
const container = document.getElementById('messageContainer');
const alert = document.createElement('div');
alert.className = `alert alert-${type} alert-dismissible fade show`;
alert.setAttribute('role', 'alert');
alert.innerHTML = `
<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'times-circle' : 'info-circle'}"></i>
${message}
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
`;
container.appendChild(alert);

// 2秒后自动关闭
setTimeout(() => {
alert.style.animation = 'slideOut 0.3s ease-out forwards';
setTimeout(() => {
alert.remove();
}, 300);
}, 2000);
}
</script>
<script>
// 返回顶部功能
(function() {
const backToTopBtn = document.getElementById('backToTop');
// 监听滚动事件
window.addEventListener('scroll', function() {
if (window.pageYOffset > 300) {
backToTopBtn.style.display = 'block';
} else {
backToTopBtn.style.display = 'none';
}
});
// 点击返回顶部
backToTopBtn.addEventListener('click', function() {
window.scrollTo({
top: 0,
behavior: 'smooth'
});
});
})();
</script>
<script src="https://cdn.staticfile.org/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<!-- AJAX页面加载器 -->
<script src="../assets/js/ajax-loader.js"></script>
<script>
// 重新初始化后台特定的事件
function reinitEvents() {
    // 更新导航active状态
    updateNavigationActive();
    
    // 重新绑定表单提交
    const forms = document.querySelectorAll('form[data-ajax]');
    forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            
            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    body: formData,
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                });
                
                const result = await response.json();
                if (result.success) {
                    showMessage(result.message, 'success');
                    if (result.redirect) {
                        setTimeout(() => window.location.href = result.redirect, 1500);
                    }
                } else {
                    showMessage(result.message, 'danger');
                }
            } catch (error) {
                showMessage('操作失败', 'danger');
            }
        });
    });
}

// 更新导航active状态
function updateNavigationActive() {
    const currentUrl = new URL(window.location.href);
    let currentFile = currentUrl.pathname.split('/').pop();
    
    if (!currentFile || !currentFile.endsWith('.php')) {
        currentFile = 'index.php';
    }
    
    const aliasMap = {
        'recipe_edit.php': 'recipe_list.php',
        'page_add.php': 'page_list.php',
        'page_edit.php': 'page_list.php'
    };
    
    const targetFile = aliasMap[currentFile] || currentFile;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href) {
            link.classList.toggle('active', href === targetFile);
        }
    });
}
</script>
</body>
</html>
