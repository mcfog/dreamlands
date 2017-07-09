import {u} from "umbrellajs";
// import {modal} from "lib/modal";

export function init() {
    u('.post .content').each(function (el) {
        if (parseInt(el.clientHeight) < 100) return;
        el.classList.add('collapsed');
    });

    require('mod/postlist').init();
    require('mod/postbox').init();
}
