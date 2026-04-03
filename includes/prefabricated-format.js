/**
 * Argon 扩展语法系统 (注释/高斯模糊/黑幕)
 */
(function() {
    const CONFIG = {
        selector: "article, .entry-content, .post-content, .card-body, .leftbar_announcement, .shuoshuo-content",
        popupClass: "annotation-popup",
        activeClass: "annotate",
        processedAttr: "data-syntax-processed"
    };

    const RULES = [
        {
            name: 'annotate',
            // 去掉全局 g 标志，方便局部处理捕获组
            regex: /\[annotate\]([\s\S]*?)\|\|([\s\S]*?)\[\/annotate\]/,
            render: (match, word, note) => {
                const span = document.createElement("span");
                span.className = CONFIG.activeClass;
                span.setAttribute("data-note", escapeHtml(note));
                span.textContent = word;
                return span;
            }
        },
        {
            name: 'blur',
            regex: /\[blur\]([\s\S]*?)\[\/blur\]/,
            render: (match, content) => {
                const span = document.createElement("span");
                span.className = "argon-hidden-text argon-hidden-text-blur";
                span.innerHTML = content;
                return span;
            }
        },
        {
            name: 'black',
            regex: /\[black\]([\s\S]*?)\[\/black\]/,
            render: (match, content) => {
                const span = document.createElement("span");
                span.className = "argon-hidden-text argon-hidden-text-background";
                span.innerHTML = content;
                return span;
            }
        }
    ];

    function escapeHtml(str) {
        return str ? str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;") : "";
    }

    // 构造联合正则时动态加上 g
    const combinedRegex = new RegExp(RULES.map(r => `(${r.regex.source})`).join('|'), 'g');

    function processTextNode(node) {
        const text = node.nodeValue;
        if (!text || !text.includes("[")) return;

        let lastIndex = 0;
        let match;
        const fragment = document.createDocumentFragment();
        let matchedAny = false;

        // 必须重置联合正则的 lastIndex
        combinedRegex.lastIndex = 0;

        while ((match = combinedRegex.exec(text)) !== null) {
            matchedAny = true;
            if (match.index > lastIndex) {
                fragment.appendChild(document.createTextNode(text.slice(lastIndex, match.index)));
            }

            // 核心修复：遍历 RULES 找到匹配的那一项
            for (const rule of RULES) {
                const localMatch = match[0].match(rule.regex);
                if (localMatch) {
                    // 使用 spread 操作符将匹配组传给 render (match, group1, group2...)
                    const resultEl = rule.render(...localMatch);
                    fragment.appendChild(resultEl);
                    break;
                }
            }
            lastIndex = combinedRegex.lastIndex;
        }

        if (matchedAny) {
            if (lastIndex < text.length) {
                fragment.appendChild(document.createTextNode(text.slice(lastIndex)));
            }
            node.parentNode.replaceChild(fragment, node);
        }
    }

    // 遍历 DOM 树
    function walk(node) {
        if (node.nodeType === 1) {
            if (["SCRIPT", "STYLE", "CODE", "PRE", "TEXTAREA", "INPUT"].includes(node.tagName)) return;
            if (node.hasAttribute(CONFIG.processedAttr)) return;
        }
        
        if (node.nodeType === 3) {
            processTextNode(node);
        } else {
            // 提前转化数组防止替换节点时导致实时 NodeList 变动异常
            Array.from(node.childNodes).forEach(walk);
            if (node.nodeType === 1 && node.matches(CONFIG.selector)) {
                node.setAttribute(CONFIG.processedAttr, "true");
            }
        }
    }

    // --- UI 逻辑（弹窗部分保持高效） ---
    let popup = null, hideTimer = null;

    function ensurePopupExists() {
        if (popup) return;
        popup = document.createElement("div");
        popup.className = CONFIG.popupClass;
        document.body.appendChild(popup);
        popup.addEventListener("mouseenter", () => clearTimeout(hideTimer));
        popup.addEventListener("mouseleave", hidePopup);
    }

    function showPopup(el) {
        clearTimeout(hideTimer);
        ensurePopupExists();
        const note = el.getAttribute("data-note");
        if (!note) return;

        popup.innerHTML = note;
        popup.classList.add("show");

        const rect = el.getBoundingClientRect();
        let top = rect.top + window.pageYOffset - popup.offsetHeight - 10;
        let left = rect.left + window.pageXOffset;

        if (top < window.pageYOffset + 10) {
            top = rect.bottom + window.pageYOffset + 10;
            popup.style.transformOrigin = "top";
        } else {
            popup.style.transformOrigin = "bottom";
        }
        left = Math.max(10, Math.min(left, window.innerWidth - popup.offsetWidth - 10));

        popup.style.top = top + "px";
        popup.style.left = left + "px";
    }

    function hidePopup() {
        hideTimer = setTimeout(() => {
            if (popup) popup.classList.remove("show");
        }, 200);
    }

    // --- 初始化与监听 ---
    function bootstrap() {
        // 绑定事件（委托机制，不影响性能）
        if (!window.syntaxEventsBound) {
            document.addEventListener("mouseover", e => {
                const t = e.target.closest(`.${CONFIG.activeClass}`);
                if (t) showPopup(t);
            });
            document.addEventListener("mouseout", e => {
                if (e.target.closest(`.${CONFIG.activeClass}`)) hidePopup();
            });
            window.syntaxEventsBound = true;
        }

        // 初次扫描
        document.querySelectorAll(CONFIG.selector).forEach(el => walk(el));

        // 监听动态内容（性能优化版）
        const observer = new MutationObserver(mutations => {
            requestAnimationFrame(() => {
                for (const m of mutations) {
                    m.addedNodes.forEach(node => {
                        if (node.nodeType === 1 || node.nodeType === 3) walk(node);
                    });
                }
            });
        });
        
        const container = document.querySelector("#app, #content, .site-content") || document.body;
        observer.observe(container, { childList: true, subtree: true });
    }

    // Pjax 支持
    const run = () => {
        if (typeof bootstrap === 'function') bootstrap();
    };
    window.addEventListener('pjax:success', () => setTimeout(run, 300));
    
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", bootstrap);
    } else {
        bootstrap();
    }
})();