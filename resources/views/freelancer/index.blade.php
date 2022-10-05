@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="d-flex align-items-center justify-content-center flex-column py-4 mb-5" style="border: 3px solid #000000;border-radius: 30px;">
                    <img src="{{ asset('img/avatar.svg') }}" class="mb-4" />
                    <h1 class="fs-4 pb-5">{{Auth::user()->name}}</h1>
                    <div class="w-100 d-flex flex-row py-2 align-items-center justify-content-around user-select-none" style="border-top: 3px solid #000000;">
                        <div class="text w-75 ps-5 pe-1">
                            Show online status
                        </div>
                        <div class="form-check form-switch w-25">
                            <input class="form-check-input" type="checkbox" id="setOnline" onclick="server.online()" {{Auth::user()->is_online == 1 ? 'checked=""' : ""}}>
                        </div>
                    </div>
                    <div id="nav-1" class="w-100 d-flex flex-row py-2 align-items-center justify-content-around user-select-none" onclick="server.clickNavigation(1)" style="cursor:pointer;border-top: 3px solid #000000;">
                        <div class="text w-75 ps-5 pe-1">
                            Student Available
                        </div>
                        <div class="none w-25">
                            &nbsp;
                        </div>
                    </div>
                    <div id="nav-2" class="w-100 d-flex flex-row py-2 align-items-center justify-content-around user-select-none" onclick="server.clickNavigation(2)" style="cursor:pointer;border-top: 3px solid #000000;">
                        <div class="text w-75 ps-5 pe-1">
                            Personal Info
                        </div>
                        <div class="none w-25">
                        &nbsp;
                        </div>
                    </div>
                    <div id="nav-3" class="w-100 d-flex flex-row py-2 align-items-center justify-content-around user-select-none" onclick="server.clickNavigation(3)" style="cursor:pointer;border-top: 3px solid #000000;border-bottom: 3px solid #000000;">
                        <div class="text w-75 ps-5 pe-1">
                            Payments
                        </div>
                        <div class="none w-25">
                        &nbsp;
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="d-flex align-items-center justify-content-center flex-column py-4 mb-5 px-3" style="border: 3px solid #000000;border-radius: 30px;">
                    <div class="main-wrapper" style="width: 100%; height: 100%;">
                    </div>
                </div>
            </div>
         </div>
    </div>
    <script type="text/javascript">
        $('title').text("Ultimate Guru - Freelancer");

         class UltimateGuru {
            navigation = 1;
            constructor() {
                this.navigation = 1;
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
            async online() {
                let url = await this.sendGetRequest("/online", JSON.stringify([]));
                let data = JSON.parse(JSON.stringify(url))
                if(data.online == 1) {
                    this.alert("success", "Success!", "You are now online.");
                    $('#setOnline').attr('checked', 'checked');
                } else if(data.online == 0) {
                    this.alert("success", "Success!", "You are now offline.");
                    $('#setOnline').removeAttr('checked');
                }
            }
            async goNavigation() {
                var id = this.navigation;
                var x = "";
                if(id == 1) {
                    x = x +'<h5 class="w-100 text-start">Student Available</h5><div id="studentAvailable"></div>';
                } else if(id == 2) {
                    x = x +'<h5 class="w-100 text-start">Personal Information</h5><div id="personalInfo"></div>';
                } else if(id == 3) {
                    x = x +'<h5 class="w-100 text-start clearfix">My Payments <div class="float-end"><select class="form-control" id="filter" onchange="server.filterMyPayments()"><option value="all">All</option><option value="received">Received</option><option value="not received">Not Received</option></select></div></h5><div><table class="table"><thead><tr><th scope="col">#</th><th scope="col">Customer Name</th><th scope="col">Category Name</th><th scope="col">Price</th><th scope="col">Time</th><th scope="col">Status</th></tr></thead><tbody id="myPayments"></tbody></table></div>';
                }
                $('.main-wrapper').html("");
                this.checkNavigation(id);
                $('.main-wrapper').append(x);
            }
            async checkNavigation(id) {
                if(id == 1) {
                    let url = await this.getData("student_available");
                    this.insertStudentAvailable(url);
                } else if(id == 2) {
                } else if(id == 3) {
                    let url = await this.getData("payments/all");
                    this.insertMyPayments(url);
                }
            }
            async insertMyPayments(data) {
                data = await data;

                $('#myPayments').html("");
                
                if(data.length == 0) {
                    var x = '<tr>';
                        x = x + '<td class="text-center" colspan="6">No results found</td>';
                    x = x +'</tr>';
                    $('#myPayments').append(x);
                } else {
                    data.forEach((item) => {
                        var x = "";
                        x = x + '<tr>';
                            x = x + '<td>'+item.id+'</td>';
                            x = x + '<td>'+item.name+'</td>';
                            x = x + '<td>'+item.category_name+'</td>';
                            x = x + '<td>P'+item.price+'</td>';
                            x = x + '<td>'+moment.unix(item.timestamp).format("MM/DD/YYYY")+'</td>';
                            x = x + '<td>'+item.status+'</td>';
                        x = x +'</tr>';
                        $('#myPayments').append(x);
                    });
                }
            }
            async insertStudentAvailable(data) {
                data = await data;
                $('#studentAvailable').html("");
                if(data.length == 0) {
                    var x = '<div class="text-center">No results found.</div>';
                    $('#studentAvailable').append(x);
                } else {
                    data.forEach((item) => {
                        var x = "";
                        x = x + '<div class="d-flex mb-2 py-2 px-3 flex-row justify-content-around" style="border: 3px solid #000000;border-radius: 20px;">';
                            x = x + '<div class="d-flex"><img src="{{ asset('img/avatar.svg') }}" class="w-50"></div>';
                            x = x + '<div class="d-flex flex-column w-50">';
                                x = x +'<h5 class="ps-3 pt-3" style="margin-bottom:0;">'+item.name+'</h5>';
                                if(item.tags.length != 0) {
                                    x = x +'<div class="ps-3">';
                                        item.tags.forEach((tag) => {
                                            x = x + '<label class="badge bg-primary me-1 pt-2">'+tag+'</label>';
                                        });
                                    x = x +'</div>';
                                }
                                x = x + '<p class="ps-3 pt-1" style="font-size: 12px;color:#7D7D7D;">Plan: '+item.plan+' | '+(item.plan_minutes <= 59 ? item.plan_minutes + ' minutes' : item.plan_minutes + ' hours')+' duration</p>';
                            x = x +'</div>';
                            x = x +'<div class="d-flex flex-row align-items-center">';
                                x = x +'<button class="btn btn-success" onclick="server.approve('+item.id+')">Approve</button>';
                                x = x +'<button class="btn btn-danger" onclick="server.decline('+item.id+')">Decline</button>';
                            x = x +'</div>';
                        x = x +'</div>';
                        $('#studentAvailable').append(x);
                    });
                }
            }
            async filterMyPayments() {
                var filter = $('#filter option:selected').val();
                let url = await this.getData("payments/"+filter);
                this.insertMyPayments(url);
            }
            async clickNavigation(id) {
                this.navigation = id;
                this.goNavigation();
            }
            async approve(id) {
                let url = await this.getData("approve/"+id);
                if(url.code == "200") {
                    this.alert("success", "Success!", "You accepted the work today!");
                    window.location.reload();
                } else if(url.code == "203") {
                    this.alert("error", "Error!", "Approve: There\'s something wrong!");
                }
                this.goNavigation();
            }
            async decline(id) {
                let url = await this.getData("decline/"+id);
                if(url.code == "200") {
                    this.alert("success", "Success!", "You declined the work today!");
                } else if(url.code == "203") {
                    this.alert("error", "Error!", "Decline: There\'s something wrong!");
                }
                this.goNavigation();
            }
            async getData(link, filter = "") {
                let url;
                if(filter == "") {
                    url = await this.sendGetRequest("/" + link, "");
                } else {
                    url = await this.sendGetRequest("/" + link + filter, "");
                }
                return url;
            }
            async check() {
            let url = await this.sendGetRequest("/check", JSON.stringify([]));
                let data = JSON.parse(JSON.stringify(url))
                if(data.hasSession == "yes") {
                    window.location.reload();
                }
            }
        }
        const server = new UltimateGuru();
        server.goNavigation();

        setInterval(() => {
            server.check();
            const id = server.navigation;
            server.checkNavigation(id);
        }, 5000);

        server.check();
    </script>
@endsection