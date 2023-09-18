function login_with_github() {
    let github_user_code_input = document.getElementById(`github_user_code`);
    const csrf_token = document.querySelector(`meta[name="csrf-token"]`).getAttribute(`content`);
    const response = fetch(`${api_url}/auth/github`, {
        method: `POST`,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrf_token
        }
    });

    response.then((response) => {
        if (response.status == 200) {
            response.json().then((data) => {
                github_user_code_input.style.display = `block`;
                github_user_code_input.value = data.user_code;

                // copy data.user_code to clipboard
                github_user_code_input.select();
                document.execCommand(`copy`);

                go_to_github_auth_page();

                // interval to chechk if user has authorized the app
                const interval = setInterval(() => {
                    const response = fetch(`${api_url}/auth/github/check`, {
                        method: `POST`,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': csrf_token
                        },
                        body: JSON.stringify({
                            user_code: data.user_code
                        })
                    });

                    response.then((response) => {
                        if (response.status == 200) {
                            response.json().then((data) => {
                                if (data.authorized) {
                                    clearInterval(interval);
                                    window.location.reload();
                                }
                            });
                        }
                    });
                }, 10000);
            });
        } else {
            alert(`Error: ${response.status}`);
        }
    });
}


function go_to_github_auth_page() {
    // open i new tab
    window.open(`https://github.com/login/device/`);
}

const authorized = document.querySelector("body").getAttribute("data-authorized");
