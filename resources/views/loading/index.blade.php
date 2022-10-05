@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            You need wait to accept the tutor
            
        </div>
    </div>
    <script type="text/javascript">
        class UltimateGuru {
            time = "0";
            timestamp = 0;
            constructor() {
                this.time = "0";
                this.timestamp = 0;
            }
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

            async alert(icon, title, text) {
                Swal.fire({
                    title: title,
                    text: text,
                    toast: true,
                    position: 'bottom-right',
                    timer: 3000,
                    showCancelButton: false,
                    showConfirmButton: false,
                    icon: icon,
                })
            }
            async check() {
                let url = await server.sendGetRequest("/check", JSON.stringify([]));
                let data = JSON.parse(JSON.stringify(url))
                if(data.hasSession == "yes" || data.has_reserve != 1 || data.left == 0) {
                    window.location.reload();
                }
                console.log(data)
                console.log("Done loading.");
            }
        }
        
        const server = new UltimateGuru();
        
        setInterval(() => {
            server.check();
        }, 5000);

        server.check();

    </script>
@endsection