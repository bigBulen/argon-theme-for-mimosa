jQuery(document).ready(function($) {
    // 初始化颜色选择器
    $('.color-picker').wpColorPicker();
    
    // 模态框控制
    const modal = $('#event-modal');
    const form = $('#event-form');
    
    // 打开添加事件模态框
    $('#add-new-event').on('click', function() {
        $('#modal-title').text('添加新事件');
        form[0].reset();
        $('#event-id').val('');
        $('.color-picker').wpColorPicker('color', '#333333');
        $('#background-color').wpColorPicker('color', '#f8f9fa');
        modal.show();
    });
    
    // 关闭模态框
    $('.timeline-modal-close, #cancel-event').on('click', function() {
        modal.hide();
    });
    
    // 点击模态框外部关闭
    modal.on('click', function(e) {
        if (e.target === this) {
            modal.hide();
        }
    });
    
    // 编辑事件
    $(document).on('click', '.edit-event', function() {
        const eventId = $(this).data('id');
        
        $.ajax({
            url: timeline_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'timeline_get_event',
                event_id: eventId,
                nonce: timeline_ajax.nonce
            },
            beforeSend: function() {
                $(this).prop('disabled', true).text('加载中...');
            },
            success: function(response) {
                if (response.success) {
                    const event = response.data;
                    
                    $('#modal-title').text('编辑事件');
                    $('#event-id').val(event.id);
                    $('#event-title').val(event.title);
                    $('#event-description').val(event.description);
                    $('#event-date').val(event.event_date);
                    $('#event-time').val(event.event_time);
                    $('#event-category').val(event.category);
                    $('#event-icon').val(event.icon);
                    $('#event-status').val(event.status);
                    
                    // 设置颜色选择器
                    $('#text-color').wpColorPicker('color', event.text_color);
                    $('#background-color').wpColorPicker('color', event.background_color);
                    
                    modal.show();
                } else {
                    alert('获取事件信息失败：' + response.data);
                }
            },
            error: function() {
                alert('请求失败，请重试');
            },
            complete: function() {
                $('.edit-event').prop('disabled', false).text('编辑');
            }
        });
    });
    
    // 保存事件
    $('#save-event').on('click', function() {
        const formData = {
            action: 'timeline_save_event',
            nonce: timeline_ajax.nonce,
            event_id: $('#event-id').val(),
            title: $('#event-title').val(),
            description: $('#event-description').val(),
            event_date: $('#event-date').val(),
            event_time: $('#event-time').val(),
            category: $('#event-category').val(),
            text_color: $('#text-color').val(),
            background_color: $('#background-color').val(),
            icon: $('#event-icon').val(),
            status: $('#event-status').val()
        };
        
        // 简单验证
        if (!formData.title.trim()) {
            alert('请输入事件标题');
            $('#event-title').focus();
            return;
        }
        
        if (!formData.event_date) {
            alert('请选择事件日期');
            $('#event-date').focus();
            return;
        }
        
        $.ajax({
            url: timeline_ajax.ajax_url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#save-event').prop('disabled', true).text('保存中...');
            },
            success: function(response) {
                if (response.success) {
                    alert('保存成功');
                    modal.hide();
                    location.reload(); // 刷新页面显示最新数据
                } else {
                    alert('保存失败：' + response.data);
                }
            },
            error: function() {
                alert('请求失败，请重试');
            },
            complete: function() {
                $('#save-event').prop('disabled', false).text('保存');
            }
        });
    });
    
    // 删除事件
    $(document).on('click', '.delete-event', function() {
        const eventId = $(this).data('id');
        const eventTitle = $(this).closest('tr').find('td:first strong').text();
        
        if (!confirm('确定要删除事件 "' + eventTitle + '" 吗？此操作不可恢复。')) {
            return;
        }
        
        $.ajax({
            url: timeline_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'timeline_delete_event',
                event_id: eventId,
                nonce: timeline_ajax.nonce
            },
            beforeSend: function() {
                $(this).prop('disabled', true).text('删除中...');
            },
            success: function(response) {
                if (response.success) {
                    alert('删除成功');
                    location.reload(); // 刷新页面
                } else {
                    alert('删除失败：' + response.data);
                }
            },
            error: function() {
                alert('请求失败，请重试');
            },
            complete: function() {
                $('.delete-event').prop('disabled', false).text('删除');
            }
        });
    });
    
    // 表单验证增强
    $('#event-title, #event-date').on('blur', function() {
        const $this = $(this);
        if (!$this.val().trim()) {
            $this.css('border-color', '#e74c3c');
        } else {
            $this.css('border-color', '#ddd');
        }
    });
    
    // 图标预览
    $('#event-icon').on('input', function() {
        const iconClass = $(this).val();
        const preview = $(this).next('.icon-preview');
        
        if (preview.length === 0) {
            $(this).after('<span class="icon-preview" style="margin-left: 10px;"></span>');
        }
        
        if (iconClass) {
            $(this).next('.icon-preview').html('<i class="' + iconClass + '"></i>');
        } else {
            $(this).next('.icon-preview').empty();
        }
    });
    
    // 键盘快捷键
    $(document).on('keydown', function(e) {
        // ESC 关闭模态框
        if (e.keyCode === 27 && modal.is(':visible')) {
            modal.hide();
        }
        
        // Ctrl+S 保存（在模态框打开时）
        if (e.ctrlKey && e.keyCode === 83 && modal.is(':visible')) {
            e.preventDefault();
            $('#save-event').click();
        }
    });
    
    // 自动保存草稿功能（可选）
    let autoSaveTimer;
    form.on('input', 'input, textarea, select', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            // 这里可以实现自动保存草稿的逻辑
            console.log('Auto-save triggered');
        }, 5000); // 5秒后自动保存
    });
});