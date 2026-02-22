window.pjaxLoaded = function () {
    // 确保 livephotoskit 脚本加载完成
    if (window.LivePhotosKit) {
        const elements = document.querySelectorAll('[data-live-photo]');
        elements.forEach((el) => {
            // 清除已绑定的，避免重复初始化
            if (el._livePhoto) {
                el._livePhoto.stop();
            }

            el._livePhoto = new LivePhotosKit.Player(el);
            el._livePhoto.photo.src = el.dataset.photoSrc;
            el._livePhoto.video.src = el.dataset.videoSrc;
        });
        

        // 👉 拓展：可以在这里添加事件委托，让整个 wrapper 触发效果
        // document.querySelectorAll('.live-photo-wrapper').forEach(wrapper => {
        //     wrapper.addEventListener('mouseenter', () => {
        //         wrapper.querySelector('[data-live-photo]')?.dispatchEvent(new Event('mouseenter'));
        //     });
        //     wrapper.addEventListener('mouseleave', () => {
        //         wrapper.querySelector('[data-live-photo]')?.dispatchEvent(new Event('mouseleave'));
        //     });
        // });
    }
};

// 页面首次载入时也执行一次
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.pjaxLoaded);
} else {
    window.pjaxLoaded();
}

