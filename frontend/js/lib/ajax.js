import {ajax} from "umbrellajs";
import * as loading from "lib/loading";
import {message} from "lib/modal";

export function get(path) {
    return request('GET', path);
}
export function post(path, body) {
    if (Object.prototype.isPrototypeOf(body)) {
        const obj = body;
        body = new FormData;
        Object.keys(obj).forEach((k) => {
            body.append(k, obj[k]);
        });
    }
    return request('POST', path, body);
}

export function handleMessage(response) {
    if (!response.result.message) {
        return response;
    }

    return Promise.reject({isError: false, result: message.alert(response.result.message, response.result)});
}

export function handleError(response) {
    if (!response.isError) {
        return response;
    }

    return {
        isError: false,
        result: message.alert(response.result.message || '未知错误', response.result)
    };
}

function request(method, path, body) {
    const headers = new Headers();
    headers.append('X-Requested-With', 'XMLHttpRequest');
    loading.start();
    return fetch(path, {
        method, body, headers,
        credentials: 'include'
    }).then(handle).then(wrapResult, wrapError);

    function handle(res) {
        loading.stop();
        const type = res.headers.get('Content-Type');
        switch (type) {
            case 'application/json':
                return res.json().then((o) =>
                    o.isError ? Promise.reject(o.result) : o.result
                );
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
