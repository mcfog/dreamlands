import * as loading from "lib/loading";

const PAGE = {
    'board-board': 'board',
    'board-thread': 'thread',
};
const TPL = {
    followPost: require('tpl/follow_post.dot.html'),
};

document.addEventListener('DOMContentLoaded', ready);

function ready() {
    if (PAGE[document.body.id]) {
        require('page/' + PAGE[document.body.id]).init();
    }


    document.body.classList.remove('loading');
}
