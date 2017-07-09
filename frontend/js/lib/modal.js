import * as Events from "minivents";
import {u} from "umbrellajs";

export const modal = {
    open: function (inner) {
        const uPop = this.u();
        document.body.classList.add('modal');
        uPop.empty();
        uPop.append(inner);
    },
    close: function () {
        this.u().children().remove();
        document.body.classList.remove('modal');
        this.emit('close');
    },
    isOpen: function () {
        return document.body.classList.contains('modal');
    },
    u: function (selector) {
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
