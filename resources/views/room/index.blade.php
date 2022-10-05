@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            @if(Auth::user()->account_level == "Freelancer")
            <div class="col-lg-12 mb-4" id="tools">
                @if($check == 0)
                    <button class="btn btn-success" id="start" onclick="start()">Start</button>
                @else
                    <button class="btn btn-primary" id="pause" onclick="pause()">Pause</button>
                @endif
                <button class="btn btn-danger" id="end"  onclick="server.end()">End</button>

                <div class="float-end">
                    <span class="time badge bg-primary">You have 0 minutes left.</span><br/>
                    <span class="live badge bg-primary"></span>
                </div>
                <div class="clearfix"></div>
            </div>
            @endif

            @if(Auth::user()->account_level == "User")
            <div class="col-lg-12 mb-4" id="tools">
                <div class="float-end">
                    <span class="time badge bg-primary">You have 0 minutes left.</span><br/>
                    <span class="live badge bg-primary"></span>
                </div>
                <div class="clearfix"></div>
            </div>
            @endif

            <script src="https://meet.jit.si/external_api.js"></script>
            <div class="col-lg-12">
                <div id="meet"></div>
            </div>
            
        </div>
    </div>
    <script type="text/javascript">
        var domain = "meet.jit.si";
        var options = {
            
            roomName: '{{$room_session}}',
            width: '100%',
            height: 700,
            parentNode: document.querySelector('#meet'),
            userInfo: {
                avatarURL: "/img/logo.svg",
                email: '{{Auth::user()->email}}',
                displayName: '{{Auth::user()->name}}',
                
            },
            configOverwrite: {
                chromeExtensionBanner: null,
                readOnlyName: true,
                disableInviteFunctions: true,
                logoImageUrl: '/img/logo.svg'
            },
            interfaceConfigOverwrite: {
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                SHOW_BRAND_WATERMARK: false,
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                    'fodeviceselection', 'profile', 'chat', 'recording',
                        'livestreaming', 'etherpad', 'sharedvideo', 'shareaudio', 'settings', 'raisehand',
                        'videoquality', 'filmstrip', 'stats',
                        'tileview', 'select-background', 'download'
                ],
                DEFAULT_LOGO_URL: '/img/logo.svg',
                DEFAULT_BACKGROUND: '#000',
                noSsl: true,
                HIDE_DEEP_LINKING_LOGO: true,
                HIDE_INVITE_MORE_HEADER: true,
                DEFAULT_REMOTE_DISPLAY_NAME: '{{Auth::user()->name}}',
                DEFAULT_LOCAL_DISPLAY_NAME: '{{Auth::user()->name}}',
            },
            
        }
        var api = new JitsiMeetExternalAPI(domain, options);
        function pad(num, size) {
            num = num.toString();
            while (num.length < size) { num = "0" + num; }
            return num;
        }
        class UltimateGuru {
            time = "0";
            timestamp = 0;
            overall_minutes = 0;
            minutes = 0;
            constructor() {
                this.time = "0";
                this.timestamp = 0;
                this.overall_minutes = 0;
                this.minutes = 0;
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
                if(data.minutes == 0) {
                    $('#end').attr("disabled","disabled");
                } else {
                    $('#end').removeAttr("disabled");
                }
                if(data.left == 0 && data.hasSession == "no") {
                    window.location.reload();
                }
                $('.time').text("You have " +data.left+" minutes left.");
                this.time = data.left +":00";
                if(data.timestamp == 0) {
                    this.timestamp = "Whoops";
                    this.overall_minutes = data.overall_minutes;
                    this.minutes = data.minutes;
                    $('.live').hide();
                    $('.time').show();
                } else {
                    this.timestamp = data.timestamp;
                    this.overall_minutes = data.overall_minutes;
                    this.minutes = data.minutes;
                    $('.live').show();
                    $('.time').hide();
                }
            }

            async start() {
                let url = await server.sendGetRequest("/start", JSON.stringify([]));
                let data = JSON.parse(JSON.stringify(url))
                if(data.code == 200) {
                    this.alert("success", "Success!", "The session has started!");
                } else {
                    this.alert("error", "Error!", "You can't start again, you must click end to start again.");
                }
            }

            async pause() {
                let url = await server.sendGetRequest("/pause", JSON.stringify([]));
                let data = JSON.parse(JSON.stringify(url))
                if(data.code == 200) {
                    this.alert("success", "Success!", "The session has paused!");
                } else {
                    this.alert("error", "Error!", "You can't pause again, you must click start to pause again.");
                }
            }
            async end() {
                if(confirm("Are you sure to end this session?")) {
                    let url = await server.sendGetRequest("/end", JSON.stringify([]));
                    let data = JSON.parse(JSON.stringify(url))
                    if(data.code == 200) {
                        this.alert("success", "Success!", "The session has ended!");
                    } else {
                        this.alert("error", "Error!", "There's something wrong!");
                    }
                } else {
                    e.preventDefault();
                }
            }
        }
        
        const server = new UltimateGuru();
        
        setInterval(() => {
            server.check();
        }, 5000);

        server.check();

        var live = setInterval(() => {
            var data = server.timestamp;
            if(data != "Whoops") {
                var startTime = moment(data); // start timestamp
                var nowTime = moment(Math.floor(Date.now() / 1000)); // end timestamp or now timestamp
                var d = nowTime.diff(startTime);
                var h = Math.floor(d / 3600);
                var m = Math.floor(d % 3600 / 60);
                var s = Math.floor(d % 3600 % 60);
                var om = Math.round(server.minutes == 0 ? server.overall_minutes : server.overall_minutes-server.minutes);
                var result = (
                    om >= 60 ?
                    pad(Math.floor(om / 60),2)+":"+pad(Math.floor(om % 60),2)+":"+pad(0, 2) :
                    "00:"+pad(om,2)+":00"
                );
                $('.live').html(pad(h, 2)+":"+pad(m, 2)+":"+pad(s,2)+" / " +result);
            }
        }, 1000)

        function start() {
            server.start();
            $('#start').remove();
            $('#tools').prepend('<button class="btn btn-primary" id="pause" onclick="pause()">Pause</button>');
            
        }
        function pause() {
            server.pause();
            $('#pause').remove();
            server.timestamp = "Whoops";
            
            $('#tools').prepend('<button class="btn btn-success" id="start" onclick="start()">Start</button>');            
        }

        
    </script>
@endsection