<?php

namespace Socieboy\Forum\Controllers;

use App\Http\Controllers\Controller;
use Socieboy\Forum\Entities\Conversations\Conversation;
use Socieboy\Forum\Entities\Conversations\ConversationRepo;
use Socieboy\Forum\Jobs\StartConversationJob;
use Socieboy\Forum\Requests\ConversationRequest;


class ConversationController extends Controller
{

    /**
     * @var ConversationRepo
     */
    protected $conversationRepo;

    /**
     * @param ConversationRepo $conversationRepo
     */
    function __construct(ConversationRepo $conversationRepo)
    {
        $this->middleware('auth');

        $this->conversationRepo = $conversationRepo;

    }

    /**
     * Display a conversation and replies
     *
     * @param $slug
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        $conversation = $this->conversationRepo->where('slug', $slug)->get()->first();

        $replies = $conversation->replies()->orderBy('created_at', 'ASC')->paginate(15);

        return view('Forum::Conversations.show', compact('conversation', 'replies'));
    }

    /**
     * Store the new conversation.
     *
     * @param ConversationRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ConversationRequest $request)
    {
        $job = new StartConversationJob(
                new Conversation(),
                $request->except('_token')
        );

        $job->handle();

        return redirect()->route('forum');
    }

}