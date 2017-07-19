window.paceOptions = {
    eventLag: false,
    document: false,
    elements: {
        selectors: ['body:not(.loading)']
    }

};
const Pace = require("pace-progress");

export function start() {
    document.body.classList.add('loading');
    Pace.restart();
}

export function stop() {
    document.body.classList.remove('loading');
    Pace.stop();
}

