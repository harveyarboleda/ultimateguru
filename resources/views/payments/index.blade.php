@extends('layouts.app')

@section('content')
    <style>
        fieldset, legend {
            all: revert;
        }
    </style>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-6">
                <fieldset class="mb-2" style="font-size: 11px;">
                    <legend style="font-size: 12px;font-weight:bold;">Order Detail</legend>
                    Transaction ID: <br/>
                    Issue Date: July 20,2022 <br/>
                    Paid by: Harvey User <br/>
                    Received by: Harvey Tutor <br/>
                    Service: 30 Minutes Tutorial on Mathematics <br/>
                    Amount Due: P50.00 <br/>
                </fieldset>
            </div>
            <div class="col-4" style="text-align:center;">
                <fieldset class="mb-2">
                    <legend style="font-size: 12px;text-align:left;font-weight:bold;">Choose payment method</legend>
                    <div id="pay-now"></div>
                    <br/>
                    <div id="btn-paypal-checkout"></div>
                </fieldset>
                or
                <div class="d-grid gap-2 mt-2">
                    <button class="btn btn-primary" onclick="server.cancel()">Cancel</button>
                </div>
            </div>
            
        </div>
    </div>
    <script type="text/javascript">
        const payment = {
            'money': 0
        };

        class UltimateGuru {
            time = "0";
            timestamp = 0;
            overall_total = 0;
            constructor() {
                this.time = "0";
                this.timestamp = 0;
                this.overall_total = 0;
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
                if(data.is_pay == 1) {
                    window.location.reload();
                }
                if(this.overall_total != data.overall_total) {
                    this.overall_total = data.overall_total;
                    payment.money = this.overall_total;
                }
                console.log("Done loading.");
            }
            async success() {
                let url = await server.sendGetRequest("/successPlan", JSON.stringify([]));
                let data = JSON.parse(JSON.stringify(url))
                this.alert("success","Success!","Please wait...");
                window.location.reload();
            }
            async error() {
                this.alert("error","Error!","There\'s something wrong.");
            }
            async paymentMade(orderID, payerID, paymentID, paymentToken) {
                var array = {};
                array['orderID'] = orderID;
                array['payerID'] = payerID;
                array['paymentID'] = paymentID;
                array['paymentToken'] = paymentToken;

                let url = await server.sendPostRequest("/successPlan", JSON.stringify(array));

                console.log(url)
            
                if (url.status == "success") {
                    this.alert("success","Successfully!",url.message);
                } else {
                    this.alert("error","Error!",url.message);
                }
            }
            async cancel() {
                let url = await server.sendGetRequest("/cancelPlan", JSON.stringify([]));
                let data = JSON.parse(JSON.stringify(url))
                this.alert("success","Success!","Please wait...");
                window.location.reload();
            }
        }
        
        const server = new UltimateGuru();
        
        setInterval(() => {
            server.check();
        }, 5000);

        server.check();

        

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
                merchantName: 'Ultimate Guru'
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
                name: '<?php echo $datas[0]->category_name; ?>',
                description: "<?php echo str_replace("\r\n",",",$datas[0]->category_desc); ?>",
                quantity: 1,
                price: <?php echo $datas[0]->price; ?>,
                sku: '<?php echo $datas[0]->category_name; ?>',
                currency: "PHP"
            }];
    
            var total = 0;
            for (var a = 0; a < cartItems.length; a++) {
                total += (cartItems[a].price * cartItems[a].quantity);
            }
    
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
                        server.paymentMade(data.orderID, data.payerID, data.paymentID, data.paymentToken);
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