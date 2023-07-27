@extends('main') @section('content')
<div class="position-relative w-100 h-100">
    <div class="position-absolute w-100 h-100">
        <div id="alertHolder" class="sticky-top"></div>
    </div>
    <div class="container bg-secondary pt-2 pb-2 border rounded">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="card-title fs-2 fw-bold mb-0 me-auto">Leave a comment:</div>
                    <form id="logoutForm" action="/logout" method="POST">
                        @csrf
                        <i id="logout" class="fa fa-solid fa-door-open logout"></i>
                    </form>
                </div>
                <form id="commentForm" action="/comments/add">
                    <textarea class="w-100" name="commentArea" id="commentArea" cols="30" rows="5"></textarea>
                    <ul id="commentErrors" class="text-danger fw-bold mb-0"></ul>
                    <div class="d-flex">
                        <button id="commentBtn" class="btn btn-primary ms-auto">Comment</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="commentHolder"></div>
    </div>
    <div id="loadMore"></div>
</div>
<script>
    const channel = Echo.channel('public.comments')

    let commentsCreated = 0

    channel.listen('.CommentAdded', (e) => {
        prependCommentContent(e.html)
        commentsCreated += 1
    })

    channel.listen('.UserVoted', (e) => {
        if(document.getElementById('comment-' + e.commentId) !== null)
        {
            const voteUp = document.getElementById('voteUp-' + e.commentId)
            const votedUp = document.getElementById('votedUp-' + e.commentId)
            const voteDown = document.getElementById('voteDown-' + e.commentId)
            const votedDown = document.getElementById('votedDown-' + e.commentId)
            if(e.voteUp && e.senderId === {{auth()->user()->id}}) handleVote(e.decrementsThis, e.decrementsOther, voteUp, votedUp, 'text-primary', voteDown, votedDown, 'text-danger')
            else if(e.senderId === {{auth()->user()->id}}) handleVote(e.decrementsThis, e.decrementsOther, voteDown, votedDown, 'text-danger', voteUp, votedUp, 'text-primary')
            else if(e.voteUp) handleVote(e.decrementsThis, e.decrementsOther, voteUp, votedUp, '', voteDown, votedDown, '')
            else handleVote(e.decrementsThis, e.decrementsOther, voteDown, votedDown, '', voteUp, votedUp, '')
        }
    })

    const more = document.getElementById('loadMore')

    let currentPage = 1
    const commentCount = {{$count}}
    const commentsPerPage = 10

    const observer = new IntersectionObserver(entries => {
        if(entries[0].isIntersecting)
        {
            if(!((currentPage - 1) * commentsPerPage >= commentCount))
            {
                let loadMore = entries[0].target
                loadMore.classList.add('d-flex','justify-content-center')
                loadMore.innerHTML = 
                '<div class="spinner-border text-secondary m-2" role="status">\
                    <span class="visually-hidden">Loading...</span>\
                </div>'
                axios.get('/comments/next', {
                    params: {
                        page: currentPage,
                        commentsPerPage: commentsPerPage,
                        created: commentsCreated
                    }
                }).then((res) => {
                    loadMore.classList.remove('d-flex', 'justify-content-center')
                    loadMore.innerHTML = ''
                    const comments = res.data.htmls
                    for(let i = 0; i < comments.length; i++)
                    {
                        appendCommentContent(comments[i])
                    }
                    currentPage += 1
                    observer.unobserve(more)
                    observer.observe(more)
                }).catch((err) => {
                    loadMore.classList.remove('d-flex', 'justify-content-center')
                    loadMore.innerHTML = ''
                    displayErrorAlert(err)
                })
            }
            else observer.unobserve(more)
        }
    })

    observer.observe(more)

    document.getElementById('commentForm').addEventListener('submit', (e) => {
        e.preventDefault()
        const data = new FormData(e.target)
        const fields = Object.fromEntries(data.entries())
        axios.post('/comments/add', fields).then((res) => {
            const errs = document.getElementById('commentErrors')
            errs.innerHTML = ''
            document.getElementById('commentArea').value = ''
            prependCommentContent(res.data.html)
            commentsCreated += 1
        }).catch((err) => {
            const errs = document.getElementById('commentErrors')
            errs.innerHTML = ''
            for(let i = 0; i < err.response.data.errors.commentArea.length; i++)
            {
                let li = document.createElement('li')
                li.innerHTML = err.response.data.errors.commentArea[i].trim()
                errs.append(li)
            }
        })
    })

    document.getElementById('logout').addEventListener('click', () => {
        document.getElementById('logoutForm').submit()
    })

    function appendCommentContent(content)
    {
        const commentHolder = document.getElementById('commentHolder')
        let div = document.createElement('div')
        div.innerHTML = content
        commentHolder.append(div.firstChild)
    }

    function prependCommentContent(content) 
    {
        const commentHolder = document.getElementById('commentHolder')
        let div = document.createElement('div')
        div.innerHTML = content
        commentHolder.prepend(div.firstChild)
    }

    function handleVoteUp(e)
    {
        const commentId = e.target.id.split('-')[1]
        axios.post('/comments/vote', {
            'id': commentId,
            'voteUp': true
        }).then((res) => {
            const votedUp = document.getElementById('votedUp-' + commentId)
            const votedDown = document.getElementById('votedDown-' + commentId)
            const voteDown = document.getElementById('voteDown-' + commentId)
            handleVote(res.data.decrementsThis, res.data.decrementsOther,e.target,votedUp,'text-primary',voteDown,votedDown,'text-danger')
        }).catch((err) => {
            displayErrorAlert(err)
        })
    }

    function handleVoteDown(e)
    {
        const commentId = e.target.id.split('-')[1]
        axios.post('/comments/vote', {
            'id': commentId,
            'voteUp': false
        }).then((res) => {
            const votedDown = document.getElementById('votedDown-' + commentId)
            const votedUp = document.getElementById('votedUp-' + commentId)
            const voteUp = document.getElementById('voteUp-' + commentId)
            handleVote(res.data.decrementsThis,res.data.decrementsOther,e.target,votedDown,'text-danger',voteUp,votedUp,'text-primary');
        }).catch((err) => {
            displayErrorAlert(err)
        })
    }

    function displayErrorAlert(err)
    {
        let div = document.createElement('div')
        div.classList.add('alert', 'alert-danger', 'mb-0')
        div.setAttribute('role', 'alert')
        let ul = document.createElement('ul')
        ul.classList.add('fw-bold', 'mb-0')
        const keys = Object.keys(err.response.data.errors)
        keys.forEach(key => {
            err.response.data.errors[key].forEach(item => {
                let li = document.createElement('li')
                li.innerHTML = item.trim()
                ul.append(li)
            })
        })
        div.append(ul)
        document.getElementById('alertHolder').append(div)
        let bsAlert = new bootstrap.Alert(div)
        setTimeout(() => {
            bsAlert.close()
        }, 3000);
    }

    function handleVote(dThis, dOther, vThis, cThis, sThis, vOther, cOther, sOther)
    {
        if(dThis)
        {
            cThis.innerHTML = parseInt(cThis.innerHTML) - 1
            if(sThis !== '')
            {
                vThis.classList.remove(sThis)
                cThis.classList.remove(sThis)
            }
        }
        else
        {
            cThis.innerHTML = parseInt(cThis.innerHTML) + 1
            if(sThis !== '')
            {
                vThis.classList.add(sThis)
                cThis.classList.add(sThis)
            }
        }
        if(dOther)
        {
            cOther.innerHTML = parseInt(cOther.innerHTML) - 1;
            if(sOther !== '')
            {
                vOther.classList.remove(sOther)
                cOther.classList.remove(sOther)
            }
        }
    }
</script>
@endsection