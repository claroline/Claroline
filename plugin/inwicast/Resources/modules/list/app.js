import $ from 'jquery'

(function ($) {
  window.select = function (mediaTitle, mediaNb) {
    $('#inwicast_widget_name').val(mediaTitle)
    $('#inwicast_widget_submit').prop('disabled', false)
    var items = $('#inwicast-items .thumbnail.item-selected')
    $(items).each(function (index, element) {
      $(element).removeClass('item-selected')
    })
    $('#inwicast-items .thumbnail').eq(mediaNb).addClass('item-selected')
  }

  window.setDisplay = function (type) {
    if (type === 'grid') {
      $('.list-media').css('display', 'none')
      $('.media').css('display', 'block')
      $('#setList').removeClass('active')
      $('#setGrid').addClass('active')
      searchForMedia(null)
    }
    else if (type === 'list') {
      $('.media').css('display', 'none')
      $('.list-media').css('display', 'block')
      $('#setList').addClass('active')
      $('#setGrid').removeClass('active')
      searchForMedia(null)
    }
  }

  window.getSelectedMediaIframe = function ()
  {
    var selectedMedia = $('input:radio[name=\'media_ref\']:checked')
    if (selectedMedia.length > 0) {
      var path = window.inwicastMediaViewPath
      path = path.replace('_mediaRef_', selectedMedia.val())
      return '<iframe src=\''+path+'\' width=\''+selectedMedia.data('width')+'\' height=\''+selectedMedia.data('height')+'\' frameborder=\'0\' scrolling=\'no\' marginwidth=\'0\' marginheight=\'0\'></iframe>'
    }

    return ''
  }

  function searchForMedia(event) {
    if (event !== null) event.preventDefault()
    // Get value from input
    var value = $('#search-inwicast').val()

    // Don't search if input is empty...
    if (value !== '') {
      // AJAX request
      $.ajax({
        url: '{{ path(\'inwicast_mediacenter_user_videos_search\') }}?keywords=' + value,
        cache: false
      })
        .done(function (json) {
          // Hide all items
          $('.thumbnail').css('display', 'none')
          $(json.videos).each(function (index, element) {
            // Display element matching with search
            $('#thumb-'+element.mediaRef).css('display', 'block')
          })
        })
    }
    // But display all items!
    else {
      $('.thumbnail').css('display', 'block')
    }
  }

  $('#search-inwicast').on('keypress', function (ev){
    var keycode = (ev.keyCode ? ev.keyCode : ev.which)
    if (keycode === 13) {
      searchForMedia(ev)
      ev.preventDefault()
    }
  })

})($)