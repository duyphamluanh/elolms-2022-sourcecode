// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A javascript module that handles Mail to user in the
 * elo reminder users block.
 *
 * @module     block_elo_reminder_users/mail_to_user
 * @package    block_elo_reminder_users
 * @copyright  2018 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/str', 'core/notification', 'core/modal_factory', 'core/modal_events', 'core/templates'],
        function($, Ajax, Str, Notification, ModalFactory, ModalEvents, Templates) {
    
    var modalShowSingle = function(btnSend){
        var trigger = $('#create-modal');
        var email = (btnSend.attr('data-email'));
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: getTitleModal('title',''),
            body: getTitleModal('body',email),
        }, trigger)
        // .then(function(modal) {
        //         modal.setSaveButtonText('Delete');
        //         var root = modal.getRoot();
        //         root.on(ModalEvents.save, function() {
        //             var elementid = clickedLink.data('id');
        //             // Do something to delete item
        //         });
        //         modal.show();
        // })
        .done(function(modal) {
            modal.setSaveButtonText(getTitleModal('confirm',''));
            modal.getRoot().on(ModalEvents.save, function(e) {
                // Stop the default save button behaviour which is to close the modal.
                e.preventDefault();
                modal.hide();

                actionBtns(btnSend);
                // Do your form validation here.
            }).on(ModalEvents.hidden, function() {
                // Destroy when hidden.
                modal.destroy();
            });
            modal.show();
        });
    };
    
    var modalShowMultiple = function(users){

        if (!users || users.length == 0) {
            return false;
        }

        var trigger = $('#create-modal');
        var emails = [];
        var btnSends = [];


        $eleSend = $(SELECTORS.MAIL_TO_USER_LINK);

        for(var i = 0; i < $eleSend.length; i++){
            var btnSend = $($eleSend[i]);
            var action = (btnSend.attr('data-action'));
            var userid = (btnSend.attr('data-userid'));
            if(users.includes(userid)  && (action=='send' || action=='resend')){
                emails.push(btnSend.attr('data-email'));
                btnSends.push(btnSend);
            }
        }

        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: getTitleModal('title',''),
            body: getTitleModal('body:multiple','<br>' + emails.join('; <br>')),
        }, trigger)
        .done(function(modal) {
            modal.setSaveButtonText(getTitleModal('confirm',''));
            modal.getRoot().on(ModalEvents.save, function(e) {
                // Stop the default save button behaviour which is to close the modal.
                e.preventDefault();
                modal.hide();

                for(var i = 0; i < btnSends.length; i++){
                    actionBtns(btnSends[i]);
                }
                // Do your form validation here.
            }).on(ModalEvents.hidden, function() {
                // Destroy when hidden.
                modal.destroy();
            });
            modal.show();
        });
    };
    
    var modalShowHistories = function(userid,courseid){

        if (!userid || userid.length == 0) {
            return false;
        }
        var trigger = $('#create-modal');
        ModalFactory.create({
            type: ModalFactory.types.CANCEL,
            title: getTitleModal('title:viewhistories',''),
            body: '',
        }, trigger)
        .done(function(modal) {
          modal.show();

          var request = {
              methodname: 'block_elo_reminder_users_viewhistories',
              args: {userid: userid, courseid: courseid},
          };

          Ajax.call([request])[0].then(function(response) {
              response = JSON.parse(response.elodata);
              if (response.success !== undefined) {

                var html = '<div class="block"><table class="flexible elo_flexible_custom"><thead>';
                for(var key in response.success.data.columns){
                  html += '<th class="header">' + response.success.data.columns[key] +'</th>';
                }
                html += '</thead><tbody>';
                for(var key in response.success.data.listusers){
                  var user = response.success.data.listusers[key];
                  var date = new Date(user.timemodified*1000);
                  var formattedDate = ('0' + date.getDate()).slice(-2) + '/' + ('0' + (date.getMonth() + 1)).slice(-2) + '/' + date.getFullYear() + ' ' + ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2);
                  html += '<tr>';
                  html += '<td>' + user.rownum + '</td>';
                  html += '<td>' + user.firstname + ' '  + user.lastname + '</td>';
                  html += '<td>' + user.coursefullname + '</td>';
                  html += '<td>' + formattedDate + '</td>';
                  html += '</tr>';
                }
                html += '</tbody></table></div>';
                modal.setBody(html);
              }
              else if (response.error !== undefined) {
              }
              return;
          }).catch(function(ex){
              Notification.exception(ex);
          });

          // Handle hidden event.
          modal.getRoot().on(ModalEvents.hidden, function() {
                // Destroy when hidden.
                modal.destroy();
          });

        });
    };

    /**
     * Selectors.
     *
     * @access private
     * @type {Object}
     */
    var SELECTORS = {
        MAIL_TO_USER_LINK: '.mail-to-user',
        MAIL_TO_USER_ICON: '.mail-to-user .icon',
        BULKACTIONSELECT: "#beruformactionid",
        BULKUSERCHECKBOXES: "input.beruusercheckbox",
        BULKUSERNOSCHECKBOXES: "input.beruusercheckbox[value='0']",
        BULKUSERSELECTEDCHECKBOXES: "input.beruusercheckbox:checked",
        BULKACTIONFORM: "#beruparticipantsform",
        CHECKALLONPAGEBUTTON: "#berucheckallonpage",
        CHECKNONEBUTTON: "#beruchecknone",
        VIEWHISTORIES: ".viewhistories"
    };

    /**
     * Set Reset button.
     *
     * @method sendMailFailed
     * @param {Element} btnSend
     * @param {Element} btnSending
     * @private
     */
    var sendMailFailed = function(btnSend,btnSending){
        if(btnSending !== undefined){
            btnSending.remove();
        }

        var newAction = 'failed';
        mailToUserLinkAttr(oppositeAction(newAction),btnSend);
        mailToUserIconAttr(newAction,btnSend,btnSending);
        // btnSend.show();
    };

    /**
     * Mail to user in the elo reminder users block.
     *
     * @method mailToUser
     * @param {String} action
     * @param {String} userid
     * @private
     */
    var mailToUser = function(action, userid, lastaccess, courseid, btnSend, btnSending) {

        var value = valueAction(action);

        var request = {
            methodname: 'block_elo_reminder_users_mailtouser',
            args: {value: value, userid: userid, lastaccess: lastaccess, courseid: courseid},
        };

        Ajax.call([request])[0].then(function(response) {
            response = JSON.parse(response.elodata);
            if (response.success !== undefined) {
                
                var newAction = oppositeAction(action);
                mailToUserLinkAttr(oppositeAction(newAction),btnSend);
                mailToUserIconAttr(newAction,btnSend,btnSending);
            }
            else if (response.error !== undefined) {
                sendMailFailed(btnSend,btnSending);
                alert(response.error.message);
            }
            return;
        }).catch(function(ex){
            Notification.exception(ex);
            sendMailFailed(btnSend,btnSending);
        });
    };

    /**
     * Get the opposite action.
     *
     * @method oppositeAction
     * @param {String} action
     * @return {String}
     * @private
     */
    var valueAction = function(action) {
        switch(action) {
          case 'send':
            return 1;
          case 'sending':
            return 2;
          case 'failed':
            return 0;
          case 'resend':
            return 3;
          default:
            return 1;
        }
    };

    /**
     * Get the opposite action.
     *
     * @method oppositeAction
     * @param {String} action
     * @return {String}
     * @private
     */
    var oppositeAction = function(action) {
        switch(action) {
          case 'send':
            return 'sending';
          case 'sending':
            return 'sent';
          case 'failed':
            return 'resend';
          case 'resend':
            return 'sending';
          default:
            return 'send';
        }
    };

    /**
     * Change the attribute values of the user visibility link in the elo reminder users block.
     *
     * @method mailToUserLinkAttr
     * @param {String} action
     * @private
     */
    var mailToUserLinkAttr = function(action,btnSend) {
        getTitle(action).then(function(title) {
            // $(SELECTORS.MAIL_TO_USER_LINK).attr({
            $(btnSend).attr({
                'data-action': action,
                'title': title
            });
            return;
        }).catch(Notification.exception);
    };

    /**
     * Change the attribute values of the user visibility icon in the elo reminder users block.
     *
     * @method mailToUserIconAttr
     * @param {String} action
     * @private
     */
    var mailToUserIconAttr = function(action,btnSend,btnSending) {
        var icon = $(btnSend).find('.icon');//$(SELECTORS.MAIL_TO_USER_ICON);
        getTitle(oppositeAction(action)).then(function(title) {

            // Add the proper title to the icon.
            $(icon).attr({
                'title': title,
                'aria-label': title
            });
            // If the icon is an image.
            if (icon.is("img")) {
                $(icon).attr({
                    'src': M.util.image_url('t/' + action),
                    'alt': title
                });
            } else {
                // Add the new icon class and remove the old one.
                $(icon).addClass(getIconClass(action));
                // $(icon).removeClass(getIconClass(oppositeAction(action)));
                $(icon).removeClass(getIconRemoveClass(getIconClass(action)));
            }
            if(btnSending !== undefined){
                btnSending.remove();
            }
            btnSend.show();
            return;
        }).catch(function(ex){
            Notification.exception(ex);

            if(btnSending !== undefined){
                btnSending.remove();
            }
            btnSend.show();
        });
    };

    /**
     * Get the proper class for the user visibility icon in the elo reminder users block.
     *
     * @method getIconClass
     * @param {String} action
     * @return {String}
     * @private
     */
    var getIconClass = function(action) {
        // return action == 'show' ? 'fa-eye-slash' : 'fa-eye';
        //Nhien elo 10_09_2019
        //sending will return email icon to add
        //sent will return sendmessage to remove
        switch(action) {
          case 'sending':
            return 'fa-envelope-o';
          case 'sent':
            return 'fa-paper-plane';
          case 'failed':
            return 'fa-repeat';
          case 'resend':
            return 'fa-paper-plane';
          default:
            return 'fa-paper-plane';
        }
        //End Nhien elo 10_09_2019
    };

    var getIconRemoveClass = function(iconClass) {
        var iconAll = 'fa-repeat fa-paper-plane fa-envelope-o';
        return iconAll.replace(iconClass,'');
    };

    /**
     * Get the title description of the user visibility link in the elo reminder users block.
     *
     * @method getTitle
     * @param {String} action
     * @return {object} jQuery promise
     * @private
     */
    var getTitle = function(action) {
        // return Str.get_string('online_status:' + action, 'block_elo_reminder_users');
        return Str.get_string('mail_status:' + action, 'block_elo_reminder_users');
    };

    var getTitleModal = function(key,data) {
        return Str.get_string('modal:' + key, 'block_elo_reminder_users',data);
    };

    var actionBtns = function(btnSend){
        var action = (btnSend.attr('data-action'));
        var userid = (btnSend.attr('data-userid'));
        var lastaccess = (btnSend.attr('data-lastaccess'));
        var courseid = (btnSend.attr('data-courseid'));
        var btnSending;
        if(action=='send' || action=='resend'){
            btnSend.hide();
            btnSending = $('#mail-to-user-sending').clone();
            btnSending.attr('id','elo-sending-' + userid).insertAfter(btnSend);
            mailToUser(action, userid, lastaccess, courseid, btnSend, btnSending);
        }
    };

    return {
        // Public variables and functions.
        /**
         * Initialise change user visibility function.
         *
         * @method init
         */
        init: function() {
            $(SELECTORS.MAIL_TO_USER_LINK).on('click', function(e) {
                e.preventDefault();
                var btnSend = $(this);
                var action = (btnSend.attr('data-action'));
                if(action=='send' || action=='resend'){
                    modalShowSingle(btnSend);
                }
            });

            $(SELECTORS.VIEWHISTORIES).on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var courseid = (btn.attr('data-courseid'));
                var userid = (btn.attr('data-userid'));
                modalShowHistories(userid,courseid);
            });

            $(SELECTORS.CHECKALLONPAGEBUTTON).on('click', function() {
                $(SELECTORS.BULKUSERCHECKBOXES).prop('checked', true);
            });

            $(SELECTORS.CHECKNONEBUTTON).on('click', function() {
                $(SELECTORS.BULKUSERCHECKBOXES).prop('checked', false);
            });


            $(SELECTORS.BULKACTIONSELECT).on('change', function(e) {
                var action = $(e.target).val();
                if (action.indexOf('#') !== -1) {
                    e.preventDefault();

                    var ids = [];
                    $(SELECTORS.BULKUSERSELECTEDCHECKBOXES).each(function(index, ele) {
                        var name = $(ele).attr('name');
                        var id = name.replace('user', '');
                        ids.push(id);
                    });

                    modalShowMultiple(ids);

                    $(SELECTORS.BULKACTIONSELECT + ' option[value=""]').prop('selected', 'selected');
                } else if (action !== '') {
                    // if ($(SELECTORS.BULKUSERSELECTEDCHECKBOXES).length > 0) {
                        $(SELECTORS.BULKACTIONFORM).submit();
                    // } else {
                    //     $(SELECTORS.BULKACTIONSELECT + ' option[value=""]').prop('selected', 'selected');
                    // }
                }
            }.bind(this));
        }
    };
});