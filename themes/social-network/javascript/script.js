current_notification_number = 0;
current_messages_number = 0;
current_follow_requests_number = 0;

current_width = $(window).width();
document_title = document.title;

$(function () {
  var hash = $('.main_session').val();
  $.ajaxSetup({ 
    data: {
        hash: hash
    },
    cache: false 
  });
  $('[data-toggle="tooltip"]').tooltip();
  // open last active tab
  var url = document.location.toString();
  if(url.match('#')) {
    var val_hash = url.split('#')[1];
    $('.nav-tabs a[href="#' + val_hash + '"]').tab('show');
  }
  $('.nav-tabs a').on('shown.bs.tab', function (e) {
    window.location.hash = e.target.hash;
    $('body').scrollTop(0);
  });
  
    intervalUpdates = setTimeout(Wo_intervalUpdates, 6000);
    setTimeout(Wo_UpdateLastSeen, 40000);
    setTimeout(Wo_IsLogged, 30000);

  //  dropdown won't close on click
  $('.dropdown-menu.request-list, .dropdown-menu.post-recipient, .dropdown-menu.post-options').click(function (e) {
    e.stopPropagation();
  }); 
  scrolled = 0;
  // Stick the home side bar if the screen width is > than 900
  if(current_width > 900) {
    $(window).scroll(function () {
      if($('.footer-wrapper').scrollTop() > 500) {
        $('.footer-wrapper .dropdown-menu').css('bottom', 'auto');
      } else {
        $('.footer-wrapper .dropdown-menu').css('bottom', '100%');
      }
      /*if($(document).scrollTop() > 1400) {
        $('#sidebar-sticky').addClass('Stick');
      } else {
        $('#sidebar-sticky').removeClass('Stick');
      }*/
      var nearToBottom = 100;
      if($('#posts').length > 0) {
          if ($(window).scrollTop() + $(window).height() > $(document).height() - nearToBottom) {
            if (scrolled == 0) {
               scrolled = 1;
               Wo_GetMorePosts();
            }
          }
      }
    });
  }
});


function Wo_CloseModels() {
  $('.modal').modal('hide');
}
// update user last seen
function Wo_UpdateLastSeen() {
  $.get(Wo_Ajax_Requests_File(), {
    f: 'update_lastseen'
  }, function () {
    setTimeout(Wo_UpdateLastSeen, 40000);
  });
}
// js function
function Wo_CheckUsername(username) {
  var check_container = $('.checking');
  var check_input = $('#username').val();
  if(check_input == '') {
    check_container.empty();
    return false;
  }
  check_container.removeClass('unavailable').removeClass('available').html('<i class="fa fa-clock-o"></i><span id="loading"> Checking <span>.</span><span>.</span><span>.</span></span>');
  $.get(Wo_Ajax_Requests_File(), {
    f: 'check_username',
    username: username
  }, function (data) {
    if(data.status == 200) {
      check_container.html('<i class="fa fa-check"></i> ' + data.message).removeClass('unavailable').addClass('available');
    } else if(data.status == 300) {
      check_container.html('<i class="fa fa-remove"></i> ' + data.message).removeClass('available').addClass('unavailable');
    } else if(data.status == 400) {
      check_container.html('<i class="fa fa-remove"></i> ' + data.message).removeClass('available').addClass('unavailable');
    } else if(data.status == 500) {
      check_container.html('<i class="fa fa-remove"></i> ' + data.message).removeClass('available').addClass('unavailable');
    } else if(data.status == 600) {
      check_container.html('<i class="fa fa-remove"></i> ' + data.message).removeClass('available').addClass('unavailable');
    }
  });
}

function Wo_CheckPagename(pagename, page_id) {
  var check_container = $('.checking');
  var check_input = $('#page_name').val();
  if(check_input == '') {
    check_container.empty();
    return false;
  }
  check_container.removeClass('unavailable').removeClass('available').html('<i class="fa fa-clock-o"></i><span id="loading"> Checking <span>.</span><span>.</span><span>.</span></span>');
  $.get(Wo_Ajax_Requests_File(), {
    f: 'check_pagename',
    pagename: pagename,
    page_id: page_id
  }, function (data) {
    if(data.status == 200) {
      check_container.html('<i class="fa fa-check"></i> ' + data.message).removeClass('unavailable').addClass('available');
    } else if(data.status == 300) {
      check_container.html('<i class="fa fa-remove"></i> ' + data.message).removeClass('available').addClass('unavailable');
    } else if(data.status == 400) {
      check_container.html('<i class="fa fa-remove"></i> ' + data.message).removeClass('available').addClass('unavailable');
    } else if(data.status == 500) {
      check_container.html('<i class="fa fa-remove"></i> ' + data.message).removeClass('available').addClass('unavailable');
    } else if(data.status == 600) {
      check_container.html('<i class="fa fa-remove"></i> ' + data.message).removeClass('available').addClass('unavailable');
    }
  });
}

function Wo_CheckGroupname(groupname, group_id) {
  var check_container = $('.checking');
  var check_input = $('#group_name').val();
  if(check_input == '') {
    check_container.empty();
    return false;
  }
  check_container.removeClass('unavailable').removeClass('available').html('<i class="fa fa-clock-o"></i><span id="loading"> Checking <span>.</span><span>.</span><span>.</span></span>');
  $.get(Wo_Ajax_Requests_File(), {
    f: 'check_groupname',
    groupname: groupname,
    group_id:group_id
  }, function (data) {
    if(data.status == 200) {
      check_container.html('<i class="fa fa-check"></i> ' + data.message).removeClass('unavailable').addClass('available');
    } else if(data.status == 300) {
      check_container.html('<i class="fa fa-remove"></i> ' + data.message).removeClass('available').addClass('unavailable');
    } else if(data.status == 400) {
      check_container.html('<i class="fa fa-remove"></i> ' + data.message).removeClass('available').addClass('unavailable');
    } else if(data.status == 500) {
      check_container.html('<i class="fa fa-remove"></i> ' + data.message).removeClass('available').addClass('unavailable');
    } else if(data.status == 600) {
      check_container.html('<i class="fa fa-remove"></i> ' + data.message).removeClass('available').addClass('unavailable');
    }
  });
}

// scroll to top function
function scrollToTop() {
  verticalOffset = typeof (verticalOffset) != 'undefined' ? verticalOffset : 0;
  element = $('html');
  offset = element.offset();
  offsetTop = offset.top;
  $('html, body').animate({
    scrollTop: offsetTop
  }, 300, 'linear');
}

// check if user is logged in function
function Wo_IsLogged() {
  $.post(Wo_Ajax_Requests_File() + '?f=session_status', function (data) {
    setTimeout(Wo_UpdateLastSeen, 30000);
    if(data.status == 200) {
      $('#logged-out-modal').modal({
        show: true
      });
    }
  });
}

// side bar users
function Wo_ReloadSideBarUsers() {
  Wo_progressIconLoader($('#sidebar-user-list-container').find('.refresh'));
  $.get(Wo_Ajax_Requests_File(), {
    f: 'update_sidebar_users'
  }, function (data) {
    if(data.status == 200) {
      $('.sidebar-users-may-know-container').html(data.html);
    }
    Wo_progressIconLoader($('#sidebar-user-list-container').find('.refresh'));
  });
}

function Wo_ReloadSideBarGroups() {
  Wo_progressIconLoader($('#sidebar-group-list-container').find('.refresh'));
  $.get(Wo_Ajax_Requests_File(), {
    f: 'update_sidebar_groups'
  }, function (data) {
    if(data.status == 200) {
      $('.sidebar-group-may-know-container').html(data.html);
    }
    Wo_progressIconLoader($('#sidebar-group-list-container').find('.refresh'));
  });
}

// side bar pages
function Wo_ReloadSideBarPages() {
  Wo_progressIconLoader($('#sidebar-page-list-container').find('.refresh'));
  var page_id = $('.sidebar-pages-may-know-container').find('.sidebar-page-data').attr('data-page-id');
  if (page_id == 'undefined') {
      page_id = '';
  }
  $.get(Wo_Ajax_Requests_File(), {
    f: 'pages',
    s: 'get_next_page',
    page_id: page_id
  }, function (data) {
    if(data.status == 200) {
      if (data.html.length == 0) {
        $('.sidebar-pages-may-know-container').html('<h2><div class="no-more-pages text-center">No more pages to like</div></h2>');
      } else {
        $('.sidebar-pages-may-know-container').hide().html(data.html).fadeIn(300);
      }
    }
    Wo_progressIconLoader($('#sidebar-page-list-container').find('.refresh'));
  });
}

// get new notifications
function Wo_OpenNotificationsMenu() {
  notification_container = $('.notification-container');
  notification_list = $('#notification-list');
  notification_container.find('.new-update-alert').addClass('hidden');
  Wo_progressIconLoader(notification_container.find('.notification-loading-progress'));
  $.get(Wo_Ajax_Requests_File(), {
    f: 'get_notifications'
  }, function (data) {
    if(data.status == 200) {
      if(data.html.length == 0) {
        notification_list.html('<span class="center-text padding-10">' + data.message + '</span>');
      } else {
        notification_list.html(data.html);
        Wo_intervalUpdates();
      }
    }
    Wo_progressIconLoader(notification_container.find('.notification-loading-progress'));
  });
}
function Wo_OpenMessagesMenu() {
  messages_container = $('.messages-notification-container');
  messages_list = $('#messages-list');
  Wo_progressIconLoader(messages_container.find('.notification-loading-progress'));
  $.get(Wo_Ajax_Requests_File(), {
    f: 'get_messages'
  }, function (data) {
    if(data.status == 200) {
      if(data.html.length == 0) {
        messages_list.html('<span class="center-text padding-10">' + data.message + '</span>');
      } else {
        messages_list.html(data.html);
        messages_list.append('<div class="show-message-link-container"><a href="' + data.messages_url + '" class="show-message-link" data-ajax="?link1=messages">' + data.messages_text + '</a></div>');
        //Wo_intervalUpdates();
      }
    }
    Wo_progressIconLoader(messages_container.find('.notification-loading-progress'));
  });
}
// get new friend requests
function Wo_OpenRequestsMenu() {
  requests_container = $('.requests-container');
  requests_List = $('#requests-list');
  Wo_progressIconLoader(requests_container.find('.requests-loading'));
  $.get(Wo_Ajax_Requests_File(), {
    f: 'get_follow_requests'
  }, function (data) {
    if(data.status == 200) {
      if(data.html.length == 0) {
        requests_List.html('<span class="center-text">' + data.message + '</span>');
      } else {
        requests_List.html(data.html);
        Wo_intervalUpdates();
      }
    }
    Wo_progressIconLoader(requests_container.find('.requests-loading'));
  });
}

// Notifications & follow requests updates
function Wo_intervalUpdates() {
  var check_posts = true;
  if ($('.posts-count').length == 0) {
     check_posts = false;
  }
  if(typeof ($('#posts').attr('data-story-user')) == "string") {
    user_id = $('#posts').attr('data-story-user');
  } else {
    user_id = 0;
  }
  before_post_id = 0;
  if($('.post-container').length > 0) {
    var before_post_id = $('.post-container  > .post:not(.boosted)').attr('data-post-id');
  }
  var notification_container = $('.notification-container');
  var messages_notification_container = $('.messages-notification-container');
  var follow_requests_container = $('.requests-container');
  $.get(Wo_Ajax_Requests_File(), {
    f: 'update_data',
    user_id: user_id,
    before_post_id: before_post_id,
    check_posts:check_posts
  }, function (data) {
    clearTimeout(intervalUpdates);
    intervalUpdates = setTimeout(Wo_intervalUpdates, 5000);
    if (data.count_num > 0 && $('.filter-by-more').attr('data-filter-by') == 'all') {
      $('.posts-count').html(data.count);
    }
    if(typeof (data.notifications) != "undefined" && data.notifications > 0) {
      notification_container.find('.new-update-alert').removeClass('hidden');
      notification_container.find('.sixteen-font-size').addClass('unread-update');
      notification_container.find('.new-update-alert').text(data.notifications).show();
      if(current_width > 800 && data.pop == 200) {
        Wo_NotifyMe(data.icon, data.title, data.notification_text, data.url);
      }
      if(data.notifications != current_notification_number) {
        if (data.notifications_sound == 0) {
           document.getElementById('notification-sound').play();
        }
        current_notification_number = data.notifications;
      }
    } else {
      notification_container.find('.new-update-alert').hide();
      notification_container.find('.sixteen-font-size').removeClass('unread-update');
      current_notification_number = 0;
    }
    if(typeof (data.messages) != "undefined" && data.messages > 0) {
      messages_notification_container.find('.new-update-alert').removeClass('hidden');
      messages_notification_container.find('.sixteen-font-size').addClass('unread-update');
      messages_notification_container.find('.new-update-alert').text(data.messages).show();
      if(data.messages != current_messages_number) {
        if (data.notifications_sound == 0) {
          document.getElementById('message-sound').play();
        }
        current_messages_number = data.messages;
      }
    } else {
      messages_notification_container.find('.new-update-alert').hide();
      messages_notification_container.find('.sixteen-font-size').removeClass('unread-update');
      current_messages_number = 0;
    }
    if(typeof (data.followRequests) != "undefined" && data.followRequests > 0) {
      follow_requests_container.find('.new-update-alert').removeClass('hidden');
      follow_requests_container.find('.sixteen-font-size').addClass('unread-update');
      follow_requests_container.find('.new-update-alert').text(data.followRequests).show();
      if(data.followRequests != current_follow_requests_number) {
        current_follow_requests_number = data.followRequests;
      }
    } else {
      follow_requests_container.find('.new-update-alert').hide();
      follow_requests_container.find('.sixteen-font-size').removeClass('unread-update');
      current_follow_requests_number = 0;
    }

    if(typeof (data.messages) != "undefined" && data.messages > 0 || typeof (data.notifications) != "undefined" && data.notifications > 0 || typeof (data.followRequests) != "undefined" && data.followRequests > 0) {
      title = Number(data.notifications) + Number(data.messages) + Number(data.followRequests);
      document.title = '(' + title + ') ' + document_title;
    } else {
      document.title = document_title;
    }
    if (data.calls == 200 && $('#re-calling-modal').length == 0) {
         if ($('#calling-modal').length == 0) {
          $('body').append(data.calls_html);
          if (!$('#re-calling-modal').hasClass('calling')) {
            $('#re-calling-modal').modal({
             show: true
            });
            Wo_PlayVideoCall('play');
          }
          document.title = 'New video call..';
          setTimeout(function () {
            Wo_CloseModels();
            $('#re-calling-modal').addClass('calling');
            Wo_PlayVideoCall('stop');
            document.title = document_title;
            setTimeout(function () {
              $( '#re-calling-modal' ).remove();
              $( '.modal-backdrop' ).remove();
              $( 'body' ).removeClass( "modal-open" );
            }, 3000)
          }, 40000);
         } 
    } else if (data.is_call == 0 && $('#re-calling-modal').length > 0) {
      Wo_PlayVideoCall('stop');
      $( '#re-calling-modal' ).remove();
      $( '.modal-backdrop' ).remove();
      $( 'body' ).removeClass( "modal-open" );
    }
  });
}

// intervel new posts
function Wo_GetNewPosts() {
  var filter_by_more = $('#load-more-filter').find('.filter-by-more').attr('data-filter-by');
  if(filter_by_more != 'all') {
    return false;
  }
  if(typeof ($('#posts').attr('data-story-user')) == "string") {
    user_id = $('#posts').attr('data-story-user');
  } else {
    user_id = 0;
  }
  before_post_id = 0;
  if($('.post-container').length > 0) {
    var before_post_id = $('.post-container  > .post:not(.boosted)').attr('data-post-id');
  }
  $.get(Wo_Ajax_Requests_File(), {
    f: 'posts',
    s: 'get_new_posts',
    before_post_id: before_post_id,
    user_id: user_id
  }, function (data) {
    if(data.length > 0) {
      $('#posts').find('.posts-container').remove();
      $('#posts').prepend(data);
    }
     $('.posts-count').empty();
  });
}

// load more posts
function Wo_GetMorePosts() {
  var more_posts = $('#load-more-posts');
  var filter_by_more = $('#load-more-filter').find('.filter-by-more').attr('data-filter-by');
  var after_post_id = $('.post:last').attr('data-post-id');
  var page_id = 0;
  var user_id = 0;
  var group_id = 0;
  if(after_post_id != null) {
    more_posts.show();
  }
  if(typeof ($('#posts').attr('data-story-user')) == "string") {
    user_id = $('#posts').attr('data-story-user');
  } else if(typeof ($('#posts').attr('data-story-page')) == "string") {
    page_id = $('#posts').attr('data-story-page');
  } else if(typeof ($('#posts').attr('data-story-group')) == "string") {
    group_id = $('#posts').attr('data-story-group');
  }
  $('#posts').append('<div class="hidden loading-status"></div>');
  $('#load-more-posts').hide();
  $('.loading-status').hide().html('<div class="white-loading list-group"><div class="cs-loader"><div class="cs-loader-inner"><label> ●</label><label> ●</label><label> ●</label><label> ●</label><label> ●</label><label> ●</label></div></div></div>').removeClass('hidden').show();
  Wo_progressIconLoader($('#load-more-posts'));
  posts_count = 0;
  if ($('.post').length > 0) {
    posts_count = $('.post').length;
  }
  $.get(Wo_Ajax_Requests_File(), {
    f: 'posts',
    s: 'load_more_posts',
    filter_by_more: filter_by_more,
    after_post_id: after_post_id,
    user_id: user_id,
    page_id: page_id,
    group_id: group_id,
    posts_count: posts_count
  }, function (data) {
    if (data.length == 0) {
      $.get(Wo_Ajax_Requests_File(), {f: 'get_no_posts_name'}, function (data3) {
          $('#load-more-posts').html('<div class="white-loading list-group"><div class="cs-loader"><div class="no-more-posts-to-show">' + data3.name + '</div></div>');
      });
     } else {
      $('#posts').append(data);
    }
    $('#load-more-posts').show();
    $('.loading-status').remove();
    Wo_progressIconLoader($('#load-more-posts'));
    scrolled = 0;
  });
}

// post filteration
function Wo_FilterPostBy(filter_by) {
  var more_posts = $('#load-more-posts');
  var filter_by_more = $('#load-more-filter').find('.filter-by-more');
  filter_by_more.attr("data-filter-by", filter_by);
  var filter_by_progress_icon = $('.filter-container').find('.type-' + filter_by);
  Wo_progressIconLoader(filter_by_progress_icon);
  var type = '';
  var id = 0;
  if(typeof ($('#posts').attr('data-story-user')) == "string") {
    id = $('#posts').attr('data-story-user');
    type = 'profile';
  } else if(typeof ($('#posts').attr('data-story-page')) == "string") {
    id = $('#posts').attr('data-story-page');
    type = 'page';
  } else if (typeof ($('#posts').attr('data-story-group')) == "string") {
    id = $('#posts').attr('data-story-group');
    type = 'group';
  }
  $.get(Wo_Ajax_Requests_File(), {
    f: 'posts',
    s: 'filter_posts',
    filter_by: filter_by,
    id: id,
    type: type
  }, function (data) {
    if(data.status == 200) {
      $('#posts').html(data.html);
      more_posts.html('<span class="btn btn-default">' + data.text + '<span>');
    }
    Wo_progressIconLoader(filter_by_progress_icon);
  });
}
// register post share
function Wo_RegisterShare(post_id) {
  var post = $('#post-' + post_id);
  $.get(Wo_Ajax_Requests_File(), {
    f: 'posts',
    s: 'register_share',
    post_id: post_id
  }, function (data) {
    if(data.status == 200) {
      post.find("#share-button").addClass('active');
      post.find("[id^=shares]").text(data.shares);
    } else {
      post.find("#share-button").removeClass('active');
      post.find("[id^=shares]").text(data.shares);
    }
    if (data.can_send == 1) {
        Wo_SendMessages();
    }
  });
}

// open post share buttons
function Wo_OpenShareBtns(post_id) {
  post_wrapper = $('#post-' + post_id);
  post_wrapper.find('.post-share').slideToggle(200);
}
// register post comment
function Wo_RegisterComment(text, post_id, user_id, event, page_id, type) {
  if(event.keyCode == 13 && event.shiftKey == 0) {
    var comment_src_image = $('#post-' + post_id).find('#comment_src_image');
    var comment_image = '';
    if (comment_src_image.length > 0) {
      comment_image = comment_src_image.val();
    }
    post_wrapper = $('[id=post-' + post_id + ']');
    comment_textarea = post_wrapper.find('.post-comments');
    comment_btn = comment_textarea.find('.emo-comment');
    textarea_wrapper = comment_textarea.find('.textarea');
    comment_list = post_wrapper.find('.comments-list');
    if(text == '' && comment_image == '') {
      return false;
    }
    event.preventDefault();
    textarea_wrapper.val('');
    Wo_progressIconLoader(comment_btn);
    $.post(Wo_Ajax_Requests_File() + '?f=posts&s=register_comment', {
      post_id: post_id,
      text: text,
      user_id: user_id,
      page_id: page_id,
      comment_image: comment_image
    }, function (data) {
      if(data.status == 200) {
        post_wrapper.find('.comment-container:first-child').before(data.html);
        post_wrapper.find('[id=comments]').html(data.comments_num);
      }
      $('#post-'+ post_id).find('.comment-image-con').empty().addClass('hidden');
      Wo_progressIconLoader(comment_btn);
      if (data.can_send == 1) {
        Wo_SendMessages();
      }
    });
  }
}

function Wo_RegisterCommentClick(text, post_id, user_id, page_id, type) {
    post_wrapper = $('[id=post-' + post_id + ']');
    comment_textarea = post_wrapper.find('.post-comments');
    comment_btn = comment_textarea.find('.emo-comment');
    textarea_wrapper = comment_textarea.find('.textarea');
    comment_list = post_wrapper.find('.comments-list');
    if(text == '') {
      return false;
    }
    textarea_wrapper.val('');
    Wo_progressIconLoader(comment_btn);
    $.post(Wo_Ajax_Requests_File() + '?f=posts&s=register_comment', {
      post_id: post_id,
      text: text,
      user_id: user_id,
      page_id: page_id
    }, function (data) {
      if(data.status == 200) {
        post_wrapper.find('.comment-container:first-child').before(data.html);
        post_wrapper.find('[id=comments]').html(data.comments_num);
      }
      Wo_progressIconLoader(comment_btn);
      if (data.can_send == 1) {
        Wo_SendMessages();
      }
    });
}

// register post comment
function Wo_LightBoxComment(text, post_id, user_id, event, page_id) {
  if(event.keyCode == 13 && event.shiftKey == 0) {
    post_wrapper = $('#lightbox-post-' + post_id);
    comment_textarea = post_wrapper.find('.post-comments');
    comment_btn = comment_textarea.find('.comment-btn');
    textarea_wrapper = comment_textarea.find('.textarea');
    comment_list = post_wrapper.find('.comments-list');
    if(textarea_wrapper.val() == '') {
      return false;
    }
    textarea_wrapper.val('');
    Wo_progressIconLoader(comment_btn);
    $.post(Wo_Ajax_Requests_File() + '?f=posts&s=register_comment', {
      post_id: post_id,
      text: text,
      user_id: user_id,
      page_id: page_id
    }, function (data) {
      if(data.status == 200) {
        post_wrapper.find('.comment-container:first-child').before(data.html);
        post_wrapper.find('#comments').html(data.comments_num);
      }
      Wo_progressIconLoader(comment_btn);
      if (data.can_send == 1) {
        Wo_SendMessages();
      }
    });
  }
}

// load all post comments
function Wo_loadAllComments(post_id) {
  main_wrapper = $('#post-' + post_id);
  view_more_wrapper = main_wrapper.find('.view-more-wrapper');
  Wo_progressIconLoader(view_more_wrapper);
  $.get(Wo_Ajax_Requests_File(), {
    f: 'posts',
    s: 'load_more_comments',
    post_id: post_id
  }, function (data) {
    if(data.status == 200) {
      main_wrapper.find('.comments-list').html(data.html);
      view_more_wrapper.remove();
    }
  });
}

// show post comments
function Wo_ShowComments(post_id) {
  $('#post-comments-' + post_id).toggleClass('hidden');
}

// open post edit modal
function Wo_OpenPostEditBox(post_id) {
  var edit_box = $('#post-' + post_id).find('#edit-post');
  edit_box.modal({
    show: true
  });
}

// save edited post
function Wo_EditPost(post_id) {
  var post = $('#post-' + post_id);
  var edit_box = $('#post-' + post_id).find('#edit-post');
  var edit_textarea = post.find('.edit-textarea-' + post_id + ' textarea');
  var text = edit_textarea.val();
  var post_text = post.find('.post-description p');
  Wo_progressIconLoader(post.find('#edit-post-button'));
  $.post(Wo_Ajax_Requests_File() + '?f=posts&s=edit_post', {
    post_id: post_id,
    text: text
  }, function (data) {
    if(data.status == 200) {
      post_text.html(data.html);
      edit_box.modal('hide');
    }
    Wo_progressIconLoader(post.find('#edit-post-button'));
    if (data.can_send == 1) {
        Wo_SendMessages();
    }
  });
}

// open delete post modal
function Wo_OpenPostDeleteBox(post_id) {
  var delete_box = $('#post-' + post_id).find('#delete-post');
  delete_box.modal({
    show: true
  });
}

// delete post
function Wo_DeletePost(post_id) {
  var delete_box = $('#post-' + post_id).find('#delete-post');
  var delete_button = delete_box.find('#delete-all-post');
  Wo_progressIconLoader(delete_button);
  $.get(Wo_Ajax_Requests_File(), {
    f: 'posts',
    s: 'delete_post',
    post_id: post_id
  }, function (data) {
    if(data.status == 200) {
      delete_box.modal('hide');
      setTimeout(function () {
        $('#post-' + post_id).slideUp(200, function () {
          $(this).remove();
        });
      }, 300);
    }
    Wo_progressIconLoader(delete_button);
  });
}

// open comment textarea
function Wo_OpenCommentEditBox(comment_id) {
  comment = $('[id=comment_' + comment_id + ']');
  comment_text = comment.find('.comment-edit');
  comment_text.slideToggle(100);
}

// save edited comment
function Wo_EditComment(text, comment_id, event) {
  comment = $('[id=comment_' + comment_id + ']');
  comment_text = comment.find('.comment-text');
  if(event.keyCode == 13 && event.shiftKey == 0) {
    Wo_progressIconLoader(comment.find('#editComment'));
    $.post(Wo_Ajax_Requests_File() + '?f=posts&s=edit_comment', {
      comment_id: comment_id,
      text: text
    }, function (data) {
      if(data.status == 200) {
        comment_text.html(data.html);
        Wo_OpenCommentEditBox(comment_id);
      }
      Wo_progressIconLoader(comment.find('#editComment'));
    });
  }
}

// delete comment
function Wo_DeleteComment(comment_id) {
  var delete_box = $('[id=comment_' + comment_id + ']').find('#delete-comment');
  var delete_button = delete_box.find('#delete-all-post');
  delete_box.modal({
    show: true
  });
  var comment = $('[id=comment_' + comment_id + ']');
  delete_button.on('click', function () {
    Wo_progressIconLoader(delete_button);
    $.get(Wo_Ajax_Requests_File(), {
      f: 'posts',
      s: 'delete_comment',
      comment_id: comment_id
    }, function (data) {
      if(data.status == 200) {
        delete_box.modal('hide');
        $('.modal').modal('hide');
        comment.fadeOut(300, function () {
          comment.remove();
        });
      }
    });
  });
}

function Wo_DeleteReplyComment(reply_id) {
  var delete_box = $('[id=comment_reply_' + reply_id + ']').find('#delete-comment-reply');
  var delete_button = delete_box.find('#delete-all-reply');
  delete_box.modal({
    show: true
  });
  var comment = $('[id=comment_reply_' + reply_id + ']');
  delete_button.on('click', function () {
    Wo_progressIconLoader(delete_button);
    $.get(Wo_Ajax_Requests_File(), {
      f: 'posts',
      s: 'delete_comment_reply',
      reply_id: reply_id
    }, function (data) {
      if(data.status == 200) {
        delete_box.modal('hide');
        comment.fadeOut(300, function () {
          $(this).remove();
        });
      }
    });
  });
}

// register comment like
function Wo_RegisterCommentLike(comment_id) {
  var comment = $('[id=comment_' + comment_id + ']');
  comment_text = comment.find('div.comment-text').text();
  Wo_progressIconLoader(comment.find('#LikeComment'));
  $.post(Wo_Ajax_Requests_File() + '?f=posts&s=register_comment_like', {
    comment_id: comment_id,
    comment_text: comment_text
  }, function (data) {
    if(data.status == 200) {
      if (data.dislike == 1) {
          comment.find("#comment-wonders").text(data.wonders_c);
          comment.find("#WonderComment").html('<i class="fa fa-thumbs-down progress-icon" data-icon="thumbs-down"></i>');
      }
      comment.find("#LikeComment").html('<i class="fa fa-thumbs-up progress-icon active-like" data-icon="thumbs-up"></i>').fadeIn(250);
      comment.find("#comment-likes").text(data.likes);
    } else {
      comment.find("#LikeComment").html('<i class="fa fa-thumbs-up progress-icon" data-icon="thumbs-up"></i>').fadeIn(250);
      comment.find("#comment-likes").text(data.likes);
    }
  });
}

// register comment wonder
function Wo_RegisterCommentWonder(comment_id) {
  var comment = $('[id=comment_' + comment_id + ']');
  comment_text = comment.find('div.comment-text').text();
  Wo_progressIconLoader(comment.find('#WonderComment'));
  $.post(Wo_Ajax_Requests_File() + '?f=posts&s=register_comment_wonder', {
    comment_id: comment_id,
    comment_text: comment_text
  }, function (data) {
    if(data.status == 200) {
      if (data.dislike == 1) {
          comment.find("#comment-likes").text(data.likes_c);
          comment.find("#LikeComment").html('<i class="fa fa-thumbs-up progress-icon" data-icon="thumbs-up"></i>');
      }
      comment.find("#WonderComment").html('<span class="active-wonder"><i class="fa fa-' + data.icon + ' progress-icon" data-icon="' + data.icon + '"></i></span>').fadeIn(250);
      comment.find("#comment-wonders").text(data.wonders);
    } else if (data.status == 300)  {
      comment.find("#WonderComment").html('<i class="fa fa-' + data.icon + ' progress-icon" data-icon="' + data.icon + '"></i>').fadeIn(250);
      comment.find("#comment-wonders").text(data.wonders);
    }
  });
}

// register comment wonder
function Wo_RegisterCommentReplyWonder(reply_id) {
  var comment = $('[id=comment_reply_' + reply_id + ']');
  comment_text = comment.find('div.reply-text').text();
  Wo_progressIconLoader(comment.find('#WonderReplyComment'));
  $.post(Wo_Ajax_Requests_File() + '?f=posts&s=register_comment_reply_wonder', {
    reply_id: reply_id,
    comment_text: comment_text
  }, function (data) {
    if(data.status == 200) {
      if (data.dislike == 1) {
          comment.find("#comment-reply-likes").text(data.likes_r);
          comment.find("#LikeReplyComment").html('<i class="fa fa-thumbs-up progress-icon" data-icon="thumbs-up"></i>');
      }
      comment.find("#WonderReplyComment").html('<span class="active-wonder"><i class="fa fa-' + data.icon + ' progress-icon" data-icon="' + data.icon + '"></i></span>').fadeIn(250);
      comment.find("#comment-reply-wonders").text(data.wonders);
    } else if (data.status == 300){
      comment.find("#WonderReplyComment").html('<i class="fa fa-' + data.icon + ' progress-icon" data-icon="' + data.icon + '"></i>').fadeIn(250);
      comment.find("#comment-reply-wonders").text(data.wonders);
    }
  });
}

function Wo_RegisterCommentReplyLike(reply_id) {
  var comment = $('[id=comment_reply_' + reply_id + ']');
  comment_text = comment.find('div.reply-text').text();
  Wo_progressIconLoader(comment.find('#LikeReplyComment'));
  $.post(Wo_Ajax_Requests_File() + '?f=posts&s=register_comment_reply_like', {
    reply_id: reply_id,
    comment_text: comment_text
  }, function (data) {
    if(data.status == 200) {
      if (data.dislike == 1) {
          comment.find("#comment-reply-wonders").text(data.wonders_r);
          comment.find("#WonderReplyComment").html('<i class="fa fa-thumbs-down progress-icon" data-icon="thumbs-up"></i>');
      }
      comment.find("#LikeReplyComment").html('<i class="fa fa-thumbs-up progress-icon active-like" data-icon="thumbs-up"></i>').fadeIn(250);
      comment.find("#comment-reply-likes").text(data.likes);
    } else if (data.status == 300){
      comment.find("#LikeReplyComment").html('<i class="fa fa-thumbs-up progress-icon" data-icon="thumbs-up"></i>').fadeIn(250);
      comment.find("#comment-reply-likes").text(data.likes);
    }
  });
}
// save post
function Wo_SavePost(post_id) {
  var post = $('#post-' + post_id);
  Wo_progressIconLoader(post.find('.save-post'));
  $.get(Wo_Ajax_Requests_File(), {
    f: 'posts',
    s: 'save_post',
    post_id: post_id
  }, function (data) {
    if(data.status == 200) {
      post.find('.save-post').html('<i class="fa fa-fw fa-check progress-icon" data-icon="floppy-o"></i> ' + data.text);
    } else if(data.status == 300) {
      post.find('.save-post').html('<i class="fa fa-fw fa-floppy-o progress-icon" data-icon="check"></i> ' + data.text);
    }
  });
}

// report post
function Wo_ReportPost(post_id) {
  var post = $('#post-' + post_id);
  Wo_progressIconLoader(post.find('.report-post'));
  $.get(Wo_Ajax_Requests_File(), {
    f: 'posts',
    s: 'report_post',
    post_id: post_id
  }, function (data) {
    if(data.status == 200) {
      post.find('.report-post').html('<i class="fa fa-fw fa-check progress-icon" data-icon="flag"></i> ' + data.text);
    } else if(data.status == 300) {
      post.find('.report-post').html('<i class="fa fa-fw fa-flag progress-icon" data-icon="flag"></i> ' + data.text);
    }
  });
}

function Wo_PinPost(post_id, id, type) {
  var post = $('#post-' + post_id);
  Wo_progressIconLoader(post.find('.pin-post'));
  $.get(Wo_Ajax_Requests_File(), {
    f: 'posts',
    s: 'pin_post',
    post_id: post_id,
    id: id,
    type: type
  }, function (data) {
    if(data.status == 200) {
      post.find('.pin-post').html('<i class="fa fa-fw fa-thumb-tack progress-icon pinned-text" data-icon="thumb-tack"></i> ' + data.text);
    } else if(data.status == 300) {
      post.find('.pin-post').html('<i class="fa fa-fw fa-thumb-tack progress-icon" data-icon="thumb-tack"></i> ' + data.text);
    }
  });
}

function Wo_BoostPost(post_id) {
  var post = $('#post-' + post_id);
  Wo_progressIconLoader(post.find('.boost-post'));
  $.get(Wo_Ajax_Requests_File(), {
    f: 'posts',
    s: 'boost_post',
    post_id: post_id
  }, function (data) {
    if(data.status == 200) {
      post.find('.boost-post').html('<i class="fa fa-fw fa-bolt progress-icon boosted-text" data-icon="bolt"></i> ' + data.text);
    } else if(data.status == 300) {
      post.find('.boost-post').html('<i class="fa fa-fw fa-bolt progress-icon" data-icon="bolt"></i> ' + data.text);
    }
  });
}
// open post liked users
function Wo_OpenPostLikedUsers(post_id) {
  var post = $('#post-' + post_id);
  post_likes_container = post.find('.post-likes');
  post.find('.post-wonders').slideUp(200, function () {
    post.find('.post-wonders').empty();
  });
  if(!post_likes_container.is(':empty')) {
    post_likes_container.slideToggle(200, function () {
      post.find('.post-likes').empty();
    });
    return false;
  }
  Wo_progressIconLoader(post.find('.post-like-status'));
  $.get(Wo_Ajax_Requests_File(), {
    f: 'posts',
    s: 'get_post_likes',
    post_id: post_id
  }, function (data) {
    if(data.status == 200) {
      if(data.html.length == 0) {
        post_likes_container.html('<span class="view-more-wrapper">' + data.message + '</span>');
      } else {
        post_likes_container.html(data.html);
      }
      post_likes_container.slideToggle(200);
    }
    Wo_progressIconLoader(post.find('.post-like-status'));
  });
}

// open post wodered users
function Wo_OpenPostWonderedUsers(post_id) {
  var post = $('#post-' + post_id);
  post_wonders_container = post.find('.post-wonders');
  post.find('.post-likes').slideUp(200, function () {
    post.find('.post-likes').empty();
  });
  if(!post_wonders_container.is(':empty')) {
    post_wonders_container.slideToggle(200, function () {
      post.find('.post-wonders').empty();
    });
    return false;
  }
  Wo_progressIconLoader(post.find('.post-wonders-status'));
  $.get(Wo_Ajax_Requests_File(), {
    f: 'posts',
    s: 'get_post_wonders',
    post_id: post_id
  }, function (data) {
    if(data.status == 200) {
      if(data.html.length == 0) {
        post_wonders_container.html('<span class="view-more-wrapper">' + data.message + '</span>');
      } else {
        post_wonders_container.html(data.html);
      }
      post_wonders_container.slideToggle(200);
    }
    Wo_progressIconLoader(post.find('.post-wonders-status'));
  });
}

// add emo to input
function Wo_AddEmo(code, input) {
  inputTag = $(input);
  inputVal = inputTag.val();
  if(typeof (inputTag.attr('placeholder')) != "undefined") {
    inputPlaceholder = inputTag.attr('placeholder');
    if(inputPlaceholder == inputVal) {
      inputTag.val('');
      inputVal = inputTag.val();
    }
  }
  if(inputVal.length == 0) {
    inputTag.val(code + ' ');
  } else {
    inputTag.val(inputVal + ' ' + code);
  }
  inputTag.keyup();
}

// accept follow request
function Wo_AcceptFollowRequest(user_id) {
  var main_container = $('.user-follow-request-' + user_id);
  var follow_main_container = main_container.find('#accept-' + user_id);
  Wo_progressIconLoader(follow_main_container);
  $.get(Wo_Ajax_Requests_File(), {
    f: 'accept_follow_request',
    following_id: user_id
  }, function (data) {
    if(data.status == 200) {
      main_container.find('.accept-btns').html(data.html);
    }
  });
}
function Wo_StartRepositioner() {
    $('.user-cover-reposition-w').hide();
    $('.user-reposition-container').show();
    $('.cover-resize-buttons').show();
    $('.default-buttons').hide();
    $('.when-notedit').hide();
    $('.when-edit').show();
    $('.user-reposition-dragable-container').show();
    $('.profile-cover-changer').show();
    $('.screen-width').val($('.user-reposition-container').width());
    $('.user-reposition-container img').css('cursor', '-webkit-grab').draggable({
        scroll: false,
        axis: "y",
        cursor: "-webkit-grab",
        drag: function (event, ui) {
            y1 = $('.user-cover-reposition-container').height();
            y2 = $('.user-reposition-container').find('img').height();
            if (ui.position.top >= 0) {
                ui.position.top = 0;
            }else
                if (ui.position.top <= (y1-y2)) {
                    ui.position.top = y1-y2;
                }
            },
            stop: function(event, ui) {
                $('input.cover-position').val(ui.position.top);
            }
        });
}

function Wo_SubmitRepositioner() {
    if ($('input.cover-position').length == 1) {
        posY = $('input.cover-position').val();
        $('form.cover-position-form').submit();
    }
}

function Wo_StopRepositioner() {
    $('.when-notedit').show();
    $('.when-edit').hide();
    $('.user-cover-reposition-w').show();
    $('.user-reposition-container').hide();
    $('.cover-resize-buttons').hide();
    $('.default-buttons').show();
    $('input.cover-position').val(0);
    $('.user-reposition-container img').draggable('destroy').css('cursor','default');
}
// delete follow request
function Wo_DeleteFollowRequest(user_id) {
  var main_container = $('.user-follow-request-' + user_id);
  var follow_main_container = main_container.find('#delete-' + user_id);
  Wo_progressIconLoader(follow_main_container);
  $.get(Wo_Ajax_Requests_File(), {
    f: 'delete_follow_request',
    following_id: user_id
  }, function (data) {
    if(data.status == 200) {
      main_container.find('.accept-btns').html(data.html);
    }
  });
}

// update post privacy
function Wo_UpdatePostPrivacy(post_id, privacy_type, event) {
  var post = $('#post-' + post_id);
  event.preventDefault();
  var post_privacy_container = post.find('.post-privacy');
  Wo_progressIconLoader(post_privacy_container);
  $.get(Wo_Ajax_Requests_File(), {
    f: 'posts',
    s: 'update_post_privacy',
    post_id: post_id,
    privacy_type: privacy_type
  }, function (data) {
    if(data.status == 200) {
      if(data.privacy_type == 0) {
        post_privacy_container.html('<i class="fa fa-globe progress-icon" data-icon="globe"></i>');
      } else if(data.privacy_type == 1) {
        post_privacy_container.html('<i class="fa fa-users progress-icon" data-icon="users"></i>');
      } else if(data.privacy_type == 2) {
        post_privacy_container.html('<i class="fa fa-user progress-icon" data-icon="user"></i>');
      } else if(data.privacy_type == 3) {
        post_privacy_container.html('<i class="fa fa-lock progress-icon" data-icon="lock"></i>');
      } else {
        return false;
      }
    }
  });
}

// open chat tab
function Wo_OpenChatTab(recipient_id) {
  $.get(Wo_Ajax_Requests_File(), {
    f: 'chat',
    s: 'is_chat_on',
    recipient_id: recipient_id
  }, function (data) {
    if(data.chat != 1) {
      document.location = data.url;
      return false;
    }
  });
  if(current_width < 720) {
    $.get(Wo_Ajax_Requests_File(), {
      f: 'chat',
      s: 'close',
      recipient_id: recipient_id
    }, function (data) {
      document.location = data.url;
    });
    return false;
  }
  placement = 1;
  if ($('.chat-wrapper').length == 1) {
    placement = 2;
  } else if ($('.chat-wrapper').length == 2) {
    placement = 3;
  }
  var loading_icon = '<i class="fa fa-spin fa-spinner"></i>';
  $('#online_' + recipient_id).find('.new-message-alert').hide();
  var loading_div = $('.chat-container').find('#online_' + recipient_id).find('.chat-loading-icon');
  loading_div.html(loading_icon);
  chat_container = $('.chat-container');
  $(document.body).attr('data-chat-recipient-'+recipient_id, recipient_id);
  $('.chat-wrapper').show();
  /* var data_html = '<div class="chat-wrapper" id="chat_"><div class="online-toggle pointer" onclick="javascript:$(\'.chat-tab-container\').slideToggle(100);"><a style="color:#fff;" href=""><span class="chat-tab-status">......</span></a></div><div class="chat-tab-container"><div class="chat-messages-wrapper"><div class="chat-messages"></div><div class="clear"></div></div><div class="chat-textarea btn-group"><div class="emo-container"></div><form action="#" method="post" class="chat-sending-form"><textarea name="textSendMessage" disabled id="sendMessage" class="form-control" cols="10" rows="5" placeholder=""  onkeydown="Wo_SubmitChatForm(event);" onfocus="Wo_SubmitChatForm(event);" dir="auto"></textarea><input type="hidden" id="user-id" name="user_id" value="" /></form></div></div></div>';
  $('.chat-tab').append('<span class="chat_main chat_main_"></span>');
  $('.chat_main_').append(data_html); */
  $.get(Wo_Ajax_Requests_File(), {
    f: 'chat',
    s: 'load_chat_tab',
    recipient_id: recipient_id,
    placement:placement
  }, function (data) {
    if(data.status == 200) {
      if ($('.chat-wrapper').length == 3) {
         if ($('.chat_main_' + recipient_id).length == 0) {
            $('.chat_main:first-child').remove();
            $('.chat-tab').append('<span class="chat_main chat_main_' + recipient_id +'"></span>');
         } else {
            $('.chat_main_' + recipient_id).remove();
         }
         $('.chat_main_' + recipient_id).html(data.html);
      } else {
        if ($('.chat_main_' + recipient_id).length > 0) {
          $('.chat_main_' + recipient_id).html(data.html);
        } else {
          $('.chat-tab').append('<span class="chat_main chat_main_' + recipient_id +'"></span>');
          $('.chat_main_' + recipient_id).append(data.html);
        }
      }
      $('.chat-wrapper').show();
      loading_div.empty();
      $('.chat-textarea textarea').keyup();
      $.get(Wo_Ajax_Requests_File(), {
        f: 'chat',
        s: 'load_chat_messages',
        recipient_id: recipient_id
      }, function (data) {
        if (data.messages.length > 0) {
           $('.chat-tab').find('.chat_' + recipient_id).find('.chat-messages').html(data.messages);
        } else {
          $('.chat_' + recipient_id).find('.chat-user-desc').fadeIn(150);
        }
        setTimeout(function () {
          $('.chat-messages-wrapper').scrollTop($('.chat-messages-wrapper')[0].scrollHeight);
        }, 1000);
      });
    }
  });
}

function Wo_OpenChatUsersTab() {
  
  $('.chat-container').toggleClass('full');
  $('.online-content-toggler').slideToggle(100);
  $.get(Wo_Ajax_Requests_File(), {
    f: 'chat',
    s: 'open_tab'
  });
}

function Wo_SearchForPosts(query) {
  var type = '';
  if ($('.page-margin').attr('data-page') == "timeline") {
    type = 'user';
  } else if ($('.page-margin').attr('data-page') == "page"){
    type = 'page';
  } else if ($('.page-margin').attr('data-page') == "group") {
    type = 'group'
  } else {
    return false;
  }
  Wo_progressIconLoader($('.search-for-posts-container'));
  var id = $('.page-margin').attr('data-id');
  if (id == null || id == "undefined") {
    return false;
  }
  $.get(Wo_Ajax_Requests_File(), {f:'posts', s:'search_for_posts', id: id, search_query: query, type: type}, function (data) {
     if (data.status == 200) {
        $('#posts').html(data.html);
     }
     Wo_progressIconLoader($('.search-for-posts-container'));
  });
}

function Wo_Fetch(id, post_id) {
   var clickedOnBody = true;
   var user_from_post_id = '#post-' + post_id;
   var user_from_image = '#post-' + post_id + ' .post-heading';
   var div = '.user-fetch-post-' + post_id + '-user-' + id;
   var bla = user_from_post_id + ', ' + div;
   clearTimeout(timeout);
   $(div).fadeIn(200);  
   var timeout;
   function hidepanel() {
     $(div).fadeOut(0); 
   }
    $(div).mouseleave(doTimeout);
    $(user_from_image).mouseleave(doTimeout);
     function doTimeout(){
        clearTimeout(timeout);
        timeout = setTimeout(hidepanel, 0);
     }
}

function Wo_RequestVerification(id, type) {
  var verify_icon = $('form').find('div.verification');
  Wo_progressIconLoader(verify_icon);
  $.get(Wo_Ajax_Requests_File(), {f:'request_verification', id:id, type:type}, function(data) {
    if (data.status == 200) {
      $('#verification-request').html(data.html);
    }
  });
}

function Wo_DeleteUserVerification(id, type) {
  if (confirm("Are you sure ?") == false) {
      return false;
  }
  var verify_icon = $('form').find('div.verification');
  Wo_progressIconLoader(verify_icon);
  $.get(Wo_Ajax_Requests_File(), {f:'delete_verification', id:id, type:type}, function(data) {
    if (data.status == 200) {
      $('#verification-request').html(data.html);
    }
  });
}

function Wo_RemoveVerification(id, type) {
  var verify_icon = $('form').find('div.verification');
  Wo_progressIconLoader(verify_icon);
  $.get(Wo_Ajax_Requests_File(), {f:'remove_verification', id:id, type:type}, function(data) {
    if (data.status == 200) {
      $('#verification-request').html(data.html);
    }
  });
}

$('body').on('mouseenter', '.user-popover', function() {
    var popover = $(this);
    var type = popover.attr('data-type');
    var id = popover.attr('data-id');
    var placement = popover.attr('data-placement') || 'none';
    var placement_code = 'user-details not-profile';
    if (placement == 'profile') {
      placement_code = 'user-details';
    }
    var over_time = setTimeout(function() {
       var offset = popover.offset();
       var posY = (offset.top - $(window).scrollTop()) + popover.height();
       var posX = offset.left - $(window).scrollLeft();
       var available = $(window).width() - posX;
       if ($(window).width() > 800) {
       if (available < 400) {
         var right = available - popover.width();
         if (posY > 0) {
          $('body').append('<div class="' + placement_code + ' right" style="position: fixed; top: ' + posY + 'px; right:' + right + 'px"><div class="loading-user"><div class="fa fa-spinner fa-spin"></div></div></div>');
         }
       } else {
        if (posY > 0) {
         $('body').append('<div class="' + placement_code + '" style="position: fixed; top: ' + posY + 'px; left:' + posX + 'px"><div class="loading-user"><div class="fa fa-spinner fa-spin"></div></div></div>');
        }
       }
       }
       $.get(Wo_Ajax_Requests_File(), {f:'popover', id:id, type:type}, function(data) {
         if (data.status == 200) {
             $('.user-details').html(data.html);
         }
       });
    }, 1000);
    popover.data('timeout', over_time);
});

$('body').on('mouseleave', '.user-popover', function(e) {
    var to = e.toElement || e.relatedTarget;
      if (!$(to).is(".user-details")) {
        clearTimeout($(this).data('timeout'));
        $('.user-details').remove();
      }
});
$('body').on('mouseleave', '.user-details', function() {
    $('.user-details').remove();
});
function Wo_OpenAlbumLightBox(image_id, type) {
  $('body').append('<div class="lightbox-container"><div class="lightbox-backgrond" onclick="Wo_CloseLightbox();"></div><div class="loading-icon lightbox-content"><i class="fa fa-spinner fa-spin"></i></div></div>');
  $.get(Wo_Ajax_Requests_File(), {f:'open_album_lightbox', image_id:image_id, type:type}, function(data) {
    if (data.status == 200) {
      $('.lightbox-container').html(data.html);
    }
  });
}
function Wo_CloseLightbox() {
  $('.lightbox-container').remove();
}
function Wo_OpenLightBox(post_id) {
  if(current_width < 900) {
    window.location.href =  websiteUrl + '/post/' + post_id;
    return false;
  }
  $('#contnet').append('<div class="lightbox-container"><div class="lightbox-backgrond" onclick="Wo_CloseLightbox();"></div><div class="loading-icon lightbox-content"><i class="fa fa-spinner fa-spin"></i></div></div>');
  $.get(Wo_Ajax_Requests_File(), {f:'open_lightbox', post_id:post_id}, function(data) {
    if (data.status == 200) {
      $('.lightbox-container').html(data.html);
    }
  });Wo_OpenAlbumLightBox
}
function Wo_OpenMultiLightBox(url) {
  $('body').append('<div class="lightbox-container"><div class="lightbox-backgrond" onclick="Wo_CloseLightbox();"></div><div class="loading-icon lightbox-content"><i class="fa fa-spinner fa-spin"></i></div></div>');
  $.post(Wo_Ajax_Requests_File() + '?f=open_multilightbox', {url:url}, function(data) {
    if (data.status == 200) {
      $('.lightbox-container').html(data.html);
    }
  });
}
function Wo_NextAlbumPicture(post_id, id) {
  Wo_CloseLightbox();
  $('body').append('<div class="lightbox-container"><div class="lightbox-backgrond" onclick="Wo_CloseLightbox();"></div><div class="loading-icon lightbox-content"><i class="fa fa-spinner fa-spin"></i></div></div>');
  $.get(Wo_Ajax_Requests_File(), {f:'get_next_album_image', post_id:post_id, after_image_id:id}, function(data) {
    if (data.status == 200) {
      $('.lightbox-container').html(data.html);
      $( ".changer").fadeIn(200);
    }
  });
}
function Wo_PreviousAlbumPicture(post_id, id) {
  Wo_CloseLightbox();
  $('body').append('<div class="lightbox-container"><div class="lightbox-backgrond" onclick="Wo_CloseLightbox();"></div><div class="loading-icon lightbox-content"><i class="fa fa-spinner fa-spin"></i></div></div>');
  $.get(Wo_Ajax_Requests_File(), {f:'get_previous_album_image', post_id:post_id, before_image_id:id}, function(data) {
    if (data.status == 200) {
      $('.lightbox-container').html(data.html);
      $( ".changer").fadeIn(200);
    }
  });
}

function Wo_NextPicture(post_id) {
  var id = 0;
  var type = 'none';
  if(typeof ($('[data-page=timeline]').attr('data-id')) == "string") {
    id = $('[data-page=timeline]').attr('data-id');
    type = 'profile';
  } else if(typeof ($('[data-page=page]').attr('data-id')) == "string") {
    id = $('[data-page=page]').attr('data-id');
    type = 'page';
  } else if (typeof ($('[data-page=group]').attr('data-id')) == "string") {
    id = $('[data-page=group]').attr('data-id');
    type = 'group';
  }
   Wo_CloseLightbox();
  $('body').append('<div class="lightbox-container"><div class="lightbox-backgrond" onclick="Wo_CloseLightbox();"></div><div class="loading-icon lightbox-content"><i class="fa fa-spinner fa-spin"></i></div></div>');
  
  $.get(Wo_Ajax_Requests_File(), {f:'get_next_image', post_id:post_id, type:type, id:id}, function(data) {
    if (data.status == 200) {
      $('.lightbox-container').html(data.html);
      $( ".changer" ).fadeIn(200);
    }
  });
}

function Wo_PreviousPicture(post_id) {
  var id = 0;
  var type = 'none';
  if(typeof ($('[data-page=timeline]').attr('data-id')) == "string") {
    id = $('[data-page=timeline]').attr('data-id');
    type = 'profile';
  } else if(typeof ($('[data-page=page]').attr('data-id')) == "string") {
    id = $('[data-page=page]').attr('data-id');
    type = 'page';
  } else if (typeof ($('[data-page=group]').attr('data-id')) == "string") {
    id = $('[data-page=group]').attr('data-id');
    type = 'group';
  }
  Wo_CloseLightbox();
  $('body').append('<div class="lightbox-container"><div class="lightbox-backgrond" onclick="Wo_CloseLightbox();"></div><div class="loading-icon lightbox-content"><i class="fa fa-spinner fa-spin"></i></div></div>');
  $.get(Wo_Ajax_Requests_File(), {f:'get_previous_image', post_id:post_id, type:type, id:id}, function(data) {
    if (data.status == 200) {
      $('.lightbox-container').html(data.html);
      $( ".changer" ).fadeIn(200);
    }
  });
}

function Wo_AcceptJoinGroup(user_id, group_id) {
  $.get(Wo_Ajax_Requests_File(), {f:'groups', s:'accept_request', user_id:user_id, group_id:group_id}, function(data) {
    if (data.status == 200) {
      $('#request-' + user_id).fadeOut(300, function () {
        $(this).remove();
      });
    }
  });
}

function Wo_DeleteJoinGroup(user_id, group_id) {
  $.get(Wo_Ajax_Requests_File(), {f:'groups', s:'delete_request', user_id:user_id, group_id:group_id}, function(data) {
    if (data.status == 200) {
      $('#request-' + user_id).fadeOut(300, function () {
        $(this).remove();
      });
    }
  });
}

function Wo_DeleteJoinedUser(user_id, group_id) {
  $.get(Wo_Ajax_Requests_File(), {f:'groups', s:'delete_joined_user', user_id:user_id, group_id:group_id}, function(data) {
    if (data.status == 200) {
      $('#member-' + user_id).fadeOut(300, function () {
        $(this).remove();
      });
    }
  });
}

function Wo_OpenReplyBox(id) {
  Wo_ViewMoreReplies(id);
   $('#comment_' + id).find('.comment-replies').slideDown(50, function () {
     $('#comment_' + id).find('.comment-reply').slideDown(50);
   });
}
// register post comment
function Wo_RegisterReply(text, comment_id, user_id, event, page_id, type) {
  if(event.keyCode == 13 && event.shiftKey == 0) {
    comment_wrapper = $('[id=comment_' + comment_id + ']');
    reply_textarea = comment_wrapper.find('.comment-replies');
    textarea_wrapper = reply_textarea.find('.textarea');
    reply_list = comment_wrapper.find('.comment-replies-text');
    if(text == '') {
      return false;
    }
    $.post(Wo_Ajax_Requests_File() + '?f=posts&s=register_reply', {
      comment_id: comment_id,
      text: text,
      user_id: user_id,
      page_id: page_id
    }, function (data) {
      textarea_wrapper.val('');
      if(data.status == 200) {
        Wo_OpenReplyBox(comment_id);
        comment_wrapper.find('.reply-container:last-child').after(data.html);
        comment_wrapper.find('[id=comment-replies]').html(data.replies_num);
      }
    });
  }
}

function Wo_ViewMoreReplies(comment_id) {
  main_wrapper = $('[id=comment_' + comment_id + ']');
  view_more_wrapper = main_wrapper.find('.view-more-replies');
  Wo_progressIconLoader(view_more_wrapper);
  $.get(Wo_Ajax_Requests_File(), {
    f: 'posts',
    s: 'load_more_replies',
    comment_id: comment_id
  }, function (data) {
    if(data.status == 200) {
      main_wrapper.find('.comment-replies-text').html(data.html);
      main_wrapper.find('.comment-reply').fadeIn(100);
      view_more_wrapper.remove();
    }
  });
}

function Wo_RegsiterRecent(id, type) {
  $.get(Wo_Ajax_Requests_File(), {
    f: 'register_recent_search',
    id: id,
    type:type
  }, function (data) {
    if(data.status == 200) {
      window.location.href = data.href;
    }
  });
} 

function Wo_RemoveAlbumImage(post_id, id) {
  $.get(Wo_Ajax_Requests_File(), {
    f: 'delete_album_image',
    id: id,
    post_id: post_id
  }, function (data) {
    if(data.status == 200) {
      $('#post-' + post_id).find('#image-' + id).fadeOut(200, function () {
        $(this).remove();
      });
    }
  });
}
function Wo_ShowDeleteButton(post_id, id) {
  $('#post-' + post_id).find('#image-' + id).find('span').fadeIn(200);
}
function Wo_HideDeleteButton(post_id, id) {
  $('#post-' + post_id).find('#image-' + id).find('span').fadeOut(200);
}
function Wo_RegisterInvite(user_id, page_id) {
  Wo_progressIconLoader($('#invite-' + user_id).find('button'));
  $.get(Wo_Ajax_Requests_File(), {
    f: 'register_page_invite',
    user_id: user_id,
    page_id: page_id
  }, function (data) {
    if(data.status == 200) {
      $('#invite-' + user_id).fadeOut(200, function () {
        $(this).remove();
      });
    }
  });
}

function Wo_RegisterAddGroup(user_id, group_id) {
  Wo_progressIconLoader($('#add-' + user_id).find('button'));
  $.get(Wo_Ajax_Requests_File(), {
    f: 'register_group_add',
    user_id: user_id,
    group_id: group_id
  }, function (data) {
    if(data.status == 200) {
      $('#add-' + user_id).fadeOut(200, function () {
        $(this).remove();
      });
    }
  });
}

function Wo_SkipStep(type) {
  $.get(Wo_Ajax_Requests_File(), {
    f: 'skip_step',
    type: type
  }, function (data) {
    if(data.status == 200) {
     window.location.reload();
    }
  });
}
function Wo_AddEmoToCommentInput(post_id, code) {
    inputTag = $('[id=post-' + post_id + ']').find('.comment-textarea');
    inputVal = inputTag.val();
    if (typeof(inputTag.attr('placeholder')) != "undefined") {
        inputPlaceholder = inputTag.attr('placeholder');
        if (inputPlaceholder == inputVal) {
            inputTag.val('');
            inputVal = inputTag.val();
        }
    }
    if (inputVal.length == 0) {
        inputTag.val(code + ' ');
    } else {
        inputTag.val(inputVal + ' ' + code);
    }
    inputTag.keyup().focus();
}
function Wo_SendMessages() {
  $.get(Wo_Ajax_Requests_File(), {f: 'send_mails'});
}
// request permission on page load
document.addEventListener('DOMContentLoaded', function () {
  if (Notification.permission !== "granted")
    Notification.requestPermission();
});

function Wo_NotifyMe(icon, title, notification_text, url) {
  if (!Notification) {
    return;
  }
  if (Notification.permission !== "granted")
    Notification.requestPermission();
  else {
    var notification = new Notification(title, {
      icon: icon,
      body: notification_text,
    });
    
    notification.onclick = function () {
      window.open(url);
      notification.close();
      Wo_OpenNotificationsMenu();    
    };
  }
}
function Wo_CheckForCallAnswer(id) {
  $.get(Wo_Ajax_Requests_File(), {f:'check_for_answer', id:id}, function (data1) {
    if (data1.status == 200) {
      clearTimeout(checkcalls);
      $('#calling-modal').find('.modal-title').html('<i class="fa fa fa-video-camera"></i> ' + data1.text_answered);
      $('#calling-modal').find('.modal-body p').text(data1.text_please_wait);
      setTimeout(function () {
          window.location.href = data1.url;
      }, 1000);
      return false;
    } else if (data1.status == 400) {
      clearTimeout(checkcalls);
      Wo_PlayAudioCall('stop');
      $('#calling-modal').find('.modal-title').html('<i class="fa fa fa-times"></i> ' + data1.text_call_declined);
      $('#calling-modal').find('.modal-body p').text(data1.text_call_declined_desc);
    }
    checkcalls = setTimeout(function () {
        Wo_CheckForCallAnswer(id);
    }, 2000);
  });
}

function Wo_AnswerCall(id, url) {
  Wo_progressIconLoader($('#re-calling-modal').find('.answer-call'));
  $.get(Wo_Ajax_Requests_File(), {f:'answer_call', id:id}, function (data) {
    Wo_PlayVideoCall('stop');
    if (data.status == 200) {
      window.location.href = url;
    }
    Wo_progressIconLoader($('#re-calling-modal').find('.answer-call'));
  });
}
function Wo_DeclineCall(id, url) {
  Wo_progressIconLoader($('#re-calling-modal').find('.decline-call'));
  $.get(Wo_Ajax_Requests_File(), {f:'decline_call', id:id}, function (data) {
    if (data.status == 200) {
      Wo_PlayVideoCall('stop');
      $( '#re-calling-modal' ).remove();
      $( '.modal-backdrop' ).remove();
      $( 'body' ).removeClass( "modal-open" );
    }
  });
}

function Wo_CancelCall() {
  Wo_progressIconLoader($('#calling-modal').find('.cancel-call'));
  $.get(Wo_Ajax_Requests_File(), {f:'cancel_call'}, function (data) {
    if (data.status == 200) {
      Wo_PlayAudioCall('stop');
      $( '#calling-modal' ).remove();
      $( '.modal-backdrop' ).remove();
      $( 'body' ).removeClass( "modal-open" );
    }
  });
}
function Wo_GenerateVideoCall(user_id1, user_id2) {
  $.get(Wo_Ajax_Requests_File(), {f:'create_new_video_call', 'new': 'true', user_id1: user_id1, user_id2:user_id2}, function(data) {
      if (data.status == 200) {
          $('body').append(data.html);
           $('#calling-modal').modal({
             show: true
           });
           checkcalls = setTimeout(function () {
              Wo_CheckForCallAnswer(data.id);
           }, 2000);
           setTimeout(function() {
            $('#calling-modal').find('.modal-title').html('<i class="fa fa fa-video-camera"></i> ' + data.text_no_answer);
            $('#calling-modal').find('.modal-body p').text(data.text_please_try_again_later);
            clearTimeout(checkcalls);
            Wo_PlayAudioCall('stop');
           }, 43000);
          Wo_PlayAudioCall('play');
    }
   });
}
function Wo_PlayAudioCall(type) {
  if (type == 'play') {
    document.getElementById('calling-sound').play();
    playmusic_ = setTimeout(function() {
       Wo_PlayAudioCall('play');
    }, 100);
  } else {
    clearTimeout(playmusic_);
    document.getElementById('calling-sound').pause();
  }
}
function Wo_PlayVideoCall(type) {
  if (type == 'play') {
    document.getElementById('video-calling-sound').play();
    playmusic = setTimeout(function() {
       Wo_PlayVideoCall('play');
    }, 100);
  } else {
    clearTimeout(playmusic);
    document.getElementById('video-calling-sound').pause();
  }
}
function textAreaAdjust(o, h) {
    o.style.height = h + 'px';
    o.style.height = (5+o.scrollHeight)+"px";
}

function Wo_MarkAsSold(post_id, product_id) {
  var post = $('#post-' + post_id);
  Wo_progressIconLoader(post.find('.mark-as-sold-post'));
  $.get(Wo_Ajax_Requests_File(), {
    f: 'posts',
    s: 'mark_as_sold_post',
    post_id: post_id,
    product_id: product_id
  }, function (data) {
    if(data.status == 200) {
      post.find('.product-status').text(data.text);
      post.find('.mark-as-sold-post').html('<i class="fa fa-fw fa-check progress-icon boosted-text" data-icon="check"></i> ' + data.text);
    }
  });
}

function Wo_VoteUp(id) {
  var $vote_con = $('#option-' + id);
  var $post_id = $vote_con.attr('data-post-id');
  if ($post_id.length == 0) {
     return false;
  }
  $is_voted = $('#post-' + $post_id).find('.options').attr('data-vote');
  if ($is_voted.length == 0) {
     return false;
  }
  if ($is_voted == 'false') {
     $vote_con.find('.vote-icon').html('<i class="fa fa-check-circle"></i>');
  }
  $('#post-' + $post_id).find('.options').attr('data-vote', true);
  $.get(Wo_Ajax_Requests_File(), {f:'vote_up', id:id}, function (data) {
       if (data.status == 200) {
        $('#post-' + $post_id).find('.total-votes').removeClass('hidden');
        data.votes.forEach(function(option) {
          $('#post-' + $post_id).find('#total-votes').text(option.all);
          $('#option-' + option.id).find('.answer-vote').html(option.percentage);
          if (option.percentage_num > 0) {
            $('#option-' + option.id).find('.result-bar').text(' ').css('width', option.percentage);
          }
        });
      } else if (data.status == 400) {
         alert(data.text);
      } 
  });
}


function Wo_UploadCommentImage(id) {
  var image_container = $('#post-' + id);
  var fetched_image = image_container.find('#comment-image');
  var data = new FormData();
  data.append('image', $('#comment_image_' + id).prop('files')[0]);
  Wo_progressIconLoader(image_container.find('.btn-upload-comment'));
  $.ajax({
        type: "POST",
        url: Wo_Ajax_Requests_File() + '?f=upload_image&id=' + id,
        data: data,
        processData: false,
        contentType: false,
        success: function (data) {
          if (data.status == 200) {
            fetched_image.html('<img src="' + data.image + '"><div class="remove-icon" onclick="Wo_EmptyCommentImage(' + id + ')"><i class="fa fa-trash"></i></div>');
            image_container.find('#comment_src_image').val(data.image_src);
            fetched_image.removeClass('hidden');
            image_container.find('.comment-textarea').focus();
          }
          Wo_progressIconLoader(image_container.find('.btn-upload-comment'));
        }
    });
}

function Wo_EmptyCommentImage(id) {
  var image_container = $('#post-' + id);
  var fetched_image = image_container.find('#comment-image');
  image_container.find('.comment-image-con').empty().addClass('hidden');
  image_container.find('#comment_src_image').val('');
  image_container.find('#comment_src_image').val('');
  image_container.find('#comment_image_' + id).val('');
}

function Wo_TurnOffSound() {
  var sound = $('.turn-off-sound');
  Wo_progressIconLoader(sound);
  $.get(Wo_Ajax_Requests_File(), {
    f: 'turn-off-sound'
  }, function (data) {
    if(data.status == 200) {
      sound.find('span').html(data.message);
    }
  });
}