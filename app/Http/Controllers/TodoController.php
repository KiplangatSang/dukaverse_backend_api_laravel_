<?php
namespace App\Http\Controllers;

use App\Models\Todo;

class TodoController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index($type = Todo::ACCOUNT_TODO_TYPES)
    {
        //

        if ($type == Todo::ACCOUNT_TODO_TYPES) {
            $todos          = $this->account()->todos;
            $createdTodos   = $this->account()->createdTodos;
            $assigneedTodos = $this->account()->assigneedTodos;

            return $this->sendResponse([
                "todos"          => $todos,
                "createdTodos"   => $createdTodos,
                "assigneedTodos" => $assigneedTodos,
            ],
                "success, The user todos have been fetched successfully.");

        } else if ($type == Todo::USER_TODO_TYPES) {
            $todos          = $this->user()->todos;
            $createdTodos   = $this->user()->createdTodos;
            $assigneedTodos = $this->user()->assigneedTodos;
            return $this->sendResponse([
                "todos"          => $todos,
                "createdTodos"   => $createdTodos,
                "assigneedTodos" => $assigneedTodos,
            ],
                "success, The user todos have been fetched successfully.");

        } else {
            return $this->sendError("Bad request", ["error" => "The sale settings could not be fetched.", "result" => false]);

        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

        $todo_types = Todo::TODO_TYPES;

        if (! $todo_types) {
            return $this->sendError("Bad request", ["error" => "The todo defaults could not be fetched.", "result" => $todo_types]);
        }

        return $this->sendResponse(["todo_types" => $todo_types], "success, The todo data has been fetched successfully.");

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $type = Todo::ACCOUNT_TODO_TYPES)
    {
        //

        $account = $this->user();
        if ($type == Todo::ACCOUNT_TODO_TYPES) {
            $account = $this->account();
        } else if ($type == Todo::USER_TODO_TYPES) {
            $account = $this->user();
        }

        $validator = Validator::make($request->all(),
            [
                "todo"        => ["required", "string"],
                "note"        => ["sometimes", "required", "string"],
                "project_id"  => ["sometimes", "integer", "exists:projects:id"],
                "assigned_to" => ["sometimes", "integer", "exists:users:id"],

            ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The settings could not be saved .", "result" => $validator->errors()]);
        }

        $validated = $validator->validated();

        $todo = $account->todos()->create(
            [

                "todo"        => $validated['todo'],
                "note"        => $request->note,
                "project_id"  => $request->project_id,
                "user_id"     => auth()->id(),
                "assigned_to" => $request->assigned_to ? $request->assigned_to : auth()->id(),

            ]
        );

        if (! $todo) {
            return $this->sendError("Bad request", ["error" => "The  todo could not be saved.", "result" => $todo]);
        }

        $todo = Todo::where('id', $todo->id)->first();
        return $this->sendResponse(["todo" => $todo], "success, The todo has been saved successfully.");

    }

    /**
     * Display the specified resource.
     */
    public function show($todo, $type = Todo::ACCOUNT_TODO_TYPES)
    {
        //

        $account = $this->user();
        if ($type == Todo::ACCOUNT_TODO_TYPES) {
            $account = $this->account();
        } else if ($type == Todo::USER_TODO_TYPES) {
            $account = $this->user();
        }

        $todo = $account->todos()->where('id', $todo)->first();

        if (! $todo) {
            return $this->sendError("Bad request", ["error" => "The  todo could not be found.", "result" => $todo]);
        }

        return $this->sendResponse(["todo" => $todo], "success, The todo has been fetched successfully.");

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($todo, $type = Todo::ACCOUNT_TODO_TYPES)
    {
        //

        $account = $this->user();
        if ($type == Todo::ACCOUNT_TODO_TYPES) {
            $account = $this->account();
        } else if ($type == Todo::USER_TODO_TYPES) {
            $account = $this->user();
        }

        $todo = $account->todos()->where('id', $todo)->first();

        if (! $todo) {
            return $this->sendError("Bad request", ["error" => "The  todo could not be found.", "result" => $todo]);
        }
        $todo_types = Todo::TODO_TYPES;

        if (! $todo_types) {
            return $this->sendError("Bad request", ["error" => "The todo defaults could not be fetched.", "result" => $todo_types]);
        }

        return $this->sendResponse(["todo_types" => $todo_types, "todo" => $todo], "success, The todo data has been fetched successfully.");

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $todo, $type = Todo::ACCOUNT_TODO_TYPES)
    {
        //
        $account = $this->user();
        if ($type == Todo::ACCOUNT_TODO_TYPES) {
            $account = $this->account();
        } else if ($type == Todo::USER_TODO_TYPES) {
            $account = $this->user();
        }

        $todo = Todo::where('id', $todo)->first();

        if (! $todo) {
            return $this->sendError("Bad request", ["error" => "The  todo could not be found.", "result" => $todo]);
        }

        $validator = Validator::make($request->all(),
            [
                "todo"        => ["sometimes", "string"],
                "note"        => ["sometimes", "string"],
                "project_id"  => ["sometimes", "integer", "exists:projects:id"],
                "assigned_to" => ["sometimes", "integer", "exists:users:id"],
                "done"        => ["sometimes", "boolean"],
                "archived"    => ["sometimes", "boolean"],

            ]);

        if ($validator->fails()) {
            return $this->sendError("Bad request", ["error" => "The settings could not be saved .", "result" => $validator->errors()]);
        }

        $validated = $validator->validated();

        $result = $todo->update(
            $request->all(),
        );

        if ($request->done) {
            $result = $todo->update(
                [
                    "done" => $request->done == "1" ? true : false,
                ]
            );

            if (! $result) {
                return $this->sendError("Bad request", ["error" => "The  todo could not be updated.", "result" => $result]);

            }
        }

        if (! $result) {
            return $this->sendError("Bad request", ["error" => "The  todo could not be updated.", "result" => $result]);
        }

        $todo = Todo::where('id', $todo->id)->first();

        return $this->sendResponse(["todo" => $todo], "success, The todo has been saved successfully.");

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($todo)
    {
        //
        $result = null;

        $result = Todo::destroy($todo);

        if (! $result) {
            return $this->sendError("Bad request", ["error" => "The  todo could not be  deleted.", "result" => $result]);
        }

        return $this->sendResponse(["result" => $result], "success, The todo has been deleted successfully.");

    }

    public function deleteAll(Request $request)
    {
        //
        $result = null;
        if ($request->all) {
            $assigneedTodos = $this->user()->assigneedTodos;
            foreach ($assigneedTodos as $assigneedTodo) {
                $result = Todo::where('id', $assigneedTodo->id)->delete();
            }

            if (! $result) {
                return $this->sendError("Bad request", ["error" => "The  todos could not be  deleted.", "result" => $result]);
            }

            return $this->sendResponse(["result" => $result], "success, The todos have been deleted successfully.");

        } else {
            return $this->sendError("Bad request", ["error" => "The  todo could not be  deleted.", "result" => $result]);
        }

    }
}
