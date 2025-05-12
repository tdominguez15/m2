define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Ui/js/form/form'
], function ($, ko, Component, ComponentParent) {
    'use strict';

    var self = null;

    return ComponentParent.extend({
        initialize: function () {
            this._super();
            this.ajaxSave = true;
            const s = ko.observable('responseData');
            s.subscribe(function () {
                console.log('asdasd');
            });
            self = this;
            return this;
        },
        responseData: function (data) {
            self.reset();

            const blob = new Blob([data.result], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const pom = document.createElement('a');
            pom.href = url;
            pom.setAttribute('download', 'report.csv');
            pom.click();
        },
    });
});
