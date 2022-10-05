class Register {
    async sendGetRequest(url){
        return fetch(url, {
            method: "GET",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        })
        .then((response) => response.json())
        .then((data) => {
            return data;
        })
        .catch(function (error) {
            console.error(error);
        });
    }
    async sendPostRequest(url, formdata) {
        return fetch(url, {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            body: formdata,
        })
        .then((response) => response.json())
        .then((data) => {
            return data;
        })
        .catch(function (error) {
            console.error(error);
        });
    }
    async register(array) {
        //let data = await this.sendPostRequest("/register", JSON.stringify(array));
        let data = await this.sendPostRequest("/register", array);
        console.log(data)
    }
}

const reg = new Register();

$('form#register').submit(function(e) {
    e.preventDefault();

    let formData = new FormData();
    formData.append('name', $('#name').val());
    formData.append('email', $('#email').val());
    formData.append('password', $('#password').val());
    formData.append('password_confirmation', $('#password-confirm').val());
    formData.append('email', $('#email').val());
    formData.append('account_level', $('#account_level option:selected').val());

    reg.register(formData);
});