<div class="card mt-2">
    <div id="comment-{{$comment->id}}" class="card-body">
        <div class="card-title fs-4 fw-bold">{{$comment->email}}</div>
        <hr>
        <p class="card-text">{{$comment->comment}}</p>
        <div class="d-flex align-items-center">
            <i id="voteUp-{{$comment->id}}" class="fa fa-solid fa-caret-up fa-2x {{array_key_exists($comment->id,$hasVoted) ? ($hasVoted[$comment->id]->votedUp == 1 ? "text-primary" : "") : ""}}" onclick="handleVoteUp(event)"></i>
            <span id="votedUp-{{$comment->id}}" class="ms-2 me-2 {{array_key_exists($comment->id,$hasVoted) ? ($hasVoted[$comment->id]->votedUp == 1 ? "text-primary" : "") : ""}}">{{$comment->votedUp}}</span>
            <i id="voteDown-{{$comment->id}}" class="fa fa-solid fa-caret-down fa-2x {{array_key_exists($comment->id,$hasVoted) ? ($hasVoted[$comment->id]->votedUp == 0 ? "text-danger" : "") : ""}}" onclick="handleVoteDown(event)"></i>
            <span id="votedDown-{{$comment->id}}" class="ms-2 me-2 {{array_key_exists($comment->id,$hasVoted) ? ($hasVoted[$comment->id]->votedUp == 0 ? "text-danger" : "") : ""}}">{{$comment->votedDown}}</span>
        </div>
    </div>
</div>