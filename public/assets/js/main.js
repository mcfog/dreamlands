(function () {
    document.addEventListener("DOMContentLoaded", ready);

    function ready() {
        if (document.body.id === 'board') {
            u('.post .content').each(function (el) {
                if (parseInt(el.clientHeight) < 100) return;
                el.classList.add('collapsed');
            });
        }

        u('.post-quote').each(initPostQuote);

        u(document.body)
            .on('click', '.j-quote-follow', function () {
                alert(123)
            })
            .on('click', '.j-quote-post', onClickPostNo);

        document.body.classList.remove('loading');
    }

    function initPostQuote(el) {
        var id = el.dataset.post;
        var uPost = u('#post-' + id).closest('.post');
        if (uPost.length > 0) {
            var uQuote = u('<div class="quote post">').append(uPost.children('.head,.content').clone());
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
        var id = e.currentTarget.dataset.post;
        var area = document.getElementById('post-content');
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
})();