@if (in_array('projects', $activeWidgets) &&
        $sidebarUserPermissions['view_projects'] != 5 &&
        $sidebarUserPermissions['view_projects'] != 'none' &&
        in_array('projects', user_modules()))
    {{-- In Progress Projects --}}
    <div class="col-xl-6 col-lg-6 col-md-6 mb-3">
        <a href="{{ route('projects.index') . '?assignee=me&status=in progress' }}">
            <x-cards.widget title="Projects In Progress" :value="$totalProjects" icon="layer-group">
            </x-cards.widget>
        </a>
    </div>

    {{-- Overdue Projects --}}
    <div class="col-xl-6 col-lg-6 col-md-6 mb-3">
        <a href="{{ route('projects.index') . '?assignee=me&status=overdue' }}">
            <x-cards.widget title="Overdue Projects" :value="$dueProjects" icon="layer-group">
            </x-cards.widget>
        </a>
    </div>
@endif
