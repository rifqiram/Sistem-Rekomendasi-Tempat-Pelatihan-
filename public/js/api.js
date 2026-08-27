// public/js/api.js

window.apiBase = window.location.origin + '/api';

window.getApiToken = function () {
    return localStorage.getItem('api_token');
};

window.setApiToken = function (token) {
    if (token) {
        localStorage.setItem('api_token', token);
    } else {
        localStorage.removeItem('api_token');
    }
};

window.clearApiToken = function () {
    localStorage.removeItem('api_token');
    localStorage.removeItem('api_user');
};

window.getApiUser = function () {
    try {
        return JSON.parse(localStorage.getItem('api_user')) || null;
    } catch (err) {
        return null;
    }
};

window.setApiUser = function (user) {
    localStorage.setItem('api_user', JSON.stringify(user));
};

window.authFetch = function (url, options = {}) {
    const token = window.getApiToken();
    
    if (!token) {
        window.clearApiToken();
        window.location.href = '/user/login';
        return Promise.reject('No token found');
    }

    options.headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer ' + token,
        ...(options.headers || {})
    };

    return fetch(url, options).then(async response => {
        if (response.status === 401) {
            window.clearApiToken();
            window.location.href = '/user/login';
            return Promise.reject('Unauthorized (Token Expired)');
        }

        if (response.status === 403) {
            console.error('Forbidden access to ' + url);
            // SystemAuth return specific message if user is disabled
            // Check if this is a JSON response to read the message, or just logout
            response.clone().json().then(data => {
                if (data && data.message && data.message.includes('dinonaktifkan')) {
                    window.clearApiToken();
                    window.location.href = '/user/login';
                }
            }).catch(() => {});

            return Promise.reject('Forbidden (No Access)');
        }

        return response;
    });
};

window.parseApi = function (response) {
    return response.json().then(payload => payload.data ?? payload);
};

window.logout = async function(redirectUrl = '/user/login') {
    const token = window.getApiToken();
    if (token) {
        try {
            await fetch(window.apiBase + '/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            });
        } catch (e) {
            console.warn('Logout request failed, clearing local data anyway.');
        }
    }
    
    window.clearApiToken();
    window.location.href = redirectUrl;
};
