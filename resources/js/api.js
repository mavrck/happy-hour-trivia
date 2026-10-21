const csrf = () => document.querySelector('meta[name="csrf-token"]').content;

async function request(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf(),
            ...options.headers,
        },
    });
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        const error = new Error(data.message || `Request failed (${response.status})`);
        error.status = response.status;
        error.data = data;
        throw error;
    }

    return data;
}

export const get = (url) => request(url);
export const post = (url, body) => request(url, { method: 'POST', body: JSON.stringify(body) });
