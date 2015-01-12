<?php

use Phragile\TaskList;
use Phragile\BurndownChart;

class SprintsController extends BaseController {

	public function show(Sprint $sprint)
	{
		$phabricator = App::make('phabricator');
		$currentSprint = $sprint->project->currentSprint();
		$taskList = new TaskList($phabricator->queryTasksByProject($sprint->phid));
		$burndown = new BurndownChart($sprint, $taskList, $phabricator->getTaskTransactions($taskList->getClosedTaskIDs()));

		return View::make('sprint.view', compact('sprint', 'currentSprint', 'taskList', 'burndown'));
	}

	public function create(Project $project)
	{
		if (!Auth::user()->certificateValid())
		{
			Flash::warning('Please set a valid Conduit certificate before trying to create a new sprint.');
			return Redirect::back();
		}

		return View::make('sprint.create', compact('project'));
	}

	public function store(Project $project)
	{
		$sprint = new Sprint(array_merge(
			array_map('trim', Input::all()),
			['project_id' => $project->id]
		));

		$validation = $sprint->validate();
		if ($validation->fails())
		{
			Flash::error(HTML::ul($validation->messages()->all()));
			return Redirect::back();
		}

		if (!$sprint->save())
		{
			Flash::error($sprint->getPhabricatorError() ?: 'A problem occurred saving the sprint record in Phragile.');
			return Redirect::back();
		}

		Flash::success("Successfully created \"$sprint->title\"");
		return Redirect::route('sprint_path', ['sprint' => $sprint->phabricator_id]);
	}
}
