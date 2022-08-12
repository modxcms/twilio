# Twilio Verify

Twilio is a verification service that allows you to send a code to a user's phone or use a time-based one time password to verify their identity. You can find more information on Twilio at [https://www.twilio.com/](https://www.twilio.com/). This is not a free service, so you will need to sign up for a Twilio account. Each successful verification will cost you $0.05, so this is something to consider before implementing.

## Phone Verification 

###  Sample Registration page

```html
<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css"
/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>

[[!Register?
    &postHooks=`TwilioActivation`
    &submitVar=`registerbtn`
    &moderate=`1`
    &customValidators=`TwilioValidatePhone`
    &twilioActivationResourceId=`2`
    &twilioActivationEmailTpl=`myActivationEmailTpl`
    &usernameField=`email`
    &validate=`nospam:blank,
  phone:TwilioValidatePhone,
  password:required:minLength=^6^,
  password_confirm:password_confirm=^password^,
  email:required:email`
    &placeholderPrefix=`reg.`
]]

<div class="register">
    <div class="registerMessage">[[!+reg.error.message]]</div>

    <form class="form" action="[[~[[*id]]]]" method="post">
        <input type="hidden" name="nospam" value="[[!+reg.nospam]]" />

        <label for="email">Email
            <span class="error">[[!+reg.error.email]]</span>
        </label>
        <input type="text" name="email" id="email" value="[[!+reg.email]]" />

        <label for="phone">Phone
            <span class="error">[[!+reg.error.phone]]</span>
        </label>
        <input type="text" id="phone" value="[[!+reg.phone]]" />
        
        <label for="password">Password
            <span class="error">[[!+reg.error.password]]</span>
        </label>
        <input type="password" name="password" id="password" value="[[!+reg.password]]" />

        <label for="password_confirm">Confirm Password
            <span class="error">[[!+reg.error.password_confirm]]</span>
        </label>
        <input type="password" name="password_confirm" id="password_confirm" value="[[!+reg.password_confirm]]" />

        <div class="form-buttons">
            <input type="submit" name="registerbtn" value="Register" />
        </div>
    </form>
</div>

<script>
    var phoneInputField = document.querySelector("#phone");
    window.intlTelInput(phoneInputField, {
        hiddenInput: "phone",
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
    });
</script>
```

### Sample Activation Page (&twilioActivationResourceId)

```html
[[!TwilioGetPhone]]

[[!FormIt?
   &hooks=`TwilioSendVerification`
   &submitVar=`get-validation`
   &validate=`channel:required`
]]

Phone: [[!+twilio.phone]]
<form action="" method="post" class="form">
<label>Channel: [[!+fi.error.channel]]</label>
<input type="radio" name="channel" value="sms" [[!+fi.channel:FormItIsChecked=`sms`]] > SMS
<input type="radio" name="channel" value="call" [[!+fi.channel:FormItIsChecked=`call`]] > Call
    <div class="form-buttons">
        <input type="submit" name="get-validation" value="Validate my phone" />
    </div>
</form>

[[!+twilio.code_sent]]

[[!FormIt?
   &hooks=`TwilioVerify`
   &twilioRedirect=`4`
   &placeholderPrefix=`fiv.`
   &submitVar=`verify`
   &validate=`code:required`
]]

<form action="" method="post" class="form">
    <label for="code">
        Code:
        <span class="error">[[!+fiv.error.code]]</span>
    </label>
    <input type="text" name="code" id="code" value="[[!+fiv.code]]" />
    <div class="form-buttons">
        <input type="submit" name="verify" value="Validate my phone" />
    </div>
</form>
```

## Time-based One Time Password

### Sample Challenge Page

Create a challenge page and set the system setting `twilio.totp_challenge_page` to the page ID.

```html
[[!FormIt?
    &hooks=`TwilioTOTPChallenge,TwilioVerify`
    &twilioRedirect=`4` // ID of the page to redirect to after verification
    &twilioFactorType=`totp`
    &validate=`code:required`
]]
<form method="post">
    <label>
        Enter 2FA Code
        <input name="code" value="" />
    </label>
    <button type="submit">Submit</button>
</form>
```

### Sample Create/Reset Token Page

Create a page with the following content:

```html
[[TwilioTOTPCreate?twilioRedirect=`4`]]
```

### Sample Profile Page

```html
[[!TwilioTOTPqr]]

[[!+twilio.qr:ne=``:then=`
    <img src="[[!+twilio.qr]]" />
    <p>Secret [[!+twilio.secret]]</p>
    [[!+twilio.status:is=`unverified`:then=`
        <p><a href="[[~5]]"><strong>Verify 2FA Code Before Next Login</strong></a></p> <!-- link to challenge page -->
    `:else=``]]
    <p><a href="[[~6]]">Refresh 2FA</a><br /> <!-- link to create / refresh page -->
    <a href="[[~6?status=`disable_totp`]]">Disable 2FA</a></p>
`:else=`
    <a href="[[~6]]">Enable 2FA</a>
`]]
```

## System Settings

| key | description                                                                                                |
| --- |------------------------------------------------------------------------------------------------------------|
| twilio.account_sid | Twilio Account SID - Found under Account Info here https://console.twilio.com/                             |
| twilio.account_token | Twilio Auth Token - Found under Account Info here https://console.twilio.com/                              |
| twilio.service_id | Twilio Service ID - Found under Services Page here https://console.twilio.com/us1/develop/verify/services  |
| twilio.totp_enforce | Enforce 2FA for all users                                                                                  |
| twilio.totp_email_on_login | Email a code to the user when they login                                                                   |
| twilio.totp_challenge_page | Page ID of the challenge page                                                                              |

## Manager Page

Twilio 2FA Verification can be enabled in the manager login as well. You can view the status of Twilio 2FA for each user in the menu under 
"Extras -> User Authentication"
