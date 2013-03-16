define([
	'jquery',
	'underscore',
	'backbone',
    'models/app',
    'collections/threadlines', 'views/threadlines'
	], function($, _, Backbone, App, ThreadLinesCollection, ThreadLineView) {

        'use strict';

		var ThreadView = Backbone.View.extend({

			className: 'thread_box',

			events: {
				"click .btn-threadCollapse":  "collapseThread",
				"click .js-btn-openAllThreadlines": "openAllThreadlines",
				"click .js-btn-closeAllThreadlines": "closeAllThreadlines",
				"click .js-btn-showAllNewThreadlines": "showAllNewThreadlines"
			},

			initialize: function(options){
                this.postings = options.postings;

				if (this.model.get('isThreadCollapsed')) {
					this.hide();
				} else {
					this.show();
				}

                this.listenTo(App.eventBus, 'newEntry', this._newEntry);
                this.model.on('change:isThreadCollapsed', this.toggleCollapseThread, this);
			},

            _newEntry: function(options) {
                var newEl,
                    html;

                // only append to the id it belongs to
                if (options.tid !== this.model.get('id')) { return; }

                $.ajax({
                    async: false,
                    url: App.settings.get('webroot') +
                        'entries/threadLine/' +
                        options.id,
                    dataType: 'json',
                    success: function(data) {
                        html = data.html;
                    }
                });

                newEl = this._insertAfter(html, options.pid);

                new ThreadLineView({
                    el: $(newEl).find('.js-thread_line'),
                    collection: this.model.threadlines,
                    postings: this.postings
                });
            },

            // @bogus
            _insertAfter: function(html, threadline_id) {
                return $('<li>'+html+'</li>').
                    insertAfter('#ul_thread_' + threadline_id + ' > li:last-child');
            },


			/**
			 * Opens all threadlines
			 */
			openAllThreadlines: function(event) {
				event.preventDefault();
				this.model.set({ isInlineOpened: true });
				_.each(
					this.model.threadlines.where({
						isInlineOpened: false
					}), function(model) {
						model.set({
							isInlineOpened: true
						});
					}, this);

			},

			/**
			 * Closes all threadlines
			 */
			closeAllThreadlines: function(event) {
				if(event) {
					event.preventDefault();
				}
				_.each(
					this.model.threadlines.where({
						isInlineOpened: true
					}), function(model) {
						model.set({
							isInlineOpened: false
						});
					}, this);
			},

			/**
			 * Toggles all threads marked as unread/new in a thread tree
			 */
			showAllNewThreadlines: function(event) {
				event.preventDefault();
				this.model.set({ isInlineOpened: true });
				_.each(
					this.model.threadlines.where({
						isInlineOpened: false,
						isNewToUser: true
					}), function(model) {
						model.set({
							isInlineOpened: true
						});
					}, this);
			},

			collapseThread: function(event) {
				event.preventDefault();
				this.closeAllThreadlines();
				this.model.toggleCollapseThread();
				this.model.save();
			},

			toggleCollapseThread: function(model, isThreadCollapsed) {
				if(isThreadCollapsed) {
					this.slideUp();
				} else {
					this.slideDown();
				}
			},

			slideUp: function() {
				$(this.el).find('ul.thread > li:not(:first-child)').slideUp(300);
				this.markHidden();
			},

			slideDown: function() {
				$(this.el).find('ul.thread > li:not(:first-child)').slideDown(300);
				this.markShown();
//				$(this.el).find('.ico-threadOpen').removeClass('ico-threadOpen').addClass('ico-threadCollapse');
//				$(this.el).find('.btn-threadCollapse').html(this.l18n_threadCollapse);
			},

			hide: function() {
				$(this.el).find('ul.thread > li:not(:first-child)').hide();
				this.markHidden();
			},

			show: function() {
				$(this.el).find('ul.thread > li:not(:first-child)').show();
				this.markShown();
			},

			markShown: function() {
				$(this.el).find('.icon-thread-closed').removeClass('icon-thread-closed').addClass('icon-thread-open');
			},

			markHidden: function() {
				$(this.el).find('.icon-thread-open').removeClass('icon-thread-open').addClass('icon-thread-closed');
				// this.l18n_threadCollapse = $(this.el).find('.btn-threadCollapse').html();
				// $(this.el).find('.btn-threadCollapse').prepend('&bull;');
			}

		});

		return ThreadView;

	});