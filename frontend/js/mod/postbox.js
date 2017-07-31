import {u} from "umbrellajs";
import * as ModUserSpawn from "mod/userspawn";
import {handleError, handleMessage, post} from "lib/ajax";

export function init() {
    ModUserSpawn.init();
    ModUserSpawn.emitter.on('spawn', reloadPostbox);
    u(document.body)
    // .on('submit', '.postbox', function (event) {
    //     console.log(event);
    //     // event.preventDefault();
    // })
        .on('dblclick', '.postbox input[name=title]', showHiddenButtons)
        .on('click', '.postbox button[name=attachImg]', attachImage)
    ;

    function showHiddenButtons(event) {
        u(event.target).closest('.postbox').find('.pure-button.hidden').removeClass('hidden');
    }

    function attachImage(event) {
        event.preventDefault();
        const img = prompt();//todo
    }

    function reloadPostbox() {
        post('/api/post-box', {
            id: u('.postbox').data('parent-id')
        })
            .then(handleMessage)
            .then(function (o) {
                u('.postbox-container').empty().append(o.result);
            })
            .catch(handleError);
    }
}
