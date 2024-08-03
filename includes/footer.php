<style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }
        main.container {
            flex: 1;
        }
        #footer-bottom {
            background-color: #f8f9fa;
            padding: 20px;
            border-top: 1px solid #ddd;
        }
        .container1 {
            max-width: 1200px;
            margin: 0 auto;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .col-md-4 {
            flex: 1;
            padding: 10px;
        }
        .card-wrap img {
            max-width: 100%;
            height: auto;
            margin-right: 10px;
        }
        .copyright {
            color: #717171;
            text-align: center;
        }
        .copyright a {
            color: #AEAEAE;
            text-decoration: none;
        }
        .copyright a:hover {
            text-decoration: underline;
        }
</style>
<div id="footer-bottom">
        <div class="container1">
            <div class="row d-flex flex-wrap justify-content-between">
                <div class="col-md-4 col-sm-6">
                    <div class="Shipping d-flex">
                        <p>We ship with:</p>
                        <div class="card-wrap ps-2">
                            <img src="images/dhl.png" alt="dhl">
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="payment-method d-flex">
                        <p>Payment options:</p>
                        <div class="card-wrap ps-2">
                            <img src="images/visa.jpg" alt="visa">
                            <img src="images/mastercard.jpg" alt="mastercard">
                            <img src="images/paypal.jpg" alt="paypal">
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="copyright">
                        <p>© Copyright 2024 MacStore. 
                        <a href="contact.html">Contact Us</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>