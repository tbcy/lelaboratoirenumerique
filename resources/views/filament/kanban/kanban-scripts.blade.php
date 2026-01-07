@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@endpush

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('kanbanBoard', ({ statuses }) => ({
            statuses: statuses,

            init() {
                this.$nextTick(() => {
                    this.initSortable();
                });
            },

            initSortable() {
                const columns = document.querySelectorAll('.kanban-column');

                columns.forEach(column => {
                    new Sortable(column, {
                        group: 'kanban',
                        animation: 150,
                        ghostClass: 'opacity-50',
                        dragClass: 'shadow-lg',
                        handle: '.kanban-record',
                        draggable: '.kanban-record',

                        onEnd: (evt) => {
                            const recordId = evt.item.dataset.id;
                            const newStatus = evt.to.dataset.status;
                            const oldStatus = evt.from.dataset.status;

                            const fromOrderedIds = Array.from(evt.from.querySelectorAll('.kanban-record'))
                                .map(el => el.dataset.id);

                            const toOrderedIds = Array.from(evt.to.querySelectorAll('.kanban-record'))
                                .map(el => el.dataset.id);

                            if (oldStatus !== newStatus) {
                                @this.dispatch('kanban-status-changed', {
                                    recordId: recordId,
                                    status: newStatus,
                                    fromOrderedIds: fromOrderedIds,
                                    toOrderedIds: toOrderedIds
                                });
                            } else {
                                @this.dispatch('kanban-sort-changed', {
                                    recordId: recordId,
                                    status: newStatus,
                                    orderedIds: toOrderedIds
                                });
                            }
                        }
                    });
                });
            }
        }));
    });
</script>
