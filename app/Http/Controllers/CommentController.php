<?php

namespace App\Http\Controllers;

use stdClass;
use App\Models\Comment;
use App\Events\UserVoted;
use App\Models\Evaluation;
use App\Events\CommentAdded;
use Illuminate\Http\Request;
use App\Rules\ValidCommentId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class CommentController extends Controller
{
    public function ListComments()
    {
        return view('comments', ['count' => Comment::count()]);
    }

    public function ListMoreComments(Request $request)
    {
        $fields = $request->validate([
            'page' => 'required|integer|gt:0',
            'commentsPerPage' => 'required|integer|gt:0',
            'created' => 'required|integer|gte:0'
        ], [
            'page.required' => 'Page number is required',
            'page.integer' => 'Page number must be an integer',
            'page.gte' => 'Page number must be greater than zero',
            'commentsPerPage.required' => 'The number of comments in a page is required',
            'commentsPerPage.integer' => 'The number of comments in a page must be an integer',
            'commentsPerPage.gt' => 'The number of comments in a page must be greater than zero',
            'created.required' => 'The number of created comments is required',
            'created.integer' => 'The number of created comments must be an integer',
            'created.gte' => 'The number of created comments must be greater than or equal to zero'
        ]);
        $offset = ($fields['page'] - 1) * $fields['commentsPerPage'] + $fields['created'];
        $count = Comment::count();
        if($offset >= $count) return response()->json(['errors' => ['offset' => ['Offset cannot be greater than or equal to the count of existing comments']]], 422);
        $comments = Comment::select('comments.id', 'comments.comment', 'users.email', DB::raw('SUM(IF(IFNULL(evaluations.votedUp,FALSE), 1, 0)) as votedUp'), DB::raw('SUM(IF(IFNULL(evaluations.votedUp,TRUE), 0, 1)) as votedDown'))
        ->join('users', 'users.id', '=', 'comments.user_id')->leftjoin('evaluations', 'comments.id', '=', 'evaluations.comment_id')
        ->groupBy('comments.id', 'comments.comment', 'users.email')
        ->orderBy('comments.id', 'desc')->skip($offset)->take($offset + $fields['commentsPerPage'] <= $count ? $fields['commentsPerPage'] : $count - $offset)->get();
        $ids = $comments->pluck('id');
        $hasVoted = Evaluation::select('evaluations.votedUp', 'evaluations.comment_id')->where('evaluations.user_id', '=', auth()->user()->id)->whereIn('evaluations.comment_id', $ids)->get()->keyBy('comment_id')->all();
        $views = [];
        foreach($comments as $comment)
        {
            $view = View::make('comment', ['comment' => $comment, 'hasVoted' => $hasVoted])->render();
            array_push($views, $view);
        }
        return response()->json(['htmls' => $views]);
    }

    public function AddComment(Request $request)
    {
        $comment = strip_tags($request->get('commentArea'));
        $request->merge(['commentArea' => empty($comment) && $comment != '0' ? null : $comment]);
        $fields = $request->validate([
            'commentArea' => 'required|string|max:65535'
        ], [
            'commentArea.required' => 'The comment field cannot be empty',
            'commentArea.max' => 'The comment is too long',
        ]);
        $comment = Comment::create(['comment' => $fields['commentArea'], 'user_id' => auth()->user()->id]);
        $info = new stdClass();
        $info->id = $comment->id;
        $info->comment = $comment->comment;
        $info->email = auth()->user()->email;
        $info->votedUp = 0;
        $info->votedDown = 0;
        $view = View::make('comment', ['comment' => $info, 'hasVoted' => []])->render();
        broadcast(new CommentAdded($view))->toOthers();
        return response()->json(['html' => $view]);
    }

    public function Vote(Request $request)
    {
        $fields = $request->validate([
            'id' => ['required', 'integer', 'gte:1', new ValidCommentId()],
            'voteUp' => 'required|boolean'
        ], [
            'id.*' => 'Bad comment identifier',
            'voteUp.*' => 'Invalid vote format'
        ]);
        $eval = Evaluation::where('user_id', '=', auth()->user()->id)->where('comment_id', '=', $fields['id'])->first();
        if(is_null($eval)) 
        {
            Evaluation::create(['user_id' => auth()->user()->id, 'comment_id' => $fields['id'], 'votedUp' => $fields['voteUp'] ? 1 : 0]);
            broadcast(new UserVoted($fields["id"], false, false, $fields["voteUp"], auth()->user()->id))->toOthers();
            return response()->json(['decrementsThis' => false, 'decrementsOther' => false]);
        }
        else if($eval->votedUp != $fields['voteUp'] ? 1 : 0)
        {
            $eval->update(['votedUp' => $fields['voteUp'] ? 1 : 0]);
            broadcast(new UserVoted($fields["id"], false, true, $fields["voteUp"], auth()->user()->id))->toOthers();
            return response()->json(['decrementsThis' => false, 'decrementsOther' => true]);
        }
        else
        {
            $eval->delete();
            broadcast(new UserVoted($fields["id"], true, false, $fields["voteUp"], auth()->user()->id))->toOthers();
            return response()->json(['decrementsThis' => true, 'decrementsOther' => false]);
        }
    }
}
