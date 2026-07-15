function formatRupiah(value) {

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);

}

function formatTanggal(datetime) {

    const date = new Date(datetime);

    return date.toLocaleString('id-ID', {

        day: '2-digit',

        month: 'long',

        year: 'numeric',

        hour: '2-digit',

        minute: '2-digit'

    });

}