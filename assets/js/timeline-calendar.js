(function($) {
    'use strict';

    function hideInitialCollapsed() {
        const $timeline = $('#argon-timeline-calendar');
        if (!$timeline.length) return;
        $timeline.find('.timeline-year-collapsed .timeline-year-events').hide();
        $timeline.find('.timeline-month-collapsed .timeline-month-events').hide();
    }

    function scrollToCurrentMonth() {
        const $timeline = $('#argon-timeline-calendar');
        if (!$timeline.length) return false;
        const now = new Date();
        const currentYear = now.getFullYear();
        const currentMonth = now.getMonth() + 1;
        const $currentMonthWrapper = $(`#timeline-month-${currentYear}-${currentMonth}`);
        if ($currentMonthWrapper.length && $currentMonthWrapper.offset()) {
            const $yearGroup = $currentMonthWrapper.closest('.timeline-year-group');
            if ($yearGroup.hasClass('timeline-year-collapsed')) {
                $yearGroup.removeClass('timeline-year-collapsed');
                $yearGroup.find('.timeline-year-events').show();
            }
            if ($currentMonthWrapper.hasClass('timeline-month-collapsed')) {
                $currentMonthWrapper.removeClass('timeline-month-collapsed');
                $currentMonthWrapper.find('.timeline-month-events').show();
            }
            $('html, body').animate({ scrollTop: $currentMonthWrapper.offset().top - 100 }, 500);
            return true;
        }
        return false;
    }

    function scrollToCurrentMonthWithRetry(maxAttempts = 10, delayMs = 120) {
        let attempts = 0;
        function tryScroll() {
            if (scrollToCurrentMonth()) return;
            attempts++;
            if (attempts < maxAttempts) {
                setTimeout(tryScroll, delayMs);
            }
        }
        tryScroll();
    }

    function initializeTimeline() {
        hideInitialCollapsed();
        // 使用重试，兼容 PJAX 内容延迟渲染
        scrollToCurrentMonthWithRetry();
    }

    // 委托事件绑定到 document，避免 PJAX 替换后失效
    $(document)
        .off('click.timeline-year')
        .on('click.timeline-year', '.argon-timeline-calendar .timeline-year-header', function() {
            const $group = $(this).closest('.timeline-year-group');
            const $events = $group.find('.timeline-year-events');
            if ($events.is(':visible')) {
                $events.stop(true, true).slideUp(300);
                $group.addClass('timeline-year-collapsed');
            } else {
                $events.stop(true, true).slideDown(300);
                $group.removeClass('timeline-year-collapsed');
            }
        })
        .off('click.timeline-month')
        .on('click.timeline-month', '.argon-timeline-calendar .timeline-month-header', function() {
            const $wrapper = $(this).closest('.timeline-month-wrapper');
            const $events = $wrapper.find('.timeline-month-events');
            if ($events.is(':visible')) {
                $events.stop(true, true).slideUp(300);
                $wrapper.addClass('timeline-month-collapsed');
            } else {
                $events.stop(true, true).slideDown(300);
                $wrapper.removeClass('timeline-month-collapsed');
            }
        });

    // 首次加载
    $(document).ready(function() {
        initializeTimeline();
    });

    // PJAX 场景：兼容多种事件名称
    $(document).on('pjax:end pjax:complete pjax:success', function() {
        initializeTimeline();
    });

})(jQuery);
