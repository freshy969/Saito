define([
    'jquery',
    'underscore',
    'backbone',
    'jqueryAutosize'
], function($, _, Backbone, jqueryAutosize) {

    var ShoutboxView = Backbone.View.extend({

        refreshTimeBase: 5000,
        refreshTimeMax: 30000,

        lastId: 0,

        events: {
            "keydown form": "form"
        },

        initialize: function(options) {
            this.urlBase = options.urlBase + 'shouts/';
            this.shouts = this.$el.find('.shouts');
            this.textarea =  this.$el.find('textarea');
            this.refreshTimeAct = this.refreshTimeBase;

            this.textarea.autosize();
            this.poll();
        },

        form: function(event) {
            if (event.keyCode == 13 && event.shiftKey === false) {
                this.submit();
                this.clearForm();
                event.preventDefault();
            }
        },

        clearForm: function() {
            this.textarea.val('').trigger('autosize');
        },

        submit: function() {
            $.ajax({
                url: this.urlBase + 'add',
                type: "post",
                data: {
                   text: this.textarea.val()
                },
                success: _.bind(function(data) {
                    clearTimeout(this.timeoutId);
                    this.poll(data);
                }, this)
            });
        },

        poll: function() {

            this.timeoutId = setTimeout(_.bind(this.poll, this), this.refreshTimeAct);

            // update shoutbox only if tab is open
            if(this.$el.is(":visible") === false) {
                return;
            }

            $.ajax({
                url: this.urlBase + 'index',
                data: {
                    lastId: this.lastId
                },
                method: 'post',
                dataType: 'html',
                success: _.bind(function(data) {
                    if (data.length > 0) {
                        this.render(data);
                        this.lastId = $(data).find('.shout:first').data('id');
                        this.refreshTimeAct = this.refreshTimeBase;
                    } else {
                        this.refreshTimeAct = Math.floor(this.refreshTimeAct * (1 + this.refreshTimeAct/40000))
                        if (this.refreshTimeAct > this.refreshTimeMax) {
                            this.refreshTimeAct = this.refreshTimeMax
                        }
                    }
                }, this)
            });
        },

        render: function(data) {
            $(this.shouts).html(data);
            return this;
        }

    });

    return ShoutboxView;
});