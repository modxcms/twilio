# Twilio

## Sample Registration page

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

## Sample Activation Page (&twilioActivationResourceId)

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