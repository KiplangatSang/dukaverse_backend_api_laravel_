<?php
namespace App\Http\Controllers;

class TaskDependancyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        $taskDependencies = $this->account()->taskDependencies()
            ->with('task')
            ->with('dependedTask')
            ->with('project')
            ->get();

        if (! $taskDependencies) {
            return $this->sendError("Bad request", ["error" => "The task  dependencies could not be fetched.", "result" => $taskDependencies]);
        }

        return $this->sendResponse(["taskDependencies" => $taskDependencies], "success, The task  dependencies has been fetched successfully.");

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

        $dependencies = TaskDependency::TASK_DEPENDENCIES;

        if (! $dependencies) {
            return $this->sendError("Bad request", ["error" => "The task  dependencies could not be fetched.", "result" => $dependencies]);
        }

        return $this->sendResponse(["dependencies" => $dependencies], "success, The task  dependencies has been fetched successfully.");

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskDependencyRequest $request)
    {
        //

        $validator = Validator::make($request->all(),
            [
                "project_id"      => ["required"],
                "task_id"         => ["required"],
                "depends_on"      => ["required"],
                "dependency_type" => ["required"],
            ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The settings could not be saved .", "result" => $validator->errors()]);
        }

        $validatated = $validator->validated();

        $taskDependencies = $this->account()->taskDependencies()->create(
            [
                "user_id"         => $this->user()->id,
                "project_id"      => $validatated["project_id"],
                "task_id"         => $validatated["task_id"],
                "depends_on"      => $validatated["depends_on"],
                "dependency_type" => $validatated["dependency_type"],
            ]
        );

        if (! $taskDependencies) {
            return $this->sendError("Bad request", ["error" => "The   task dependencies could not be saved.", "result" => $taskDependencies]);
        }

        return $this->sendResponse(["taskDependencies" => $taskDependencies], "success, The task dependencies  has been saved successfully.");

    }

    /**
     * Display the specified resource.
     */
    public function show($taskDependency)
    {
        //

        $taskDependency = $this->account()->taskDependencies()
            ->with('task')
            ->with('dependedTask')
            ->with('project')
            ->where('id', $taskDependency)
            ->first();

        if (! $taskDependency) {
            return $this->sendError("Bad request", ["error" => "The task  dependency could not be fetched.", "result" => $taskDependency]);
        }

        return $this->sendResponse(["taskDependency" => $taskDependency], "success, The task dependency has been fetched successfully.");

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($taskDependency)
    {
        //

        $taskDependency = $this->account()->taskDependencies()
            ->with('task')
            ->with('dependedTask')
            ->with('project')
            ->where('id', $taskDependency)
            ->first();

        if (! $taskDependency) {
            return $this->sendError("Bad request", ["error" => "The task  dependencies could not be fetched.", "result" => $taskDependency]);
        }

        $dependencies = TaskDependency::TASK_DEPENDENCIES;

        if (! $dependencies) {
            return $this->sendError("Bad request", ["error" => "The task  dependencies could not be fetched.", "result" => $dependencies]);
        }

        return $this->sendResponse(["taskDependency" => $taskDependency, "dependencies" => $dependencies], "success, The task  dependencies has been fetched successfully.");

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskDependencyRequest $request, TaskDependency $taskDependency)
    {
        //

        $taskDependency = $this->account()->taskDependencies()
            ->with('task')
            ->with('dependedTask')
            ->with('project')
            ->where('id', $taskDependency)
            ->first();

        if (! $taskDependency) {
            return $this->sendError("Bad request", ["error" => "The task  dependencies could not be fetched.", "result" => $taskDependency]);
        }

        $validator = Validator::make($request->all(),
            [
                "project_id"      => ["required"],
                "task_id"         => ["required"],
                "depends_on"      => ["required"],
                "dependency_type" => ["required"],
            ]);

        $validatated = $validator->validated();

        $taskDependencies = $this->account()->taskDependencies()->create(
            [
                "user_id"         => $this->user()->id,
                "project_id"      => $validatated["project_id"],
                "task_id"         => $validatated["task_id"],
                "depends_on"      => $validatated["depends_on"],
                "dependency_type" => $validatated["dependency_type"],
            ]
        );

        if (! $taskDependencies) {
            return $this->sendError("Bad request", ["error" => "The   task dependencies could not be updated.", "result" => $taskDependencies]);
        }

        return $this->sendResponse(["taskDependencies" => $taskDependencies], "success, The task dependencies  has been updated successfully.");

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($taskDependency)
    {
        //

        $result = TaskDependency::destroy($taskDependency);

        if (! $result) {
            return $this->sendError("Bad request", ["error" => "The   task dependencies could not be deleted.", "result" => $result]);
        }

        return $this->sendResponse(["result" => $result], "success, The task dependencies  has been deleted successfully.");

    }
}
