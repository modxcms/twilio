Ext.onReady(function() {
    var div = Ext.get('modx-panel-profile-update');
    Ext.DomHelper.insertAfter(div, '<div class="x-form-item x-tab-item x-form-element">'
        + '<label for="qrcode" style="width:auto;" class="x-form-item-label">'+
        twilio.qrText
        +':\n\</label></div>'
        + '<div id="qrcode"><img id="qrimg" src="" width="350" height="350"></div>');
    MODx.Ajax.request({
        url: twilio.config.connector_url,
        params:{action:'MODX\\Twilio\\Processors\\TOTP\\QR', user: twilio.config.user.user},
        listeners:{
            'success':{fn:function(r){ document.getElementById("qrimg").src = r.object.qr;},scope:this },
            'failure':{fn:function(){  },scope:this}}});
});
