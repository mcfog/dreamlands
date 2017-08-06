import {ajax, u} from "umbrellajs";
import {modal} from "lib/modal";
import {get, handleError} from "lib/ajax";

export function init() {
    u('.post').each(initPost);

    u(document.body)
        .on('click', '.j-quote-follow', onClickFollowPost)
        .on('click', '.j-quote-post', onClickPostNo)
    ;
}

function initPost(elPost) {
    u('img.external', elPost).on('error', onImgError);
    if (!elPost.classList.contains('quote')) {
        u('.post-quote', elPost).each(initPostQuote);
    }
}

function initPostQuote(el) {
    if (el._initPostQuote) {
        return;
    }
    el._initPostQuote = true;

    const id = el.dataset.post;
    const uPost = u('#post-' + id).closest('.post');
    if (uPost.length > 0) {
        const uQuote = u('<div class="quote post">').append(uPost.children().clone());
        uQuote.find('[id]').each(function (el) {
            el.removeAttribute('id');
        });
        uQuote.find('.quote,.reply-list').remove();
        uQuote.find('.post-quote').addClass('j-quote-follow');
        u(el).after(uQuote);
        initPost(uQuote.first());
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

function onClickFollowPost(event) {
    var cancelled = false;
    modal.open(require('tpl/follow_post.dot.html')());
    modal.on('close', cancel);

    get('/api/get-quote?id=' + event.target.dataset.post).then(function (res) {
        cancel();

        modal.u('.loading').removeClass('loading').addClass('container');
        const uPost = modal.u('.post');
        uPost.html(res.result);
        initPost(uPost.first());
    }, function (e) {
        modal.close();
        return handleError(e);
    });

    function cancel() {
        modal.off('close', cancel);
        cancelled = true;
    }
}

function onImgError(event) {
    if (!document.body.contains(event.target)) {
        return;
    }
    const html = require('tpl/bad_img.dot.html')({
        src: event.target.src
    });
    u(event.target).after(html).remove();
}