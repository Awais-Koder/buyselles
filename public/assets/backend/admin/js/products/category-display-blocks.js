'use strict';

(function ($) {
    const configEl = document.getElementById('category-display-blocks-config');
    if (!configEl) {
        return;
    }

    const categoryId = configEl.dataset.categoryId;
    const reorderUrl = configEl.dataset.reorderUrl;
    const statusUrl = configEl.dataset.statusUrl;
    const csrfToken = configEl.dataset.csrf;

    const list = document.getElementById('category-display-blocks-list');
    if (list) {
        let draggedItem = null;

        list.querySelectorAll('li[data-block-id]').forEach((li) => {
            const handle = li.querySelector('.category-display-block-drag-handle');
            if (!handle || handle.disabled) {
                return;
            }

            handle.addEventListener('mousedown', () => li.setAttribute('draggable', 'true'));

            li.addEventListener('dragstart', (e) => {
                draggedItem = li;
                li.classList.add('opacity-50');
                e.dataTransfer.effectAllowed = 'move';
            });

            li.addEventListener('dragend', () => {
                li.classList.remove('opacity-50');
                li.removeAttribute('draggable');
                if (draggedItem) {
                    saveOrder();
                }
                draggedItem = null;
            });

            li.addEventListener('dragover', (e) => {
                e.preventDefault();
                if (!draggedItem || draggedItem === li) {
                    return;
                }
                const rect = li.getBoundingClientRect();
                const offset = e.clientY - rect.top;
                if (offset > rect.height / 2) {
                    li.after(draggedItem);
                } else {
                    li.before(draggedItem);
                }
            });
        });

        function saveOrder() {
            const blockIds = Array.from(list.querySelectorAll('li[data-block-id]')).map(
                (item) => item.dataset.blockId
            );

            $.ajax({
                url: reorderUrl,
                method: 'POST',
                data: {
                    _token: csrfToken,
                    category_id: categoryId,
                    block_ids: blockIds,
                },
                success: function () {},
                error: function () {
                    if (typeof toastMagic !== 'undefined') {
                        toastMagic.error('Failed to save block order');
                    }
                },
            });
        }
    }

    $('.category-display-block-status').on('change', function () {
        const id = $(this).data('id');
        const isActive = $(this).is(':checked') ? 1 : 0;

        $.ajax({
            url: statusUrl,
            method: 'POST',
            data: {
                _token: csrfToken,
                id: id,
                is_active: isActive,
            },
            error: function () {
                if (typeof toastMagic !== 'undefined') {
                    toastMagic.error('Failed to update status');
                }
            },
        });
    });

    $('#block-type-select').on('change', function () {
        const description = $(this).find(':selected').data('description') || '';
        $('#block-type-description').text(description);
    });
})(jQuery);
