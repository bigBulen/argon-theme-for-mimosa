(function (blocks, editor, element, components) {
    var el = element.createElement;
    var MediaUpload = editor.MediaUpload;
    var InspectorControls = editor.InspectorControls;
    var TextControl = components.TextControl;

    blocks.registerBlockType('custom/live-photos-block', {
    title: 'Live Photos Block',
    icon: 'camera',
    category: 'media',
    attributes: {
        photoURL: {
            type: 'string',
            default: ''
        },
        videoURL: {
            type: 'string',
            default: ''
        },
        width: {
            type: 'number',
            default: 400
        },
        height: {
            type: 'number',
            default: 300
        }
    },

    edit: function (props) {
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;

        return el(
            'div',
            { className: props.className },
            el('p', {}, '选择图片和视频：'),
            el(
                MediaUpload,
                {
                    onSelect: function (media) {
                        setAttributes({ photoURL: media.url });
                    },
                    allowedTypes: 'image',
                    render: function (obj) {
                        var fileName = '';
                        if (attributes.photoURL) {
                            // 从 URL 中提取文件名
                            fileName = attributes.photoURL.split('/').pop();
                        }
                        return el(
                            components.Button,
                            {
                                className: 'button button-large',
                                onClick: obj.open
                            },
                            attributes.photoURL ? fileName : '选择图片'
                        );
                }

                }
            ),
            el(
                MediaUpload,
                {
                    onSelect: function (media) {
                        setAttributes({ videoURL: media.url });
                    },
                    allowedTypes: 'video',
                    render: function (obj) {
                        var fileNam1e = '';
                        if (attributes.videoURL) {
                            // 从 URL 中提取文件名
                            fileName1 = attributes.videoURL.split('/').pop();
                        }
                        return el(
                            components.Button,
                            {
                                className: 'button button-large',
                                onClick: obj.open
                            },
                            attributes.videoURL ? fileName1 : '选择视频'
                        );
                    }
                }
            ),
            el(InspectorControls, {},
                el(TextControl, {
                    label: '宽度(px)',
                    value: attributes.width,
                    onChange: function (value) {
                        setAttributes({ width: parseInt(value, 10) || 0 });
                    }
                }),
                el(TextControl, {
                    label: '高度(px)',
                    value: attributes.height,
                    onChange: function (value) {
                        setAttributes({ height: parseInt(value, 10) || 0 });
                    }
                })
            )
        );
    },

    save: function () {
        // 后台通过 PHP 渲染，前端保存为空
        return null;
    }
});

}(
    window.wp.blocks,
    window.wp.editor,
    window.wp.element,
    window.wp.components
));