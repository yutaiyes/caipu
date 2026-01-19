</div>
</div>
</div>
</div>
<!-- 返回顶部按钮 -->
<button id="backToTop" class="btn btn-primary" style="display: none; position: fixed; bottom: 30px; right: 30px; z-index: 1000; border-radius: 50%; width: 50px; height: 50px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);" title="返回顶部">
<i class="fas fa-arrow-up"></i>
</button>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>

