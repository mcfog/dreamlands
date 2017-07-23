import {u} from "umbrellajs";
import {handleError, handleMessage, post} from "lib/ajax";
import * as Events from "minivents";

export function init() {
    u(document.body)
        .on('click', '.j-spawn-user input', onClickSpawnUserInput)
        .on('click', '.j-spawn-user button', onClickSpawnUserButton)
    ;
}

export const emitter = new Events;

function onClickSpawnUserInput(event) {
    let elInput = event.target;
    if (elInput.value) {
        return;
    }
    elInput.removeAttribute('readonly');
    elInput.focus();
}

function onClickSpawnUserButton(event) {
    event.preventDefault();
    const uContainer = u(event.target).closest('.j-spawn-user');
    const elInput = uContainer.find('input[type=text]').first();
    const nickname = elInput.value;
    elInput.value = '';
    elInput.setAttribute('readonly', '');

    post('/user/spawn', {
        nickname: nickname
    }).then(handleMessage).then(function (o) {
        if (o.isError) {
            return;
        }

        if (-1 === nickname.indexOf(':')) {
            setTimeout(function () {
                window.prompt("请保留好您的登录凭据", [o.result.name, o.result.hash].join(':'));
            }, 500);
        }
        elInput.value = o.result.name;
        event.target.setAttribute('disabled', '');
        emitter.emit('spawn', o.result);
    }).catch(handleError);

    // modal.open('loading');
}
