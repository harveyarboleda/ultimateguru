@extends('layouts.app')

@section('content')

    <script type="text/javascript" src="./js/tags.js"></script>

    <div class="container mb-4" id="page-1">
        <h1>General Subjects</h1>
        <div class="row">
            <div class="col-lg-12">
                <div id="subjects"></div>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-lg-12">
                <label class="d-block" style="padding:0;margin:0;">Subject Tags (Up to 10)</label>
                <input type="text" value="" data-role="tagsinput" class="form-control w-100" />
                <div class="col" style="margin:0;margin-top: 10px;padding:0;">
                    <button onclick="server.next(1)" class="btn btn-primary">Submit</button>
                </div>
            </div>  
        </div>
    </div>
    <div class="container" id="page-2" style="display: none;">
        <div class="row">
            <div class="col-lg-12">
                <button onclick="server.prev(0)" class="btn btn-primary mb-5">Back</button>
                <h1><span id="whatSubject"></span>Choose available tutors</h1>
                <div id="tutorAvailable"></div>
            </div>
        </div>
    </div>
    <div class="container" id="page-3" style="display: none;">
        <div class="row">
            <div class="col-lg-12">
                <button onclick="server.prev(1)" class="btn btn-primary mb-5">Back</button>
                <h1><span id="whatSubject"></span>Choose your Plan</h1>
                <div id="choosePlan"></div>
                <div style="margin:0;margin-top: 30px;padding:0;">
                    <button onclick="server.choosePayment()" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $('title').text("Ultimate Guru - User");

        $('input').tagsinput({
            maxChars: 20,
            maxTags: 10
        });

        class UltimateGuru {
            time = "0";
            timestamp = 0;
            subjects = 0;
            plans = 0;
            page = 0;
            selectedSubject = 0;
            selectedSubjectWork = "";
            selected_tutor_id = "";
            selected_category_id = "";
            constructor() {
                this.time = "0";
                this.timestamp = 0;
                this.subjects = 0;
                this.plans = 0;
                this.page = 0;
                this.selectedSubject = 0;
                this.selectedSubjectWork = "";
                this.selected_tutor_id = "";
                this.selected_category_id = "";
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
                let url = await this.sendGetRequest("/check", JSON.stringify([]));
                let data = JSON.parse(JSON.stringify(url))
                if(data.hasSession == "yes") {
                    window.location.reload();
                }
            }
            async getSubjects() {
                let url = await this.sendGetRequest("/findSubjects", JSON.stringify([]));
                let data = JSON.parse(JSON.stringify(url));
                if(this.subjects != data.length) {
                    this.subjects = data.length;
                    $("#subjects").html("");
                    data.forEach((item) => {
                        var x = `<label class="subject-box">
                                    <input type="radio" name="subject_name" value="`+item.subjects_id+`"/>
                                    <div><span>`+item.subject_name+`</span></div>
                                </label>`;
                        $('#subjects').append(x);
                    });
                }
            }

            async getPlans() {
                let url = await this.sendGetRequest("/findPlan", JSON.stringify([]));
                let data = JSON.parse(JSON.stringify(url));
                if(this.plans != data.length) {
                    this.plans = data.length;
                    $("#choosePlan").html("");
                    data.forEach((item) => {
                        var x = `<label class="subject-box">
                                    <input type="radio" name="category_id" value="`+item.category_id+`"/>
                                    <div><span>`+item.category_name+`</span></div>
                                </label>`;
                        $('#choosePlan').append(x);
                    });
                }
            }

            async getAvailable(id = "") {

                let url;
                
                if(id) {
                    url = await this.sendGetRequest("/findSubjectsAvailable/"+id, JSON.stringify([]));
                } else {
                    url = await this.sendGetRequest("/findSubjectsAvailable/"+this.selectedSubject, JSON.stringify([]));
                }

                
                    
                this.selectedSubject = $('input:checked').val();
                this.selectedSubjectWork = $('input[type=radio]:checked + div').text();
                $('#whatSubject').text("("+this.selectedSubjectWork+") ");
                let data = JSON.parse(JSON.stringify(url));
                if(data.code != '203') {
                    if(this.page == 0) {
                        this.page = 0;
                        $('#page-1').hide();
                        $('#page-2').hide();
                        $('#page-3').hide();
                        $('#page-1').show();
                    } else if(this.page == 1) {
                        this.page = 1;
                        $('#page-1').hide();
                        $('#page-2').hide();
                        $('#page-3').hide();
                        $('#page-2').show();
                        $("#tutorAvailable").html("");
                        this.insertAvailable(data);
                    } else if(this.page == 2) {
                        this.page = 2;
                        $('#page-1').hide();
                        $('#page-2').hide();
                        $('#page-3').hide();
                        $('#page-3').show();
                    }
                } else {
                    if(this.page == 1) {
                        this.page = 0;
                        $('#page-1').hide();
                        $('#page-2').hide();
                        $('#page-3').hide();
                        $('#page-1').show();
                        this.alert("error","Error!","There\'s no tutor available right now!");
                    } else if(this.page == 0) {
                        this.page = 0;
                        $('#page-1').hide();
                        $('#page-2').hide();
                        $('#page-3').hide();
                        $('#page-1').show();
                    } else if(this.page == 2) {
                        this.page = 0;
                        $('#page-1').hide();
                        $('#page-2').hide();
                        $('#page-3').hide();
                        $('#page-1').show();
                    }
                }
             }
             
             async insertAvailable(data) {
                data.forEach((item) => {
                    var x = "";
                    x = x + '<div class="d-flex mb-2 py-2 px-3 flex-row justify-content-around" style="border: 3px solid #000000;border-radius: 20px;">';
                        x = x + '<div class="d-flex"><img src="{{ asset('img/avatar.svg') }}" class="w-50"></div>';
                        x = x + '<div class="d-flex flex-column w-50">';
                            x = x +'<h5 class="ps-3 pt-3" style="margin-bottom:0;">'+item.name+'</h5>';
                            if(item.tags.length != 0) {
                                x = x +'<div class="ps-3">';
                                    item.tags.forEach((tag) => {
                                        x = x + '<label class="badge bg-primary mt-1 me-1 pt-2">'+tag+'</label>';
                                    });
                                x = x +'</div>';
                            }
                        x = x +'</div>';
                        x = x +'<div class="d-flex flex-row align-items-center">';
                            x = x +'<button class="btn btn-success" onclick="server.choose('+item.tutor_id+')">Choose</button>';
                        x = x +'</div>';
                    x = x +'</div>';
                    $('#tutorAvailable').append(x);
                });
             }

             async next(id) {
                this.page = id;

                this.selectedSubject = $('input:checked').val();
                this.selectedSubjectWork = $('input[type=radio]:checked + div').text();
                $('#whatSubject').text("("+this.selectedSubjectWork+") ");
                if($('input[type=radio]:checked').val()) {
                    this.getAvailable(this.selectedSubject);
                    console.log("Subject / Success!");
                } else {
                    console.log("Subject / Error!");
                    return false;
                }
            }
            async prev(id) {
                this.page = id;
                $('#page-1').hide();
                $('#page-2').hide();
                $('#page-3').hide();
                $('#page-'+(id+1)).toggle();
            }
            async choose(tutor_id) {
                this.selected_tutor_id = tutor_id;
                this.next(2);
                console.log(this.page)
            }
            async choosePayment() {
                var array = {};
                array['category_id'] = $('input[name=category_id]:checked').val();
                array['subjects_id'] = $('input[name=subject_name]:checked').val();
                array['tutor_id'] = this.selected_tutor_id;
                array['tags'] = $("input[data-role=tagsinput]").tagsinput('items');

                if(
                    array['category_id'].length == 0 || 
                    array['subjects_id'].length == 0 || 
                    array['tutor_id'].length == 0
                ) {
                    this.alert("error","Error!","There\'s something wrong!");
                } else {
                    let url = await this.sendPostRequest("/choose", JSON.stringify(array));
                    this.alert("success","Success!","Please wait...");
                    window.location.reload();
                }
            }
        }
        
        const server = new UltimateGuru();
        
        server.check();
        server.getSubjects();
        server.getPlans();

        setInterval(() => {
            server.check();
            server.getSubjects();
            server.getPlans();
            if(server.selectedSubject != 0) {
                server.getAvailable(server.selectedSubject);
            }
        }, 5000);
    </script>
@endsection