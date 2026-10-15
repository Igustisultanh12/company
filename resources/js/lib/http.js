import axios from 'axios';

/** Instance axios untuk permintaan JSON kecil (notifikasi, komentar). CSRF via cookie XSRF-TOKEN. */
const http = axios.create({
    headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
    withCredentials: true,
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
});

export default http;
