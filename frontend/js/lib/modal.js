import Events from "minivents";
import {u} from "umbrellajs";
import {once} from "lib/utility";

export const modal = {
    open(inner) {
        const uPop = this.u();
        document.body.classList.add('modal');
        uPop.empty();
        uPop.append(inner);
    },
    close() {
        this.u().children().remove();
        document.body.classList.remove('modal');
        this.emit('close');
    },
    isOpen() {
        return document.body.classList.contains('modal');
    },
    u(selector) {
        if (!this._uPopup) {
            this._uPopup = u('#modal-popup');
        }
        return selector ? u(selector, this._uPopup.nodes[0]) : this._uPopup;
    }
};

Events(modal);

u(document.body).on('click', onClickBody);

function onClickBody(e) {
    if ('modal-popup' === e.target.id || e.target.classList.contains('modal-close')) {
        modal.close();
    }
}

export const message = {
    alert(message, option) {
        option = Object.assign({
            message
        }, option);

        return new Promise((resolve, reject) => {
            console.log(option);
            modal.open(require('tpl/message.dot.html')(option));
            once(modal, 'close', resolve);
        });
    }
};
