@extends('layouts.admin')

@section('content')
@if($user_id)
    <a href="/payment">Go back</a>
@endif
<div class="row">
    <div class="col">
        
        <div class="d-flex bd-highlight">
            
            <div class="p-2 flex-fill bd-highlight">
                @if($user_id)
                    <h1 class="mt-4">{{$name}}</h1>
                @endif

                @if(!$user_id)
                <label for="basic-url" class="form-label" style="font-size: 11px;text-transform: uppercase; color:#212529; font-weight:bold; margin: 0;">Search</label>
                <div class="input-group mb-3">
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search..." >
                </div>
                @endif
            </div>
            

            <div class="p-2 bd-highlight">
                <label for="basic-url" class="form-label" style="font-size: 11px;text-transform: uppercase; color:#212529; font-weight:bold; margin: 0;">Filter</label>
                <div class="input-group mb-3">
                    <select class="form-select" id="filter" onclick="server.change();">
                        <option value="All" selected>All</option>
                        <option value="1">Paid</option>
                        <option value="2">Not Paid</option>
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
                    <th scope="col" style="width: 40%;"></th>
                    <th scope="col" style="width: 20%;">Transaction ID</th>
                    <th scope="col" style="width: 20%;">Total</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody id="list">
            </tbody>    
        </table>
    </div>
</div>
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paymentModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="pay-now"></div>
        <br/>
        <div id="btn-paypal-checkout"></div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
    const payment = {
        'payments_id': 0,
        'name': '',
        'money': 0
    };
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

            var user_id = <?php echo $user_id ? $user_id : 0; ?>;
            var search = $('input[name=search]').val();
            var filter = $('#filter option:checked').val();
            
            if(user_id != 0) {
                array['user_id'] = user_id;
            } else {
                array['user_id'] = "";
            }

            array['filter'] = filter;

            let data = await this.sendPostRequest("/getPayment", JSON.stringify(array));

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
                            `+((item.status == "Not received") ? `<span class="badge bg-danger">Not received</span>` : `` )+`
                            `+((item.status == "received") ? `<span class="badge bg-primary">Received</span>` : `` )+`

                            `+((item.type == 0) ? `<span class="badge bg-primary">Paypal</span>` : `` )+`
                            `+((item.type == 1) ? `<span class="badge bg-success">Google Pay</span>` : `` )+`

                            <span class="badge bg-primary">`+moment.unix(item.timestamp).format("MM/DD/YYYY hh:mm A")+`</span>
                        </td>
                        <td>
                            `+item.transaction_id+`
                        </td>
                        <td>
                            P`+item.total+`
                        </td>
                        <td>
                            `+((item.status == "Not received") ? `<button type="button" class="btn btn-success btn-sm" style="margin:2px;" onclick="server.pay('`+item.payments_id+`', '`+item.name+`',`+item.total+`)">Pay</button>` : "")+`
                            `+((item.status == "received") ? `<button type="button" class="btn btn-primary btn-sm" disabled style="margin:2px;">Received</button>` : "")+`
                        </td>
                    
                    </tr>
                    `;
                    $('#list').append(x);
                    
                });
            }
        }
        async pay(payments_id, name, money) {
            payment.payments_id = payments_id;
            payment.name = name;
            payment.money = (money * 0.80);
            console.log(payment)
            $('#paymentModalLabel').html("Name: "+payment.name+"<br/>Fee: P"+((payment.money/0.8)-payment.money)+"<br/>Total: P"+payment.money);
            $('#paymentModal').modal('show');
        }
        async success() {
            let url = await server.sendGetRequest("/successSalary/"+payment.payments_id, JSON.stringify([]));
            let data = JSON.parse(JSON.stringify(url))
            this.alert("success","Success!","Successfully paid!");
            payment.payments_id = "";
            payment.name = "";
            payment.money = 0;
            $('#paymentModal').modal('hide');
            this.change();
        }
    }

    const server = new UltimateGuru();

    $('#search').keyup(function(e) {
        e.preventDefault();
        server.change();
    });

    server.change();

    const baseRequest = {
            apiVersion: 2,
            apiVersionMinor: 0
        };

        const allowedCardNetworks = ["AMEX", "MASTERCARD", "VISA"];

        const allowedCardAuthMethods = ["PAN_ONLY", "CRYPTOGRAM_3DS"];

        const tokenizationSpecification = {
            type: 'PAYMENT_GATEWAY',
            parameters: {
                'gateway': 'example',
                'gatewayMerchantId': 'exampleGatewayMerchantId'
            }
        };

        const baseCardPaymentMethod = {
            type: 'CARD',
            parameters: {
                allowedAuthMethods: allowedCardAuthMethods,
                allowedCardNetworks: allowedCardNetworks
            }
        };

        const cardPaymentMethod = Object.assign({},
            baseCardPaymentMethod, {
                tokenizationSpecification: tokenizationSpecification
            }
        );


        let paymentsClient = null;


        function getGoogleIsReadyToPayRequest() {
            return Object.assign({},
                baseRequest, {
                    allowedPaymentMethods: [baseCardPaymentMethod]
                }
            );
        }


        function getGooglePaymentDataRequest() {
            const paymentDataRequest = Object.assign({}, baseRequest);
            paymentDataRequest.allowedPaymentMethods = [cardPaymentMethod];
            paymentDataRequest.transactionInfo = getGoogleTransactionInfo();
            paymentDataRequest.merchantInfo = {
                merchantId: 'BCR2DN4TUCT4PSJ4',
                merchantName: payment.name
            };

            paymentDataRequest.callbackIntents = ["PAYMENT_AUTHORIZATION"];

            return paymentDataRequest;
        }

        function getGooglePaymentsClient() {
            if (paymentsClient === null) {
                paymentsClient = new google.payments.api.PaymentsClient({
                    environment: 'TEST',
                    paymentDataCallbacks: {
                        onPaymentAuthorized: onPaymentAuthorized
                    }
                });
            }
            return paymentsClient;
        }


        function onPaymentAuthorized(paymentData) {
            return new Promise(function(resolve, reject) {
                // handle the response
                processPayment(paymentData)
                    .then(function() {
                        server.success();
                        resolve({
                            transactionState: 'SUCCESS'
                        });
                    })
                    .catch(function() {
                        server.error();
                        resolve({
                            transactionState: 'ERROR',
                            error: {
                                intent: 'PAYMENT_AUTHORIZATION',
                                message: 'Insufficient funds, try again. Next attempt should work.',
                                reason: 'PAYMENT_DATA_INVALID'
                            }
                        });
                    });
            });
        }

        function onGooglePayLoaded() {
            const paymentsClient = getGooglePaymentsClient();
            paymentsClient.isReadyToPay(getGoogleIsReadyToPayRequest())
                .then(function(response) {
                    if (response.result) {
                        addGooglePayButton();
                    }
                })
                .catch(function(err) {
                    console.error(err);
                });
        }

        function addGooglePayButton() {
            const paymentsClient = getGooglePaymentsClient();
            const button =
                paymentsClient.createButton({
                    buttonColor: 'default',
                    buttonType: 'pay',
                    buttonLocal: 'en',
                    buttonSizeMode: 'fill',
                    onClick: onGooglePaymentButtonClicked
                });
            document.getElementById('pay-now').appendChild(button);
        }

        function getGoogleTransactionInfo() {
            console.log(payment.foo)
            return {
                countryCode: 'PH',
                currencyCode: "PHP",
                totalPriceStatus: "FINAL",
                totalPrice: '' + payment.money + ''
            };
        }


        function onGooglePaymentButtonClicked() {
            const paymentDataRequest = getGooglePaymentDataRequest();
            paymentDataRequest.transactionInfo = getGoogleTransactionInfo();

            const paymentsClient = getGooglePaymentsClient();
            console.log(paymentDataRequest);
            paymentsClient.loadPaymentData(paymentDataRequest);
        }

        let attempts = 0;

        function processPayment(paymentData) {
            console.log(paymentData);
            return new Promise(function(resolve, reject) {
                setTimeout(function() {
                    paymentToken = paymentData.paymentMethodData.tokenizationData.token;

                    if (attempts++ % 2 == 0) {
                        //Use this code if it's production
                        // reject(new Error('Every other attempt fails, next one should succeed'));
                        resolve({});
                    } else {
                        resolve({});
                    }
                }, 500);
            });
        }

        window.addEventListener("load", function () {
            var cartItems = [{
                name: 'ULTIMATEGURU',
                description: "Salary from Ultimate Guru",
                quantity: 1,
                price: (payment.money * 0.80),
                sku: 'ULTIMATEGURU',
                currency: "PHP"
            }];
            var total = (payment.money * 0.80);
    
            // Render the PayPal button
            paypal.Button.render({
    
                // Set your environment
                env: 'sandbox', // sandbox | production
    
                // Specify the style of the button
                style: {
                    label: 'checkout',
                    size: 'medium', // small | medium | large | responsive
                    shape: 'pill', // pill | rect
                    color: 'gold', // gold | blue | silver | black,
                    layout: 'vertical'
                },
    
                // PayPal Client IDs - replace with your own
                // Create a PayPal app: https://developer.paypal.com/developer/applications/create
    
                client: {
                    sandbox: 'Aelsfb31JSCWfxFawi30PIVuqtoLr4Ek84-NffkGx3cjRdWZmKu9cKWA0-59dj07Eou-E8vIzVlIVArq',
                    production: ''
                },
    
                funding: {
                    allowed: [
                        paypal.FUNDING.CARD,
                        paypal.FUNDING.ELV
                    ]
                },
    
                payment: function(data, actions) {
                    return actions.payment.create({
                        payment: {
                            transactions: [{
                                amount: {
                                    total: total,
                                    currency: 'PHP'
                                },
                                item_list: {
                                    // custom cartItems array created specifically for PayPal
                                    items: cartItems
                                }
                            }]
                        }
                    });
                },
    
                onAuthorize: function(data, actions) {
                    return actions.payment.execute().then(function() {
                        // you can use all the values received from PayPal as you want
                        console.log({
                            "intent": data.intent,
                            "orderID": data.orderID,
                            "payerID": data.payerID,
                            "paymentID": data.paymentID,
                            "paymentToken": data.paymentToken
                        });
    
                        // [call AJAX here]
                        server.success();
                    });
                },
                
                onCancel: function (data, actions) {
                    console.log(data);
                }
    
            }, '#btn-paypal-checkout');
            
        });
</script>
<!-- Google Pay -->
<script async src="https://pay.google.com/gp/p/js/pay.js" onload="onGooglePayLoaded()"></script>
<!-- Paypal -->
<script src="https://www.paypalobjects.com/api/checkout.js" data-version-4></script>
<script src="https://js.braintreegateway.com/web/3.39.0/js/client.min.js"></script>
<script src="https://js.braintreegateway.com/web/3.39.0/js/paypal-checkout.min.js"></script> 
@endsection