<?php
require'layout_header.php';
require'../libs/Parsedown.php';
$docs_dir='../readme/';
$files=glob($docs_dir.'*.md');
$docs=[];
foreach($files as $file){
$filename=basename($file);
$name=str_replace('.md','',$filename);
$icon='fa-file-alt';
if(strpos($name,'README')!==false)$icon='fa-home';
elseif(strpos($name,'QUICK')!==false)$icon='fa-rocket';
elseif(strpos($name,'GUIDE')!==false)$icon='fa-book';
elseif(strpos($name,'UPGRADE')!==false)$icon='fa-arrow-up';
elseif(strpos($name,'COMPARISON')!==false)$icon='fa-balance-scale';
elseif(strpos($name,'SUMMARY')!==false)$icon='fa-chart-bar';
elseif(strpos($name,'FILE')!==false)$icon='fa-folder';
elseif(strpos($name,'ADMIN')!==false)$icon='fa-user-shield';
elseif(strpos($name,'FRONTEND')!==false)$icon='fa-desktop';
elseif(strpos($name,'BACKEND')!==false)$icon='fa-server';
elseif(strpos($name,'FEATURES')!==false)$icon='fa-star';
elseif(strpos($name,'VISUAL')!==false)$icon='fa-eye';
elseif(strpos($name,'PROJECT')!==false)$icon='fa-project-diagram';
elseif(strpos($name,'SECURITY')!==false)$icon='fa-shield-alt';
elseif(strpos($name,'RESPONSIVE')!==false)$icon='fa-mobile-alt';
elseif(strpos($name,'DEBUG')!==false)$icon='fa-bug';
elseif(strpos($name,'HOTFIX')!==false)$icon='fa-wrench';
elseif(strpos($name,'UPDATE')!==false)$icon='fa-sync-alt';
elseif(strpos($name,'LAYOUT')!==false)$icon='fa-th-large';
$docs[]=[
'filename'=>$filename,
'name'=>$name,
'slug'=>$name,
'icon'=>$icon,
'path'=>$file
];
}
usort($docs,function($a,$b){
if(strpos($a['name'],'README')!==false)return-1;
if(strpos($b['name'],'README')!==false)return 1;
return strcmp($a['name'],$b['name']);
});
$current_slug=$_GET['doc']??'';
$current_doc='';
$current_content='';
$current_name='';
$current_icon='fa-file-alt';
if(!empty($current_slug)&&substr($current_slug,-3)==='.md'){
$clean_slug=str_replace('.md','',$current_slug);
header('Location: docs.php?doc='.urlencode($clean_slug));
exit;
}
if($current_slug){
foreach($docs as $doc){
if($doc['slug']===$current_slug){
$current_doc=$doc['filename'];
$current_name=$doc['name'];
$current_icon=$doc['icon'];
break;
}
}
}else{
if(!empty($docs)){
$current_doc=$docs[0]['filename'];
$current_name=$docs[0]['name'];
$current_icon=$docs[0]['icon'];
$current_slug=$docs[0]['slug'];
}
}
if($current_doc){
$doc_path=$docs_dir.$current_doc;
if(file_exists($doc_path)){
$markdown=file_get_contents($doc_path);
$Parsedown=new Parsedown();
$current_content=$Parsedown->text($markdown);
}
}
?>
<div class="page-header d-flex justify-content-between align-items-center">
<h3 class="mb-0"><i class="fas fa-book"></i> 文档中心</h3>
<!-- 移动端文档选择按钮 -->
<button class="btn btn-primary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#docList">
<i class="fas fa-list"></i> 文档列表
</button>
</div>
<!-- 桌面端：文档选择器在右上方 -->
<div class="row mb-3 d-none d-md-flex">
<div class="col-md-12">
<div class="doc-selector-horizontal">
<label class="me-2"><i class="fas fa-file-alt"></i> 选择文档：</label>
<select class="form-select form-select-sm d-inline-block w-auto" onchange="if(this.value) location.href='?doc='+this.value">
<option value="">-- 请选择 --</option>
<?php foreach($docs as $doc):?>
<option value="<?=urlencode($doc['slug'])?>" <?=$current_slug==$doc['slug']?'selected':''?>>
<?=htmlspecialchars($doc['name'])?>
</option>
<?php endforeach;?>
</select>
</div>
</div>
</div>
<!-- 移动端：折叠的文档列表 -->
<div class="collapse d-md-none mb-3" id="docList">
<div class="doc-list-mobile">
<?php foreach($docs as $doc):?>
<a href="?doc=<?=urlencode($doc['slug'])?>"
class="doc-link-mobile <?=$current_slug==$doc['slug']?'active':''?>">
<i class="fas <?=$doc['icon']?>"></i>
<?=htmlspecialchars($doc['name'])?>
</a>
<?php endforeach;?>
</div>
</div>
<!-- 文档内容 -->
<div class="row">
<div class="col-12">
<div class="doc-content">
<?php if($current_content):?>
<div class="doc-title-bar">
<h4><i class="fas <?=$current_icon?>"></i> <?=htmlspecialchars($current_name)?></h4>
</div>
<div class="markdown-body">
<?=$current_content?>
</div>
<?php else:?>
<div class="text-center text-muted py-5">
<i class="fas fa-file-alt fa-4x mb-3"></i>
<h4>请选择一个文档查看</h4>
<p>从上方下拉菜单中选择要查看的文档</p>
</div>
<?php endif;?>
</div>
</div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.5.0/github-markdown.min.css">
<script>
// 让所有外部链接在新标签页打开
document.addEventListener('DOMContentLoaded', function() {
    const markdownBody = document.querySelector('.markdown-body');
    if (markdownBody) {
        // 为所有标题添加ID，支持锚点跳转
        const headers = markdownBody.querySelectorAll('h1, h2, h3, h4, h5, h6');
        headers.forEach(header => {
            // 生成友好的ID，使用encodeURIComponent编码以便匹配URL中的锚点
            let text = header.textContent.trim();

            // 清理标题文本：移除常见的前缀和后缀
            text = text
                .replace(/^[A-Z]\d+[:：]\s*/, '') // 移除 Q1: Q2: 等前缀
                .replace(/[?？!！。,，;；]$/, ''); // 移除末尾的标点符号

            // 两种格式的ID：纯小写连字符格式 和 URL编码格式
            const plainId = text
                .toLowerCase()
                .replace(/[^\w\u4e00-\u9fa5-]+/g, '-') // 保留中文字符、字母、数字、连字符
                .replace(/^-+|-+$/g, ''); // 移除首尾的连字符

            const encodedId = encodeURIComponent(text)
                .replace(/[!'()*]/g, function(c) {
                    return '%' + c.charCodeAt(0).toString(16);
                });

            // 使用URL编码格式作为主要ID
            if (encodedId && plainId !== encodedId) {
                header.id = encodedId;
            } else if (plainId) {
                header.id = plainId;
            } else {
                header.id = 'header-' + Math.random().toString(36).substr(2, 9);
            }
        });

        const links = markdownBody.querySelectorAll('a[href^="http"]');
        links.forEach(link => {
            // 检查是否是外部链接
            const url = new URL(link.href);
            if (url.hostname !== window.location.hostname) {
                link.setAttribute('target', '_blank');
                link.setAttribute('rel', 'noopener noreferrer');
            }
        });

        // 替换文档内部链接，统一使用当前页面路径
        const docLinks = markdownBody.querySelectorAll('a[href*="?doc="]');
        docLinks.forEach(link => {
            const href = link.getAttribute('href');
            // 替换 index.php?doc= 或 docs.php?doc= 为当前页面的路径
            if (href.includes('index.php?doc=')) {
                link.setAttribute('href', href.replace('index.php?doc=', 'docs.php?doc='));
            }
        });
    }
    
    // 处理锚点跳转
    if (window.location.hash) {
        setTimeout(function() {
            const hash = window.location.hash;
            let target = document.querySelector(hash);

            // 如果直接查询失败，尝试解码后查询
            if (!target) {
                try {
                    const decodedHash = '#' + decodeURIComponent(hash.substring(1));
                    target = document.querySelector(decodedHash);
                } catch (e) {
                    // 解码失败，继续尝试其他方法
                }
            }

            // 如果仍然失败，尝试通过文本内容匹配
            if (!target) {
                const hashText = hash.substring(1).toLowerCase();
                const headers = markdownBody.querySelectorAll('h1, h2, h3, h4, h5, h6');
                headers.forEach(header => {
                    let headerText = header.textContent.trim().toLowerCase();
                    // 尝试匹配原始文本
                    if (headerText === hashText) {
                        target = header;
                        return;
                    }

                    // 尝试匹配清理后的文本（移除前缀和标点）
                    let cleanedText = headerText
                        .replace(/^[a-z]\d+[:：]\s*/, '') // 移除 q1: q2: 等前缀
                        .replace(/[?？!！。,，;；]$/, ''); // 移除末尾的标点符号

                    if (cleanedText === hashText) {
                        target = header;
                        return;
                    }

                    // 尝试匹配连字符格式
                    const dashedText = headerText.replace(/[^\w\u4e00-\u9fa5-]+/g, '-').replace(/^-+|-+$/g, '');
                    if (dashedText === hashText) {
                        target = header;
                        return;
                    }

                    const dashedCleanedText = cleanedText.replace(/[^\w\u4e00-\u9fa5-]+/g, '-').replace(/^-+|-+$/g, '');
                    if (dashedCleanedText === hashText) {
                        target = header;
                    }
                });
            }

            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // 高亮目标元素
                target.style.backgroundColor = '#fff3cd';
                setTimeout(() => {
                    target.style.transition = 'background-color 2s';
                    target.style.backgroundColor = '';
                }, 1000);
            }
        }, 100);
    }
    
    // 处理文档内链接的锚点跳转
    markdownBody.querySelectorAll('a[href*="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            const hashIndex = href.indexOf('#');

            // 如果链接包含锚点
            if (hashIndex !== -1) {
                const hash = href.substring(hashIndex); // 获取#及其后面的内容
                const queryString = href.substring(0, hashIndex); // 获取#前面的查询参数部分

                // 如果只是简单的锚点链接或当前页面的锚点
                if (queryString === '' || queryString === window.location.search) {
                    e.preventDefault();
                    const target = document.querySelector(hash);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        // 更新URL但不刷新页面
                        history.pushState(null, null, hash);
                        // 高亮目标元素
                        target.style.backgroundColor = '#fff3cd';
                        setTimeout(() => {
                            target.style.transition = 'background-color 2s';
                            target.style.backgroundColor = '';
                        }, 1000);
                    }
                } else if (queryString.indexOf('?doc=') !== -1) {
                    // 如果是切换文档+锚点的链接，需要处理跳转后滚动到锚点
                    // 保存锚点到sessionStorage，页面加载后自动跳转
                    sessionStorage.setItem('docAnchor', hash);
                }
            }
        });
    });

    // 检查是否有保存的锚点，有则跳转
    const savedAnchor = sessionStorage.getItem('docAnchor');
    if (savedAnchor) {
        sessionStorage.removeItem('docAnchor');
        setTimeout(function() {
            let target = document.querySelector(savedAnchor);

            // 如果直接查询失败，尝试解码后查询
            if (!target && savedAnchor.startsWith('#')) {
                try {
                    const decodedHash = '#' + decodeURIComponent(savedAnchor.substring(1));
                    target = document.querySelector(decodedHash);
                } catch (e) {
                    // 解码失败，继续尝试其他方法
                }
            }

            // 如果仍然失败，尝试通过文本内容匹配
            if (!target && savedAnchor.startsWith('#')) {
                const hashText = savedAnchor.substring(1).toLowerCase();
                const headers = markdownBody.querySelectorAll('h1, h2, h3, h4, h5, h6');
                headers.forEach(header => {
                    let headerText = header.textContent.trim().toLowerCase();
                    // 尝试匹配原始文本
                    if (headerText === hashText) {
                        target = header;
                        return;
                    }

                    // 尝试匹配清理后的文本（移除前缀和标点）
                    let cleanedText = headerText
                        .replace(/^[a-z]\d+[:：]\s*/, '') // 移除 q1: q2: 等前缀
                        .replace(/[?？!！。,，;；]$/, ''); // 移除末尾的标点符号

                    if (cleanedText === hashText) {
                        target = header;
                        return;
                    }

                    // 尝试匹配连字符格式
                    const dashedText = headerText.replace(/[^\w\u4e00-\u9fa5-]+/g, '-').replace(/^-+|-+$/g, '');
                    if (dashedText === hashText) {
                        target = header;
                        return;
                    }

                    const dashedCleanedText = cleanedText.replace(/[^\w\u4e00-\u9fa5-]+/g, '-').replace(/^-+|-+$/g, '');
                    if (dashedCleanedText === hashText) {
                        target = header;
                    }
                });
            }

            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // 更新URL
                history.pushState(null, null, savedAnchor);
                // 高亮目标元素
                target.style.backgroundColor = '#fff3cd';
                setTimeout(() => {
                    target.style.transition = 'background-color 2s';
                    target.style.backgroundColor = '';
                }, 1000);
            }
        }, 300);
    }
});
</script>
<?php require'layout_footer.php';?>

