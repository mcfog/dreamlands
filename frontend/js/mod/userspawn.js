import {u} from "umbrellajs";
import {handleError, handleMessage, post} from "lib/ajax";
import {modal} from "lib/modal";

export function init() {
    u(document.body)
        .on('click', '.j-spawn-user input', onClickSpawnUserInput)
        .on('click', '.j-spawn-user button', onClickSpawnUserButton)
    ;
}

function onClickSpawnUserInput(e) {
    let elInput = e.target;
    elInput.removeAttribute('readonly');
    elInput.focus();
}

function onClickSpawnUserButton(e) {
    e.preventDefault();
    const elInput = u(e.target).closest('.j-spawn-user').find('input[type=text]').first();
    const nickname = elInput.value;
    elInput.value = '';
    elInput.setAttribute('readonly', '');

    post('/user/spawn', {
        nickname: nickname
    }).then(handleMessage).then(function (o) {
        console.log(o);
        if (o.isError) {
            return;
        }

        // elInput.value = [o.result.name, o.result.hash].join(':');
        elInput.value = o.result.name;
    }).catch(handleError);

    // modal.open('loading');
}
