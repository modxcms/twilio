Ext.onReady(function() {
    var div = document.getElementById('modx-panel-profile-update');
    div.innerHTML = div.innerHTML + '<div class="x-form-item x-tab-item x-form-element">'
        + '<label for="qrcode" style="width:auto;" class="x-form-item-label">'+
        twilio.qrText
        +':\n\</label></div>'
        + '<div id="qrcode"><img id="qrimg" src=""></div>';
    MODx.Ajax.request({
        url: twilio.config.connector_url,
        params:{action:'totp/qr', user: twilio.config.user.user},
        listeners:{
            'success':{fn:function(r){ document.getElementById("qrimg").src = r.object.qr;},scope:this },
            'failure':{fn:function(){  },scope:this}}});
});
