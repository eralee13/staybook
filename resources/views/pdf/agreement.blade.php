<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<head>
    <title>StayBook</title>

</head>
<body>

<div class="page admin">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <p>Acceptance Date: {{ date('d.m.Y') }}</p>
                <h2>DISTRIBUTION AGREEMENT № {{ Auth::user()->id }}</h2>
                <p>The Hotel Supplier hereby appoints the Distributor to promote and arrange the distribution of the
                    Hotel Services provided to Clients at the Accommodation Facilities. The Distributor may distribute
                    the Services on a standalone basis or as part of the Travel Package.</p>
                <p>Cooperation between the Parties is carried out via the Extranet, a hotel inventory management
                    system accessible via the link:.</p>
                <p>The relationship between the Parties is governed by the Rules and Procedures published on the
                    Extranet and constituting its integral part. The term “Agreement” refers jointly to this Agreement
                    and the Rules and Procedures.</p>
                <p>The Agreement is concluded in a special manner, without signing by the parties, and comes into
                    force from the registration of the Hotel Supplier on the Extranet. By placing the Information on the
                    Extranet, the Hotel Supplier consents to the terms of the Agreement. If the Hotel Supplier does not
                    agree with any of the terms, it shall immediately cease using the Extranet.</p>
                <p><b>Rates:</b> The Hotel Supplier indicates the following types of Rates on the Extranet:</p>
                <p>(A) Post-pay Rates for the Client’s payment at the Accommodation Facility;</p>
                <p>(B) Pre-pay Rates for the Client’s payment on the Website.</p>
                <p>Rates must include all applicable Taxes, except for the taxes that the Client is charged at the
                    Accommodation Facility according to applicable legislation.</p>
                <p><b>The Distributor’s Remuneration</b> under the Agreement is set as follows:</p>
                <ol>
                    <li>If the Parties have not agreed otherwise, the amount of Distributor’s Remuneration for each
                        Reservation under the Pre-pay Rates may comprise from 1% to 18.0% of the Reservation Price.
                    </li>
                    <ul>
                        <li>The Distributor may from time to time provide the Hotel Supplier the ability to upload to
                            the Extranet Reservation Prices without the Distributor’s Remuneration included in the
                            Reservation Price (the “Net Rate Reservation Price”). If the Hotel Supplier uploads to
                            the Extranet the Net Rate Reservation Prices, the amount of the Distributor’s Remuneration
                            shall comprise the difference between the Reservation Price and the Net Rate Reservation
                            Price.
                        </li>
                    </ul>
                    <li>If the Parties have not agreed otherwise, the amount of the Distributor’s Remuneration for
                        each Booking under the Post-pay Rates shall comprise 18.0% of the Reservation Price.
                    </li>
                </ol>
                <h2>DETAILS OF THE PARTIES</h2>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h4>user</h4>
                    <h4>{{ Auth::user()->name }}</h4>
                </div>
                <div class="col-md-6">
                    <h4>DISTRIBUTOR</h4>
                    <h4>“StayBook” LLC</h4>
                    <p>Legal address: 1В, Ashar Street, 720077,
                        Bishkek, Kyrgyz Republic</p>
                    <p><b>Address for correspondence:</b> 86, Umetaliev
                        Street, 720001, Bishkek, Kyrgyz Republic</p>
                    <p><b>VAT number:</b> 02010201610086</p>
                    <p><b>Bank details:</b></p>
                    <p>Bank Name: “Bank Optima” JSC, Bishkek,
                        Kyrgyz Republic</p>
                    <p>SWIFT CODE: ENEJKG22</p>
                    <p>Account number: 1090820060580145</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .page {
        padding: 30px 0;
    }
    h2{
        text-align: center;
    }
    .col-md-6{
        width: 48%;
        display: inline-block;
    }
    body {
        font-family: DejaVu Sans
    }
</style>

</body>
</html>