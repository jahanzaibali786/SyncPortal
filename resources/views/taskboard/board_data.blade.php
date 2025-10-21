@php
    $addTaskPermission = user()->permission('add_tasks');
    $addStatusPermission = user()->permission('add_status');
    $changeStatusPermission = user()->permission('change_status');
@endphp

<style>
    .quick-add-task-container {
        animation: fadeIn 0.2s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .quick-task-input {
        border: none;
        box-shadow: none;
        font-size: 14px;
        padding: 8px;
    }

    .quick-task-input:focus {
        border: none;
        box-shadow: none;
        outline: none;
    }

    .quick-add-trigger:hover {
        text-decoration: none;
        color: #007bff !important;
    }
</style>

@foreach ($result['boardColumns'] as $key => $column)
    @if ($column->userSetting && $column->userSetting->collapsed)
        <!-- MINIMIZED BOARD PANEL START -->
        <div class="minimized rounded bg-additional-grey border-grey mr-3">
            <!-- TASK BOARD HEADER START -->
            <div class="d-flex mt-4 mx-1 b-p-header align-items-center">
                <a href="javascript:;" class="d-grid f-8 mb-3 text-lightest collapse-column"
                    data-column-id="{{ $column->id }}" data-status="{{ $column->slug }}" data-type="maximize"
                    data-toggle="tooltip" data-original-title=@lang('app.expand')>
                    <i class="fa fa-chevron-right ml-1"></i>
                    <i class="fa fa-chevron-left"></i>
                </a>

                <p class="mb-3 mx-0 f-15 text-dark-grey font-weight-bold"><i class="fa fa-circle mb-2 text-red"
                        style="color: {{ $column->label_color }}"></i>{{ $column->slug == 'completed' || $column->slug == 'incomplete' ? __('app.' . $column->slug) : $column->column_name }}
                </p>

                <span class="b-p-badge bg-grey f-13 px-2 py-2 text-lightest font-weight-bold rounded d-inline-block"
                    id="task-column-count-{{ $column->id }}">{{ $column->tasks_count }}</span>

            </div>
            <!-- TASK BOARD HEADER END -->

        </div>
        <!-- MINIMIZED BOARD PANEL END -->
    @else
        <!-- BOARD PANEL START -->
        <div class="board-panel rounded bg-additional-grey border-grey mr-3">
            <!-- HEADER -->
            <div class="d-flex m-3 b-p-header">
                <p class="mb-0 f-15 mr-3 text-dark-grey font-weight-bold"><i class="fa fa-circle mr-2 text-yellow"
                        style="color: {{ $column->label_color }}"></i>
                    <span
                        @if (strlen($column->column_name) > 20) data-toggle="tooltip" data-original-title="{{ $column->column_name }}" @endif>
                        {{ str_limit($column->column_name, 20, '...') }}
                    </span>
                </p>

                <span class="b-p-badge bg-grey f-13 px-2 text-lightest font-weight-bold rounded d-inline-block"
                    id="task-column-count-{{ $column->id }}">{{ $column->tasks_count }}</span>

                <span class="ml-auto d-flex align-items-center">
                    <a href="javascript:;" class="d-flex f-8 text-lightest collapse-column"
                        data-column-id="{{ $column->id }}" data-status="{{ $column->slug }}" data-type="minimize"
                        data-column-status="{{ $column->column_name }}" data-toggle="tooltip"
                        data-original-title=@lang('app.collapse')>
                        <i class="fa fa-chevron-right mr-1"></i>
                        <i class="fa fa-chevron-left"></i>
                    </a>

                    @if ($addTaskPermission == 'all' || $addTaskPermission == 'added' || $addStatusPermission == 'all')
                        <div class="dropdown">
                            <button
                                class="btn bg-white btn-lg f-10 px-2 py-1 text-dark-grey  rounded  dropdown-toggle ml-3"
                                type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <i class="fa fa-ellipsis-h"></i>
                            </button>

                            <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                aria-labelledby="dropdownMenuLink" tabindex="0">

                                @if (($addTaskPermission == 'all' || $addTaskPermission == 'added') && $column->slug != 'waiting_approval')
                                    <a class="dropdown-item openRightModal"
                                        href="{{ route('tasks.create') }}?column_id={{ $column->id }}">@lang('app.addTask')
                                    </a>
                                @endif

                                @if ($addStatusPermission == 'all')
                                    <hr class="my-1">
                                    <a class="dropdown-item edit-column" data-column-id="{{ $column->id }}"
                                        data-status="{{ $column->slug }}" href="javascript:;">@lang('app.edit')</a>
                                @endif

                                @if (
                                    $column->slug != 'completed' &&
                                        $column->slug != 'waiting_approval' &&
                                        $column->slug != 'incomplete' &&
                                        company()->default_task_status != $column->id &&
                                        $boardDelete &&
                                        $addStatusPermission == 'all')
                                    <a class="dropdown-item delete-column" data-column-id="{{ $column->id }}"
                                        data-status="{{ $column->slug }}" href="javascript:;">@lang('app.delete')</a>
                                @endif
                            </div>
                        </div>
                    @endif
                </span>
            </div>
            <!-- /HEADER -->

            <!-- BODY -->
            <div class="b-p-body">
                <div class="b-p-tasks" id="drag-container-{{ $column->id }}" data-column-id="{{ $column->id }}"
                    data-status="{{ $column->slug }}">
                    @if ($column->slug == 'waiting_approval')
                        <div
                            class="card rounded bg-white border-grey b-shadow-4 m-1 mb-3 no-task-card move-disable {{ $column->tasks_count > 0 ? 'd-none' : '' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-center py-3">
                                    <div class="align-items-center d-flex flex-column text-lightest w-100">
                                        <i class="fa fa-tasks f-15 w-100"></i>
                                        <div class="f-15 mt-4">
                                            - @lang('messages.noRecordFound') -
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- div end -->
                    @endif

                    @foreach ($column['tasks'] as $task)
                        @php
                            $taskUsers = $task->users ? $task->users->pluck('id')->toArray() : [];
                        @endphp

                        <x-cards.task-card :draggable="$changeStatusPermission == 'all' ||
                        ($changeStatusPermission == 'added' && $task->added_by == user()->id) ||
                        ($changeStatusPermission == 'owned' && in_array(user()->id, $taskUsers)) ||
                        ($changeStatusPermission == 'both' &&
                            (in_array(user()->id, $taskUsers) || $task->added_by == user()->id)) ||
                        ($task->project && $task->project->project_admin == user()->id)
                            ? 'true'
                            : 'false'" :task="$task" :company="$company" />
                    @endforeach

                    {{-- QUICK ADD (ONLY ONCE) - placed just BEFORE the ADD TASK trigger so new tasks appear above the add card --}}
                    @if (($addTaskPermission == 'all' || $addTaskPermission == 'added') && $column->slug != 'waiting_approval')
                        <div class="quick-add-task-container m-1 mb-3 move-disable" id="quick-add-container-{{ $column->id }}"
                            style="display:none;">
                            <div class="card rounded bg-white border-grey b-shadow-4 move-disable">
                                <div class="card-body p-2">
                                    <form class="quick-add-task-form" data-column-id="{{ $column->id }}">
                                        <input type="text" class="form-control quick-task-input"
                                            placeholder="@lang('app.enterTaskTitle')" data-column-id="{{ $column->id }}"
                                            autocomplete="off">
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <small class="text-muted">Press Enter to add, Escape to cancel</small>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ADD TASK TRIGGER (kept at bottom of column tasks) --}}
                    @if (($addTaskPermission == 'all' || $addTaskPermission == 'added') && $column->slug != 'waiting_approval')
                        <div class="add-task-trigger-container m-1 mb-3 move-disable" id="add-task-trigger-{{ $column->id }}">
                            <div class="card rounded bg-white border-grey b-shadow-4"
                                style="border: 2px dashed #e0e0e0;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-center">
                                        <a href="javascript:;" class="text-dark-grey quick-add-trigger"
                                            data-column-id="{{ $column->id }}">
                                            <i class="fa fa-plus mr-2"></i>@lang('app.add') @lang('app.task')
                                        </a>
                                    </div>
                                    <div class="d-flex justify-content-center mt-1">
                                        <small class="text-muted">or
                                            @if (isset($project))
                                                <a href="{{ route('tasks.create') }}?column_id={{ $column->id }}&task_project_id={{ $project->id }}"
                                                    class="openRightModal text-muted">advanced options</a>
                                            @else
                                                <a href="{{ route('tasks.create') }}?column_id={{ $column->id }}"
                                                    class="openRightModal text-muted">advanced options</a>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div> {{-- end b-p-tasks --}}

                @if ($column->tasks_count > count($column['tasks']))
                    <div class="d-flex m-3 justify-content-center">
                        <a class="f-13 text-dark-grey f-w-500 load-more-tasks" data-column-id="{{ $column->id }}"
                            data-total-tasks="{{ $column->tasks_count }}" data-status="{{ $column->status }}"
                            href="javascript:;">@lang('modules.tasks.loadMore')</a>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endforeach

<!-- Drag and Drop Plugin -->
<script>
    var arraylike = document.getElementsByClassName('b-p-tasks');
    var containers = Array.prototype.slice.call(arraylike);
    var drake = dragula({
            containers: containers,
            moves: function(el, source, handle, sibling) {
                if (el.classList.contains('move-disable') || !KTUtil.isDesktopDevice()) {
                    return false;
                }
                return true;
            },
        })
        .on('drag', function(el) {
            el.className = el.className.replace('ex-moved', '');
        }).on('drop', function(el) {
            el.className += ' ex-moved';
        }).on('over', function(el, container) {
            container.className += ' ex-over';
        }).on('out', function(el, container) {
            container.className = container.className.replace('ex-over', '');
        });
</script>

<script>
    drake.on('drop', function(element, target, source, sibling) {
        // unchanged drag/drop logic (kept as in original)
        var elementId = element.id;

        $children = $('#' + target.id).children();
        var boardColumnId = $('#' + target.id).data('column-id');
        var movingTaskId = $('#' + element.id).data('task-id');

        var sourceBoardColumnId = $('#' + source.id).data('column-id');
        var sourceColumnCount = parseInt($('#task-column-count-' + sourceBoardColumnId).text());
        var targetColumnCount = parseInt($('#task-column-count-' + boardColumnId).text());
        var targetBoardColumnStatus = $('#' + target.id).data('column-status');

        var taskIds = [];
        var prioritys = [];
        var sourceStatus = $('#' + source.id).data('status');
        var targetStatus = $('#' + target.id).data('status');

        $children.each(function(ind, el) {
            taskIds.push($(el).data('task-id'));
            prioritys.push($(el).index());
        });

        var role = "{{ $userRole }}";
        var needApproval = $('#' + element.id).data('need-approval');

        if ((sourceStatus == 'waiting_approval') || (targetStatus == 'waiting_approval')) {
            drake.cancel(true);
            Swal.fire({
                title: "@lang('messages.youCannotMoveTask')",
                icon: 'warning',
                confirmButtonText: "@lang('app.ok')",
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
            return;
        } else if (targetStatus == 'completed' && role == 'no' && needApproval == 1) {
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.approvalmsgsent')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('app.yes')",
                cancelButtonText: "@lang('app.no')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('tasks.send_approval', ':id') }}";
                    url = url.replace(':id', movingTaskId);

                    var token = "{{ csrf_token() }}";
                    var isApproval = 1;
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            taskId: movingTaskId,
                            isApproval: isApproval,
                            '_method': 'POST'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                window.location.reload();
                            }
                        }
                    });
                } else {
                    window.location.reload();
                }
            });
        } else {
            $.easyAjax({
                url: "{{ route('taskboards.update_index') }}",
                type: 'POST',
                container: '#taskboard-columns',
                blockUI: true,
                data: {
                    boardColumnId: boardColumnId,
                    movingTaskId: movingTaskId,
                    taskIds: taskIds,
                    prioritys: prioritys,
                    '_token': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status == 'failed') {
                        Swal.fire({
                            title: "@lang('messages.sweetAlertTitle')",
                            text: "@lang('messages.You cant ')",
                            icon: 'warning',
                            confirmButtonText: "@lang('app.okay')",
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            showClass: {
                                popup: 'swal2-noanimation',
                                backdrop: 'swal2-noanimation'
                            },
                            buttonsStyling: false
                        });
                    }
                    if ($('#' + source.id + ' .task-card').length == 0) {
                        $('#' + source.id + ' .no-task-card').removeClass('d-none');
                    }
                    if ($('#' + target.id + ' .task-card').length > 0) {
                        $('#' + target.id + ' .no-task-card').addClass('d-none');
                    }

                    $('#task-column-count-' + sourceBoardColumnId).text(sourceColumnCount - 1);
                    $('#task-column-count-' + boardColumnId).text(targetColumnCount + 1);
                }
            });
        }

    });
    $(document).ready(function() {
        $(document).on('click', '.quick-add-trigger', function(e) {
            e.preventDefault();
            var columnId = $(this).data('column-id');

            // hide all others and clear their values
            $('.quick-add-task-container').hide();
            $('.quick-task-input').val('');

            // show the quick add for this column and focus
            var container = $('#quick-add-container-' + columnId);
            container.show();
            container.find('.quick-task-input').focus();
        });

        // Handle form submit (Enter)
        $(document).on('submit', '.quick-add-task-form', function(e) {
            e.preventDefault();
            var form = $(this);
            var columnId = form.data('column-id');
            var input = form.find('.quick-task-input');
            var taskTitle = input.val().trim();

            if (!taskTitle) {
                console.log('Task title is empty.');
                return;
            }

            addQuickTask(columnId, taskTitle, input, form);
        });

        // Keyboard shortcuts
        $(document).on('keydown', '.quick-task-input', function(e) {
            var columnId = $(this).data('column-id');

            if (e.key === 'Escape') {
                hideQuickAdd(columnId);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                $(this).closest('form').submit();
            }
        });
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.quick-add-task-container, .quick-add-trigger').length) {
                $('.quick-add-task-container').hide();
                $('.quick-task-input').val('');
            }
        });

        function hideQuickAdd(columnId) {
            $('#quick-add-container-' + columnId).hide();
            $('#quick-add-container-' + columnId + ' .quick-task-input').val('');
        }

        function addQuickTask(columnId, taskTitle, input, form) {
            input.prop('disabled', true);

            var projectId = '';
            @if (isset($project))
                projectId = '{{ $project->id }}';
            @endif

            $.easyAjax({
                url: "{{ route('tasks.quick_store') }}",
                type: 'POST',
                data: {
                    'heading': taskTitle,
                    'board_column_id': columnId,
                    'project_id': projectId,
                    '_token': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        input.val('').focus();
                        var countElement = $('#task-column-count-' + columnId);
                        var currentCount = parseInt(countElement.text()) || 0;
                        countElement.text(currentCount + 1);
                        $('#drag-container-' + columnId + ' .no-task-card').addClass('d-none');
                        $('#quick-add-container-' + columnId).before(response.view);
                    } else {
                        console.log('Failed to add task:', response.message || 'Unknown error');
                    }
                },

                error: function(xhr, status, error) {
                    console.log('An error occurred while adding the task:', error || status);
                },
                complete: function() {
                    input.prop('disabled', false);
                }
            });
        }
    });
</script>
