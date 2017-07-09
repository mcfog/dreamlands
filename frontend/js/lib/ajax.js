import {ajax} from "umbrellajs";

export function get(path) {
    return request('GET', path);
}
export function post(path, body) {
    if (Object.prototype.isPrototypeOf(body)) {
        const obj = body;
        body = new FormData;
        Object.keys(obj).forEach(function (k) {
            body.append(k, obj[k]);
        });
    }
    console.log(body);
    return request('POST', path, body);
}

function request(method, path, body) {
    const headers = new Headers();
    headers.append('X-Requested-With', 'XMLHttpRequest');
    return fetch(path, {
        method, body, headers,
        credentials: 'include'
    }).then(handle).then(wrapResult, wrapError);

    function handle(res) {
        const type = res.headers.get('Content-Type');
        switch (type) {
            case 'application/json':
                return res.json().then(function (o) {
                    if (o.isError) {
                        return Promise.reject(o.result);
                    }

                    return o.result;
                });
            default:
                return res.text();
        }
    }

    function wrapResult(result) {
        return {
            isError: false,
            result: result,
        }
    }

    function wrapError(e) {
        console.error(e);
        if (!e.message) {
            e.message = "unknown error";
        }

        return Promise.reject({
            isError: true,
            result: e,
        });
    }
}
