<?php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Retail;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Messages",
 *     description="API Endpoints for managing messages"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class MessageController extends BaseController
{

    /**
     * @OA\Get(
     *     path="/api/v1/messages",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get all messages",
     *     description="Fetch all messages for the authenticated retail account",
     *     @OA\Response(
     *         response=200,
     *         description="Success, list of messages",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="messages",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="sender_id", type="integer", example=10),
     *                     @OA\Property(property="recipient_id", type="integer", example=20),
     *                     @OA\Property(property="content", type="string", example="Hello there!"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-21T14:30:00Z")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function index()
    {
        $messages                 = $this->retail()->messages()->latest()->get();
        $messagesdata['messages'] = $messages;
        return $this->sendResponse($messagesdata, 'success, messages');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/messages/create",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get message creation data",
     *     description="Fetch messages for message creation form",
     *     @OA\Response(
     *         response=200,
     *         description="Success, message data"
     *     )
     * )
     */
    public function create()
    {
        $message = $this->retail()->messages()->get();
        return $this->sendResponse($message, 'success, message');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/messages",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     summary="Send a new message",
     *     description="Store a newly created message",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string", example="Hello!"),
     *             @OA\Property(property="title", type="string", example="Message title")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Success, message sent"),
     *     @OA\Response(response=400, description="Error, message not sent")
     * )
     */
    public function store(Request $request)
    {
        $request['sender_id'] = $this->user()->id;
        $request['retail_id'] = $this->retail()->id;
        $result               = $this->retail()->messages()->create($request->all());

        if (! $result) {
            return $this->sendError('Error!', 'Message  not sent.');
        }

        $result['text'] = $result->message;
        $this->sendNotification($result);

        return $this->sendResponse($result, 'Success! Message sent. Awaiting response');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/messages/{id}",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     summary="Show a specific message",
     *     description="Fetch a message by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Message ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success, message found"),
     *     @OA\Response(response=404, description="Message not found")
     * )
     */
    public function show(Message $message)
    {
        $message = Message::where('id', $message->id)
            ->with('retail')
            ->first();
        return $this->sendResponse($message, 'Success! Message sent. Await response');
    }

    public function edit(Message $message)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/api/v1/messages/{id}",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update a message (add reply)",
     *     description="Update a message by adding a reply",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Message ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string", example="Reply content")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Success, reply added"),
     *     @OA\Response(response=400, description="Error, message cannot be updated")
     * )
     */
    public function update(Request $request, Message $message)
    {
        $response = [
            "key"     => "retail",
            "message" => $request->message,
            "time"    => now(),
        ];
        $message = $this->retail()->messages()->where('id', $message->id)->first();

        $replies = [];
        if ($message->replies) {
            $replies = json_decode($message->replies);
        }

        array_push($replies, $response);
        $replies           = json_encode($replies);
        $update['replies'] = $replies;

        if (! $message->messageable->first() instanceof (Retail::class)) {
            $update['replyable_id']   = $this->retail()->id;
            $update['replyable_type'] = Retail::class;
        }

        $result = $message->update(['replies' => $replies]);

        $message['text'] = $request->text;
        $this->sendNotification($message);

        if (! $result) {
            return $this->sendError('error', 'Message cannot be sent');
        }

        return $this->sendResponse($message, 'success! Message sent successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/messages/{id}",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete a message",
     *     description="Delete a message by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Message ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success, message deleted"),
     *     @OA\Response(response=404, description="Message not found")
     * )
     */
    public function destroy(Message $message)
    {
        $result = Message::destroy($message->id);
        if (! $result) {
            return $this->sendError('error', 'This item could not be deleted');
        }

        return $this->sendResponse('success', 'This item has been deleted successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/messages/tenant",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     summary="Send message to tenant",
     *     description="Create or update a message for a tenant",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"retail_id","message"},
     *             @OA\Property(property="retail_id", type="integer", example=5),
     *             @OA\Property(property="message", type="string", example="Tenant message content"),
     *             @OA\Property(property="title", type="string", example="Tenant message title")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Success, message sent")
     * )
     */
    public function messageTenant(Request $request)
    {
        $message = $this->retail()->message()->where('retail_id', $request->retail_id)
            ->first();

        if (! $message) {
            $message = $this->retail()->createMessage()->updateOrCreate(
                [
                    "retail_id" => $request->retail_id,
                    "title"     => $request->title,
                ],
                [
                    "message"         => $request->message,
                    'sender_id'       => $this->user()->id,
                    'user_id'         => $this->user()->id,
                    'retail_owner_id' => $this->retail()->id,
                    "reply_id"        => $request->retail_owner_id,
                ]
            );
        }

        return $this->sendResponse($message, 'Success, message sent successfully');
    }

    public function sendNotification($message)
    {
        if ($message->retail) {
            $this->notifyRetail($message->retail_id, $message);
        } else {
            $this->notifyRetails($message);
        }

        return true;
    }

    public function notifyRetail($retail_id, $message)
    {
        $retail = Retail::where('id', $retail_id)->first();
        // $retail->user->notify(new RetailNotification($message));
        return true;
    }

    public function notifyRetails($message)
    {
        $retails = Retail::with('user')->get();
        foreach ($retails as $retail) {
            // $retail->notify(new RetailNotification($message));
        }
        return true;
    }
}
