define([
  'jquery',
  'underscore',
  'backbone',
  'models/app',
  'views/uploads', 'views/mediaInsert',
  'views/editCountdown',
  'models/preview', 'views/preview',
  'lib/saito/jquery.scrollIntoView',
  'jqueryAutosize'
], function($, _, Backbone, App, UploadsView, MediaInsertView, EditCountdown,
            PreviewModel, PreviewView) {
  'use strict';

  var AnsweringView = Backbone.View.extend({

    _requestUrl: null,

    rendered: false,

    answeringForm: false,

    preview: false,

    mediaView: false,

    sendInProgress: false,

    /**
     * current action
     *
     * either 'edit' or 'add'
     */
    _action: null,

    /**
     * same model as the parent PostingView
     */
    model: null,

    events: {
      "click .btn-previewClose": "_closePreview",
      "click .btn-preview": "_showPreview",
      "click .btn-markItUp-Upload": "_upload",
      "click .btn-markItUp-Media": "_media",
      "click .btn-submit": "_send",
      "click .btn-cite": "_cite",
      "keypress .js-subject": "_onKeyPressSubject"
    },

    initialize: function(options) {
      this.parentThreadline = options.parentThreadline || null;

      this._setupTextArea();
      if (!this.parentThreadline) {
        //* view came directly from server and is ready without rendering
        this._onFormReady();
      }
      this._requestUrl = App.settings.get('webroot') +
        'entries/add/' + this.model.get('id');

      // focus can only be set after element is visible in page
      this.listenTo(App.eventBus, "isAppVisible", this._focusSubject);

      // auto-open upload view for easy developing
      // this._upload();
    },

    _disable: function() {
      this.$('.btn.btn-submit').attr('disabled', 'disabled');
    },

    _enable:  function() {
      this.$('.btn.btn-submit').removeAttr('disabled');
    },

    _cite: function(event) {
      event.preventDefault();
      var citeContainer = this.$('.cite-container'),
          citeText = this.$('.btn-cite').data('text'),
          currentText = this.$textarea.val();

      this.$textarea.val(citeText + "\n\n" + currentText);
      citeContainer.slideToggle();
      this.$textarea.trigger('autosize.resize');
      this.$textarea.focus();
    },

    _onKeyPressSubject: function(event) {
      // intercepts sending to form's action url when inline answering
      if (event.keyCode === 13) {
        this._send(event);
      }
    },

    _upload: function(event) {
      var uploadsView;
      if (event) {
        event.preventDefault();
      }
      uploadsView = new UploadsView({
        el: '#markitup_upload',
        textarea: this.$textarea[0]
      });
    },

    _media: function(event) {
      event.preventDefault();

      if (this.mediaView === false) {
        this.mediaView = new MediaInsertView({
          el: '#markitup_media',
          model: this.model
        });
      }
      this.mediaView.render();
    },

    _showPreview: function(event) {
      var previewModel;
      event.preventDefault();
      this.$('.preview').slideDown('fast');
      if (this.preview === false) {
        previewModel = new PreviewModel();
        this.preview = new PreviewView({
          el: this.$('.preview .panel-content'),
          model: previewModel
        });
      }
      this.preview.model.set('data', this.$('form').serialize());
    },

    _closePreview: function(event) {
      event.preventDefault();
      this.$('.preview').slideUp('fast');
    },

    _setupTextArea: function() {
      this.$textarea = this.$('textarea#EntryText');
      this.$textarea.autosize();
    },

    _requestAnsweringForm: function() {
      $.ajax({
        // don't append timestamp to _requestUrl or Cake's
        // SecurityComponent will blackhole the ajax call in _sendInline()
        cache: true,
        url: this._requestUrl,
        success: _.bind(function(data) {
          this.answeringForm = data;
          this.render();
        }, this)
      });
    },

    _postRendering: function() {
      this.$el.scrollIntoView('bottom');
      this._focusSubject();
      this._onFormReady();
    },

    _onFormReady: function() {
      this._setupTextArea();

      var _$data = this.$('.js-data');
      if (_$data.length > 0 && _$data.data('meta').action === 'edit') {
        var _entry = this.$('.js-data').data('entry');
        this.model.set(_entry, {silent: true});
        this._addCountdown();
      }
      App.eventBus.trigger('change:DOM');
    },

    /**
     * Adds countdown to Submit button
     *
     * @private
     */
    _addCountdown: function() {
      var _$submitButton = this.$('.js-btn-submit');
      var editCountdown = new EditCountdown({
        el: _$submitButton,
        model: this.model,
        editPeriod: App.settings.get('editPeriod'),
        done: 'disable'
      });
    },

    _focusSubject: function() {
      this.$('.postingform input[type=text]:first').focus();
    },

    _send: function(event) {
      if (this.sendInProgress) {
        event.preventDefault();
        return;
      }
      this.sendInProgress = true;
      if (this.parentThreadline) {
        this._sendInline(event);
      } else {
        this._sendRedirect(event);
      }
    },

    _sendRedirect: function(event) {
      var button = this.$('.btn-submit')[0];
      event.preventDefault();
      if (typeof button.validity === 'object' &&
          button.form.checkValidity() === false) {
        // we can't trigger JS validation messages via form.submit()
        // so we create and click this hidden dummy submit button
        var submit = _.bind(function() {
          if (!this.checkValidityDummy) {
            this.checkValidityDummy = $('<button></button>', {
              type: 'submit',
              style: 'display: none;'
            });
            $(button).after(this.checkValidityDummy);
          }
          this.checkValidityDummy.click();
        }, this);

        submit();
        this.sendInProgress = false;
      } else {
        button.disabled = true;
        button.form.submit();
      }
    },

    _sendInline: function(event) {
      event.preventDefault();
      var data = this.$('#EntryAddForm').serialize();
      var success = _.bind(function(data) {
        this.model.set({isAnsweringFormShown: false});
        if (this.parentThreadline !== null) {
          this.parentThreadline.set('isInlineOpened', false);
        }
        App.eventBus.trigger('newEntry', {
          tid: data.tid,
          pid: this.model.get('id'),
          id: data.id,
          isNewToUser: true
        });
      }, this);
      var fail = _.bind(function(jqXHR, text) {
        this.sendInProgress = false;
        this._enable();
        App.eventBus.trigger('notification', {
          title: text,
          type: 'error',
          message: jqXHR.responseText
        });
      }, this);
      var disable = _.bind(this._disable, this);

      $.ajax({
        url: this._requestUrl,
        type: 'POST',
        dataType: 'json',
        data: data, beforeSend: disable
      }).done(success).fail(fail);
    },

    render: function() {
      if (this.answeringForm === false) {
        this._requestAnsweringForm();
      } else if (this.rendered === false) {
        this.rendered = true;
        this.$el.html(this.answeringForm);
        _.defer(function(caller) {
          caller._postRendering();
        }, this);
      } else {
        App.eventBus.trigger('change:DOM');
      }
      return this;
    }

  });

  return AnsweringView;

});
