document
    .getElementById('login-form')
    .addEventListener('submit', login);

async function login(event) {

    event.preventDefault();

    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;

    const formData = new FormData();

    formData.append('username', username);
    formData.append('password', password);

    try {

        await apiPost(
            `${window.APP.apiBase}/login.php`,
            formData
        );

        window.location.href = 'index.php';

    } catch (error) {

        alert(error.message);

    }

}