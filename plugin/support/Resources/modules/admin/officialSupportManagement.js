/*global Routing*/
/*global Translator*/
import $ from 'jquery'

const supportUrl = 'https://api.claroline.cloud/cc'

$('#official-support-management-body').on('click', '#support-token-validate-btn', function () {
  const token = $('#support-token-input').val()
  const platformUrl = $('#support-platform-url-input').val()

  if (validate()) {
    $.ajax({
      url: `${supportUrl}/support/register`,
      type: 'POST',
      data: JSON.stringify({token: token, platformUrl: platformUrl}),
      success: () => {
        $.ajax({
          url: Routing.generate('formalibre_admin_support_token_register'),
          type: 'POST',
          data: {
            token: token,
            platformUrl: platformUrl
          }
        })
        $('#official-support-form').hide('slow')
      },
      error: (response) => {
        const errorMsg = response.responseText ?
          response.responseText :
          Translator.trans('invalid_support_token', {}, 'support')
        $('#support-token-form-row').addClass('has-error')
        $('#token-error-block').html(errorMsg)
      }
    })
  }
})

const validate = () => {
  let isValid = true
  const token = $('#support-token-input').val().trim()
  const platformUrl = $('#support-platform-url-input').val().trim()

  if (token === '') {
    isValid = false
    $('#support-token-form-row').addClass('has-error')
    $('#token-error-block').html(Translator.trans('form_not_blank_error', {}, 'support'))
  } else {
    $('#token-error-block').html('')
    $('#support-token-form-row').removeClass('has-error')
  }
  if (platformUrl === '') {
    isValid = false
    $('#support-platform-url-form-row').addClass('has-error')
    $('#platform-url-error-block').html(Translator.trans('form_not_blank_error', {}, 'support'))
  } else {
    $('#platform-url-error-block').html('')
    $('#support-platform-url-form-row').removeClass('has-error')
  }

  return isValid
}