@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col">
        <div class="d-flex bd-highlight">
            <div class="p-2 flex-fill bd-highlight">
                <label for="basic-url" class="form-label" style="font-size: 11px;text-transform: uppercase; color:#212529; font-weight:bold; margin: 0;">Search</label>
                <div class="input-group mb-3">
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search..." >
                </div>
            </div>
            <div class="p-2 bd-highlight">
                <label for="basic-url" class="form-label" style="font-size: 11px;text-transform: uppercase; color:#212529; font-weight:bold; margin: 0;">Filter</label>
                <div class="input-group mb-3">
                    <select class="form-select" id="filter" onclick="server.change();">
                        <option value="All" selected>All</option>
                        <option value="1">User</option>
                        <option value="2">Tutor (Verified)</option>
                        <option value="3">Tutor (Unverified)</option>
                        <option value="4">Admin</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col" style="width: 80%;"></th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody id="list">
            </tbody>    
        </table>
    </div>
</div>
<script type="text/javascript">
    class UltimateGuru {
        constructor() {
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
        async change() {
            var array = {};

            var search = $('input[name=search]').val();
            var filter = $('#filter option:checked').val();
            
            array['search'] = search;
            array['filter'] = filter;

            let data = await this.sendPostRequest("/getList", JSON.stringify(array));

            if(data.length == 0) {
                this.alert("error","Error!","No results found!");
            } else {
                $('#list').html("");
                data.forEach( (item) => {
                    var x = "";
                    x = x + `
                    <tr>
                        <td>
                            <b>`+item.name+`</b><br/>
                            <span class="badge bg-info">Account Level: `+item.account_level+`</span>
                            <span class="badge bg-info">Created Date: `+item.created_at+`</span>
                            `+((item.account_level == "Freelancer" && item.is_verified == 0) ? `<span class="badge bg-danger">Unverified Account</span>` : `` )+`
                            `+((item.account_level == "Freelancer" && item.is_verified == 1) ? `<span class="badge bg-primary">Verified Account</span>` : `` )+`
                        </td>
                        <td>
                            `+((item.account_level == "User" || item.account_level == "Freelancer") ? `<button type="button" class="btn btn-primary btn-sm">Activity Log</button>` : '')+`
                            `+((item.account_level == "Freelancer") ? `<a href="/show/`+item.resume+`"><button type="button" class="btn btn-success btn-sm" style="margin:2px;">View Resume</button></a>` : "")+`
                            `+((item.account_level == "Freelancer" && item.is_verified == 0) ? `<a onclick="server.activate(`+item.id+`)"><button type="button" class="btn btn-success btn-sm" style="margin:2px;">Activate Account</button></a>` : "")+`
                            `+((item.account_level == "Freelancer" && item.is_verified == 1) ? `<a onclick="server.activate(`+item.id+`)"><button type="button" class="btn btn-danger btn-sm" style="margin:2px;">Deactivate Account</button></a>` : "")+`
                            `+((item.account_level == "Freelancer" && item.is_verified == 1) ? `<a href="/payment/`+item.id+`"><button type="button" class="btn btn-primary btn-sm" style="margin:2px;">Payment</button></a>` : "")+`
                        </td>
                    
                    </tr>
                    `;
                    $('#list').append(x);
                });
            }
        }

        async activate(id) {
            let data = await this.sendGetRequest("/action/"+id, "");
            this.alert("success","Success!","Successfully!");
            server.change();
        }

    }

    const server = new UltimateGuru();

    $('#search').keyup(function(e) {
        e.preventDefault();
        server.change();
    });

    server.change();
</script>
@endsection