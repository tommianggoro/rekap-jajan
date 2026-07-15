async function apiGet(url) {

    const response = await fetch(url);

    const result = await response.json();

    if (!result.success) {
        throw new Error(result.message || 'Terjadi kesalahan.');
    }

    return result.data;

}

async function apiPost(url, body) {

    const response = await fetch(url, {
        method: 'POST',
        body: body
    });

    const result = await response.json();

    if (!result.success) {
        throw new Error(result.message || 'Terjadi kesalahan.');
    }

    return result.data;
}

