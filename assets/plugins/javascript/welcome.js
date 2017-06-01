$(function() {
  LoginLink = $('#login-form-link');
  LoginForm = $('#login-form');
  RegisterLink = $('#register-form-link');
  RegisterForm = $('#register-form');
  ForgetLink = $('#forget-form-link');
  ForgetForm = $('#forget-form');
  ConfirmForm = $('#confirm-form');
  LoginFormANDRegisterForm = $("#login-form, #register-form");
  LoginLinkANDRegisterLink = $('#login-form-link, #register-form-link');
  ActiveClass = 'active';
  LoginLink.click(function(e) { e.preventDefault();
    ForgetForm.fadeOut(100);
    RegisterForm.fadeOut(100, function() {
      LoginForm.fadeIn(300);
    });
    RegisterLink.removeClass(ActiveClass);
    $(this).addClass(ActiveClass);
  });
  RegisterLink.click(function(e) { e.preventDefault();
    ForgetForm.fadeOut(100);
    LoginForm.fadeOut(100, function() {
      RegisterForm.fadeIn(300);
    });
    LoginLink.removeClass(ActiveClass);
    $(this).addClass(ActiveClass);
  });
  ForgetLink.click(function(e) { e.preventDefault();
    LoginFormANDRegisterForm.fadeOut(100, function() {
      ForgetForm.fadeIn(300);
    });
    LoginLinkANDRegisterLink.removeClass(ActiveClass);
  });

  // Login

  $('#login-form').ajaxForm({
    url: Wo_Ajax_Requests_File() + '?f=login',
    beforeSend: function() {
      Wo_progressIconLoader($('#login-form').find('button'));
      $('#login-submit').attr('disabled',true);
    },
    success: function(data) {
      if (data.status == 200) {
        window.location.href = data.location;
      } else if(data.status == 600) {
        window.location.href = data.location;
      } else {
        $('#login-submit').attr('disabled',false);
        var errors = data.errors.join("<br>");
        $('#login-alerts').html('<div class="error-container">' + errors + '</div>');
        $('.error-container').fadeIn(300);
      }
      Wo_progressIconLoader($('#login-form').find('button'));
    }
  });

  $('#confirm-form').ajaxForm({
    url: Wo_Ajax_Requests_File() + '?f=confirm_user',
    beforeSend: function() {
      Wo_progressIconLoader($('#confirm-form').find('button'));
      $('#confirm-submit').attr('disabled',true);
    },
    success: function(data) {
      if (data.status == 200) {
          window.location.href = data.location;
      } else {
        $('#confirm-submit').attr('disabled',false);
        var errors = data.errors.join("<br>");
        $('#confirm-alerts').html('<div class="error-container">' + errors + '</div>');
        $('.error-container').fadeIn(300);
      }
      Wo_progressIconLoader($('#confirm-form').find('button'));
    }
  });

  // Register
   
  $('#register-form').ajaxForm({
    url: Wo_Ajax_Requests_File() + '?f=register',
    beforeSend: function() {
      Wo_progressIconLoader($('#register-form').find('button'));
      $('#register-submit').attr('disabled',true);
    },
    success: function(data) {
      if (data.status == 200) {
        $('#register-alerts').html('<div class="success-container">' + data.message + '</div>');
        $('.success-container').fadeIn('fast', function() {
          window.setTimeout(function() {
            window.location.href = data.location;
          }, 1500);
        });
      } else if(data.status == 300) {
        $('#confirm-user-id').val(data.user_id);
        $('#phone-num').val(data.phone_num)
        LoginFormANDRegisterForm.fadeOut(100, function() {
            ConfirmForm.fadeIn(300);
            $('.panel-heading').fadeOut(100);
        });
        LoginLinkANDRegisterLink.removeClass(ActiveClass);
        Wo_SetTimer();
      } else {
        $('#register-submit').attr('disabled', false);
        var errors = data.errors.join("<br>");
        $('#register-alerts').html('<div class="error-container">' + errors + '</div>');
        $('.error-container').fadeIn(300);
      }
      Wo_progressIconLoader($('#register-form').find('button'));
    }
  });

  // Forget
   
  $('#forget-form').ajaxForm({
    url: Wo_Ajax_Requests_File() + '?f=recover',
    beforeSend: function() {
      Wo_progressIconLoader($('#forget-form').find('button'));
      $('#recover-submit').attr('disabled',true);
    },
    success: function(data) {
      if (data.status == 200) {
        $('#forget-alerts').html('<div class="success-container">' + data.message + '</div>');
        $('.success-container').fadeIn('fast');
      } else {
        $('#recover-submit').attr('disabled',false);
        var errors = data.errors.join("<br>");
        $('#forget-alerts').html('<div class="error-container">' + errors + '</div>');
        $('.error-container').fadeIn(300);
      }
      Wo_progressIconLoader($('#forget-form').find('button'));
    }
  });

  // Reset-Password
   
  $('#reset-password-form').ajaxForm({
    url: Wo_Ajax_Requests_File() + '?f=reset_password',
    beforeSend: function() {
      Wo_progressIconLoader($('#reset-password-form').find('button'));
      $('#reset-password-submit').attr('disabled',true);
    },
    success: function(data) {
      if (data.status == 200) {
        $('#reset-alerts').html('<div class="success-container">' + data.message + '</div>');
        $('.success-container').fadeIn('fast', function() {
          window.setTimeout(function() {
            window.location.href = data.location;
          }, 1500);
        });
      } else {
        $('#reset-password-submit').attr('disabled',false);
        var errors = data.errors.join("<br>");
        $('#reset-alerts').html('<div class="error-container">' + errors + '</div>');
        $('.error-container').fadeIn(300);
      }
      Wo_progressIconLoader($('#reset-password-form').find('button'));
    }
  });

});

function Wo_ResendCode() {
  var user_id = $('#confirm-user-id').val();
  var phone_number = $('#phone-num').val();
  $('#re-send').hide();
  Wo_SetTimer();
  $.post(Wo_Ajax_Requests_File() + '?f=resned_code', {user_id:user_id, phone_number:phone_number}, function(data) {
       if (data.status == 200) {
          $('#confirm-alerts').html('<div class="success-container">' + data.message + '</div>');
          $('.success-container').fadeIn('fast');
      } else {
          $('#confirm-submit').attr('disabled',false);
          var errors = data.errors.join("<br>");
          $('#confirm-alerts').html('<div class="error-container">' + errors + '</div>');
          $('.error-container').fadeIn(300);
      }
  });
}

function Wo_SetTimer() {
  $('#hideMsg h2 span').text('60');
  $('#hideMsg').fadeIn('fast');
  var sec = $('#hideMsg h2 span').text();
  var timer = setInterval(function() { 
  $('#hideMsg h2 span').text(--sec);
  if (sec == 0) {
      $('#hideMsg').fadeOut('fast', function () {
        clearInterval(timer);
        $('#re-send').fadeIn('fast');
      });
  } 
  }, 1000);
}