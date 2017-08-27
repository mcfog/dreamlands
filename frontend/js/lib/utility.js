// export function Z(g) {
//     return v => g(Z(g))(v);
// }
//
// export function Y(g) {
//     return g(() => Y(g));
// }
//
// export function U(g) {
//     return g(g);
// }

export function once(emitter, event, handler) {
    emitter.on(event, _ => {
        emitter.off(event, handler);
        handler(_);
    });
}
