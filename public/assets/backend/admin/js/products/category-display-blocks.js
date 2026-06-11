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
    const statusUpdatedMessage = configEl.dataset.statusUpdatedMessage || 'Status updated successfully';
    const statusFailedMessage = configEl.dataset.statusFailedMessage || 'Failed to update status';
    const reorderSavedMessage = configEl.dataset.reorderSavedMessage || 'Block order saved';
    const reorderFailedMessage = configEl.dataset.reorderFailedMessage || 'Failed to save block order';

    function showToast(type, message) {
        if (typeof toastMagic !== 'undefined') {
            toastMagic[type](message);
        }
    }

    const list = document.getElementById('category-display-blocks-list');
    if (list) {
        let draggedItem = null;
        let previousOrder = [];

        list.addEventListener('drop', (e) => {
            e.preventDefault();
        });

        function captureOrder() {
            return Array.from(list.querySelectorAll('li[data-block-id]'));
        }

        function restoreOrder() {
            previousOrder.forEach((item) => {
                list.appendChild(item);
            });
        }

        list.querySelectorAll('li[data-block-id]').forEach((li) => {
            const handle = li.querySelector('.category-display-block-drag-handle');
            if (!handle || handle.disabled) {
                return;
            }

            handle.addEventListener('mousedown', () => li.setAttribute('draggable', 'true'));

            li.addEventListener('dragstart', () => {
                previousOrder = captureOrder();
                draggedItem = li;
                li.classList.add('opacity-50');
            });

            li.addEventListener('dragend', () => {
                li.classList.remove('opacity-50');
                li.removeAttribute('draggable');

                if (!draggedItem) {
                    return;
                }

                const currentOrder = captureOrder().map((item) => item.dataset.blockId);
                const previousOrderIds = previousOrder.map((item) => item.dataset.blockId);
                const orderChanged = currentOrder.some((id, index) => id !== previousOrderIds[index]);

                if (orderChanged) {
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
            const blockIds = captureOrder().map((item) => item.dataset.blockId);

            $.ajax({
                url: reorderUrl,
                method: 'POST',
                data: {
                    _token: csrfToken,
                    category_id: categoryId,
                    block_ids: blockIds,
                },
                success: function (response) {
                    previousOrder = captureOrder();
                    showToast('success', response.message || reorderSavedMessage);
                },
                error: function (xhr) {
                    restoreOrder();
                    const message = xhr.responseJSON?.message || reorderFailedMessage;
                    showToast('error', message);
                },
            });
        }
    }

    $('.category-display-block-status').on('change', function () {
        const $input = $(this);
        const id = $input.data('id');
        const isActive = $input.is(':checked') ? 1 : 0;
        const previousState = !isActive;

        $.ajax({
            url: statusUrl,
            method: 'POST',
            data: {
                _token: csrfToken,
                id: id,
                is_active: isActive,
            },
            success: function (response) {
                showToast('success', response.message || statusUpdatedMessage);
            },
            error: function (xhr) {
                $input.prop('checked', previousState);
                const message = xhr.responseJSON?.message || statusFailedMessage;
                showToast('error', message);
            },
        });
    });

    $('#block-type-select').on('change', function () {
        const description = $(this).find(':selected').data('description') || '';
        $('#block-type-description').text(description);
    });
})(jQuery);
