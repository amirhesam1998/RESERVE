//
let login = true;
let loginFormContent = document.querySelectorAll(".form-content");
let goToRegister = document.querySelector(".go-to-register");
let goToLogin = document.querySelector(".go-to-login");

if (login) {
    loginFormContent[1].classList.add("hidden");
} else {
    loginFormContent[0].classList.add("hidden");
}

goToRegister.addEventListener("click", (event) => {
    event.preventDefault();
    login = false;
    loginFormContent[0].classList.add("hidden");
    loginFormContent[1].classList.remove("hidden");
});

goToLogin.addEventListener("click", (event) => {
    event.preventDefault();
    login = true;
    loginFormContent[1].classList.add("hidden");
    loginFormContent[0].classList.remove("hidden");
});

const loginForm = document.getElementById("loginForm");
const registerForm = document.getElementById("registerForm");

const BASE_URL = "http://localhost:5000/api";
const LOGIN_API = `${BASE_URL}/auth/login`;
const REGISTER_API = `${BASE_URL}/auth/register`;

loginForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const formData = new FormData(loginForm);
    const data = Object.fromEntries(formData);

    console.log("داده‌های ارسالی برای ورود:", data);

    try {
        const response = await fetch(LOGIN_API, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(data),
        });

        const result = await response.json();

        if (response.ok) {
            alert("ورود با موفقیت انجام شد!");
            console.log("پاسخ بک‌اند (توکن):", result);
            // localStorage.setItem('token', result.token);
            // sessionStorage.setItem('token', result.token);
        } else {
            alert(result.message || "خطایی در ورود رخ داد.");
        }
    } catch (error) {
        console.error("خطا در اتصال به سرور:", error);
        alert("اتصال با سرور برقرار نشد.");
    }
});

registerForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const formData = new FormData(registerForm);
    const data = Object.fromEntries(formData);

    if (data.password !== data.password_confirmation) {
        alert("رمز عبور و تکرار آن با یکدیگر مطابقت ندارند!");
        return;
    }

    console.log("داده‌های ارسالی برای ثبت‌نام:", data);

    try {
        const response = await fetch(REGISTER_API, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(data),
        });

        const result = await response.json();

        if (response.ok) {
            alert("ثبت‌نام با موفقیت انجام شد! حالا می‌توانید وارد شوید.");
            login = true;
            loginFormContent[1].classList.add("hidden");
            loginFormContent[0].classList.remove("hidden");
        } else {
            alert(result.message || "خطایی در ثبت‌نام رخ داد.");
        }
    } catch (error) {
        console.error("خطا در اتصال به سرور:", error);
        alert("اتصال با سرور برقرار نشد.");
    }
});
