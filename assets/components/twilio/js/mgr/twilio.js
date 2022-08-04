var Twilio = function (config) {
    config = config || {};
    Twilio.superclass.constructor.call(this,config);
};
Ext.extend(Twilio, Ext.Component, {
    page:{},window:{},grid:{},tree:{},panel:{},combo:{},config: {}
});
Ext.reg('twilio',Twilio);
twilio = new Twilio();
