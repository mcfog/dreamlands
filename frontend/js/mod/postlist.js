import {ajax, u} from "umbrellajs";

export function init() {

    u('.post-quote').each(initPostQuote);

    u(document.body)
        .on('click', '.j-quote-follow', onClickFollowPost)
        .on('click', '.j-quote-post', onClickPostNo)
    ;
}


function initPostQuote(el) {
    const id = el.dataset.post;
    const uPost = u('#post-' + id).closest('.post');
    if (uPost.length > 0) {
        const uQuote = u('<div class="quote post">').append(uPost.children('.head,.content').clone());
        uQuote.find('[id]').each(function (el) {
            el.removeAttribute('id');
        });
        uQuote.find('.quote').remove();
        uQuote.find('.post-quote').addClass('j-quote-follow');
        u(el).after(uQuote);
    } else {
        el.classList.add('j-quote-follow');
    }
}

function onClickPostNo(e) {
    if (modal.isOpen()) {
        return;
    }

    const id = e.currentTarget.dataset.post;
    const area = document.getElementById('post-content');
    if (!area) {
        return;
    }

    if (!area.value) {
        area.value = ">> " + id + "\n";
    } else {
        area.value += "\n>> " + id + "\n";
    }

    area.focus();
}

function onClickFollowPost(e) {
    var cancelled = false;
    modal.open(require('tpl/follow_post.dot.html')());
    modal.on('close', cancel);

    ajax('/api/get-quote?id=' + e.target.dataset.post, {}, function (err, res) {
        if (cancelled) {
            return;
        }
        cancel();

        if (!res) {
            alert('???');
            modal.close();
        } else {
            modal.u('.loading').removeClass('loading').addClass('container');
            modal.u('.post').html(res);
            modal.u('.post-quote').each(initPostQuote);
        }
    });

    function cancel() {
        modal.off('close', cancel);
        cancelled = true;
    }
}
