@extends('layouts.default')

@section('title', 'Phragile - ' . (isset($snapshot) ? "Snapshot of {$sprint->title}" : $sprint->title))

@section('content')
	@include('project.partials.settings_form')
	@include('sprint.partials.settings_form')

	<h1 class="sprint-overview-title">
		{{ $sprint->project->title }}
		{{ isset($snapshot) ? "Snapshot $snapshot->created_at" : 'Sprint Overview' }}
		<span class="dropdown">
			<button class="btn btn-lg dropdown-toggle" type="button" data-toggle="dropdown">
				{{ $sprint->title }}
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
				@foreach($sprint->project->sprints as $s)
					<li class="{{ $s->id === $sprint->id ? 'active' : '' }}">
						<a href="{{ route('sprint_path', $s->phabricator_id) }}">
							@if($currentSprint && $currentSprint->id === $s->id && $currentSprint->isActive())
								Current sprint ({{ $s->title }})
							@else
								{{ $s->title }}
							@endif
						</a>
					</li>
				@endforeach
			</ul>
		</span>

		<a href="{{ $_ENV['PHABRICATOR_URL'] }}project/view/{{ $sprint->phabricator_id }}" class="btn btn-default" title="Go to Phabricator" target="_blank">
			<img src="/images/phabricator.png" class="phabricator-icon"/>
		</a>
	</h1>

	<div class="row">
		<div class="col-md-8">
			<ul class="nav nav-tabs" id="pick-chart">
				<li role="presentation" class="active" data-graphs="ideal burndown"><a href="#">Burndown chart</a></li>
				<li role="presentation" data-graphs="scope burnup"><a href="#">Burnup chart</a></li>
			</ul>
			<div id="burndown">
				<table class="table table-condensed" id="graph-labels">
					<tbody>
					</tbody>
				</table>
			</div>
			<div id="chart-data" class="hidden">{!! json_encode($burnChartData) !!}</div>
		</div>

		<div class="col-md-4">
			<div class="dropdown" id="snapshots">
				@if(!$sprint->sprintSnapshots->isEmpty())
					<button class="btn btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
						{!! isset($snapshot) ? "Snapshot <span id='snapshot-date'>$snapshot->created_at</span>" : 'Live version' !!}
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						<li class="{{ isset($snapshot) ? '' : 'active' }}">
							{!! link_to_route('sprint_live_path', 'Live version', ['sprint' => $sprint->phabricator_id]) !!}
						</li>

						@foreach($sprint->sprintSnapshots as $sprintSnapshot)
							<li class="{{ isset($snapshot) && $snapshot->id === $sprintSnapshot->id ? 'active' : '' }}">
								{!! link_to_route('snapshot_path', $sprintSnapshot->created_at, ['snapshot' => $sprintSnapshot->id]) !!}
							</li>
						@endforeach
					</ul>
				@endif

				@if(!isset($snapshot) && Auth::check())
					<a class="btn btn-default btn-sm" href="{{ route('create_snapshot_path', $sprint->phabricator_id) }}">
						Create snapshot
					</a>
				@endif

				@if(isset($snapshot) && Auth::check())
					{!! link_to_route(
						'delete_snapshot_path',
						'',
						['snapshot' => $snapshot->id],
						[
							'class' => 'btn btn-danger btn-sm glyphicon glyphicon-remove',
							'title' => 'Delete snapshot',
							'onclick' => 'return confirm("Delete this snapshot?")'
						]
					) !!}
				@endif
			</div>

			<table class="table status-table">
				@foreach($pieChartData as $status => $statusMeta)
					<tr class="{{ $status === 'total' ? 'reset-filter' : 'filter-backlog' }}" data-column="status" data-value="{{ $status === 'total' ? '' : $status }}">
						<th><span class="status-label {{ $statusMeta['cssClass'] }}">{{ $status }}</span></th>
						<td>{{ $statusMeta['tasks'] }} ({{ $statusMeta['points'] }} story points)</td>
					</tr>
				@endforeach
			</table>

			<div id="status-data" class="hidden">{{ json_encode(array_diff_key($pieChartData, ['total' => false])) }}</div>
			<div id="pie"></div>
		</div>
	</div>

	<button class="btn btn-default reset-filter" disabled="disabled">Show all tasks</button>
	<table id="backlog" class="table table-striped sprint-backlog">
		<thead>
			<tr>
				<th class="sort" data-sort="title">Title</th>
				<th class="sort" data-sort="priority">Priority</th>
				<th class="sort" data-sort="points">Story Points</th>
				<th class="sort" data-sort="assignee">Assignee</th>
				<th class="sort" data-sort="status">Status</th>
			</tr>
		</thead>

		<tbody class="list">
			@foreach($sprintBacklog as $task)
				<tr id="t{{ $task['id'] }}">
					<td>
						{!! link_to(
							$_ENV['PHABRICATOR_URL'] . 'T' . $task['id'],
							"#${task['id']} " . $task['title'],
							[
								'class' => 'title',
								'target' => '_blank'
							]
						) !!}
					</td>

					<?php $priorityValue = Config::get('phabricator.MANIPHEST_PRIORITIES')[strtolower($task['priority'])] ?>
					<td class="filter-backlog" data-column="priority" data-value="{{ $priorityValue }}">
						<span class="priority hidden">{{ $priorityValue }}</span>
						{{ $task['priority'] }}
					</td>
					<td class="points">{{ $task['story_points'] }}</td>

					<td class="assignee filter-backlog" data-column="assignee" data-value="{{ $task['assignee'] }}">
						{{ $task['assignee']}}
					</td>
					<td class="filter-backlog" data-column="status" data-value="{{ $task['status'] }}">
						<span class="status status-label {{ $task['cssClass'] }}">{{ $task['status'] }}</span>
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@stop

@section('optional_scripts')
	{!! HTML::script('js/sprint_overview.js') !!}
@stop
