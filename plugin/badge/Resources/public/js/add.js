$(function () {
  var badgeFormFile = $('#badge_form_file')

  $('.nav-tabs a.has-error:first').tab('show')

  var uploadImagePlaceholder = $('.upload_image_placeholder')
  uploadImagePlaceholder.click(function (event) {
    badgeFormFile.click()
    event.preventDefault()
  })

  badgeFormFile.change(function () {
    var input = this
    if (input.files && input.files[0]) {
      var reader = new FileReader()

      reader.onload = function (event) {
        var previewImage = $('<img class="badge_image" src="">')
        previewImage.attr('src', event.target.result)
        uploadImagePlaceholder.html(previewImage)
      }

      reader.readAsDataURL(input.files[0])
    }
  })

  $('[data-toggle=popover]').popover()

  var expiringPeriodDurationBlock = $('#expiring_period_duration')

  $('#badge_form_is_expiring').click(function () {
    if (this.checked) {
      expiringPeriodDurationBlock.show()
    } else {
      expiringPeriodDurationBlock.hide()
    }
  })
})
