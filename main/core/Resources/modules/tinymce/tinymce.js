import $ from 'jquery'
import _ from 'underscore'
import 'claroline-tinymce-mention/plugin.min'
import 'tinymce-codemirror/plugins/codemirror/plugin.min'
import './plugins/codemirror'

var tinymce = window.tinymce
var home = window.Claroline.Home
var translator = window.Translator
var routing = window.Routing

tinymce.DOM.loadCSS(home.asset + 'packages/claroline-tinymce-mention/css/autocomplete.css')
//tinymce.DOM.loadCSS(home.asset + 'packages/font-awesome/css/font-awesome.min.css')

var codemirrorPath = home.asset + 'packages/tinymce-codemirror/plugins/codemirror/codemirror-4.8'

/**
 * Claroline TinyMCE parameters and methods.
 */
tinymce.claroline = {
  'disableBeforeUnload': false,
  'domChange': null,
  'buttons': {},
  'plugins': {},
  'css': []
}

tinymce.claroline.init = tinymce.claroline.init || {}
tinymce.claroline.plugins = tinymce.claroline.plugins || {}
tinymce.claroline.css = tinymce.claroline.css || []
tinymce.claroline.addCss = css => tinymce.claroline.css.push(css)

/**
 * This method fix the height of TinyMCE after modify it,
 * this is usefull when change manually something in the editor.
 *
 * @param editor A TinyMCE editor object.
 */
tinymce.claroline.editorChange = function (editor) {
  setTimeout(function () {
    var container = $(editor.getContainer()).find('iframe').first()
    var height = container.contents().height()
    var max = 'autoresize_max_height'
    var min = 'autoresize_min_height'

    switch (true) {
      case ( height <= tinymce.claroline.configuration[min]):
        container.css('height', tinymce.claroline.configuration[min])
        break
      case ( height >= tinymce.claroline.configuration[max]):
        container.css('height', tinymce.claroline.configuration[max])
        container.css('overflow', 'scroll')
        break
      default:
        container.css('height', height)
    }
  }, 500)
}

/**
 * This method if fired when paste in a TinyMCE editor.
 *
 *  @param plugin TinyMCE paste plugin object.
 *  @param args TinyMCE paste plugin arguments.
 *
 */
tinymce.claroline.paste = function (plugin, args) {
  if ($('#platform-configuration').attr('data-enable-opengraph') === '1') {
    var link = $('<div>' + args.content + '</div>').text().trim() // inside div because a bug of jquery

    home.canGenerateContent(link, function (data) {
      tinymce.activeEditor.insertContent('<div>' + data + '</div>')
      tinymce.claroline.editorChange(tinymce.activeEditor)
    })
  }
}

/**
 * Check if a TinyMCE editor has change.
 *
 * @return boolean.
 *
 */
tinymce.claroline.checkBeforeUnload = function () {
  if (!tinymce.claroline.disableBeforeUnload) {
    for (var id in tinymce.editors) {
      if (tinymce.editors.hasOwnProperty(id) &&
        tinymce.editors[id].isBeforeUnloadActive &&
        tinymce.editors[id].getContent() !== '' &&
        $(tinymce.editors[id].getElement()).data('saved')
      ) {
        return true
      }
    }
  }

  return false
}

/**
 * Set the edition detection parameter for a TinyMCE editor.
 *
 * @param editor A TinyMCE editor object.
 *
 */
tinymce.claroline.setBeforeUnloadActive = function (editor) {
  if ($(editor.getElement()).data('before-unload') !== 'off') {
    editor.isBeforeUnloadActive = true
  } else {
    editor.isBeforeUnloadActive = false
  }
}

/**
 * Add or remove fullscreen class name in a modal containing a TinyMCE editor.
 *
 * @param editor A TinyMCE editor object.
 *
 */
tinymce.claroline.toggleFullscreen = function (element) {
  $(element).parents('.modal').first().toggleClass('fullscreen')
}

/**
 * Setup configuration of TinyMCE editor.
 *
 * @param editor A TinyMCE editor object.
 *
 */
tinymce.claroline.setup = function (editor) {
  editor.on('change', function () {
    if (editor.getElement()) {
      editor.getElement().value = editor.getContent()
      if (editor.isBeforeUnloadActive) {
        $(editor.getElement()).data('saved', 'false')
        tinymce.claroline.disableBeforeUnload = false
      }
    }
  }).on('LoadContent', function () {
    tinymce.claroline.editorChange(editor)
    tinymce.claroline.customInit(editor)
  })

  editor.on('BeforeRenderUI', function () {
    editor.theme.panel.find('toolbar').slice(1).hide()
  })

  // Add a button that toggles toolbar 1+ on/off
  editor.addButton('displayAllButtons', {
    'icon': 'none fa fa-chevron-down',
    'classes': 'widget btn',
    'tooltip': translator.trans('tinymce_all_buttons', {}, 'platform'),
    onclick: function () {
      if (!this.active()) {
        this.active(true)
        editor.theme.panel.find('toolbar').slice(1).show()
      } else {
        this.active(false)
        editor.theme.panel.find('toolbar').slice(1).hide()
      }
    }
  })

  tinymce.claroline.setBeforeUnloadActive(editor)

  $('body').bind('ajaxComplete', function () {
    setTimeout(function () {
      if (editor.getElement() && editor.getElement().value === '') {
        editor.setContent('')
      }
    }, 200)
  })
}

/**
 * @todo documentation
 */
tinymce.claroline.mentionsSource = function (query, process, delimiter) {
  if (!_.isUndefined(window.Workspace) && !_.isNull(window.Workspace.id)) {
    if (delimiter === '@' && query.length > 0) {
      var searchUserInWorkspaceUrl = routing.generate('claro_user_search_in_workspace') + '/'

      $.getJSON(searchUserInWorkspaceUrl + window.Workspace.id + '/' + query, function (data) {
        if (!_.isEmpty(data) && !_.isUndefined(data.users) && !_.isEmpty(data.users)) {
          process(data.users)
        }
      })
    }
  }
}

/**
 * @todo documentation
 */
tinymce.claroline.mentionsItem = function (item) {
  var avatar = '<i class="fa fa-user"></i>'
  if (item.avatar !== null) {
    avatar = '<img src="' + home.asset + 'uploads/pictures/' + item.avatar + '" alt="' + item.name +
      '" class="img-responsive">'
  }

  return '<li>' +
  '<a href="javascript:;"><span class="user-picker-dropdown-avatar">' + avatar + '</span>' +
  '<span class="user-picker-dropdown-name">' + item.name + '</span>' +
  '<small class="user-picker-avatar-email text-muted">(' + item.email + ')</small></a>' +
  '</li>'
}

/**
 * @todo documentation
 */
tinymce.claroline.mentionsInsert = function (item) {
  var publicProfileUrl = routing.generate('claro_user_profile') + '/'

  return '<user id="' + item.id + '"><a href="' + publicProfileUrl + item.id + '">' + item.name + '</a></user>'
}

/**
 * Configuration and parameters of a TinyMCE editor.
 */

// Get theme to load inside tinymce in order to have no display differences
var homeTheme = document.getElementById('homeTheme')
var themeCSS = homeTheme.innerText || homeTheme.textContent

// remap locale (we only have the short version without localization part)
const currentLocale = home.locale.trim()
const tinyMceLocale = {
  en: 'en_GB',
  fr: 'fr_FR'
}

tinymce.claroline.configuration = {
  'paste_data_images': true,
  'relative_urls': false,
  'remove_script_host': false,
  'theme': 'modern',
  'language': tinyMceLocale[currentLocale] || currentLocale,
  'browser_spellcheck': true,
  'autoresize_min_height': 100,
  'autoresize_max_height': 500,
  'content_css': [
    themeCSS,
    //home.asset + 'bundles/clarolinecore/css/common/tinymce.css',
    home.asset + 'packages/font-awesome/css/font-awesome.min.css'
  ],
  'toolbar2': 'styleselect | undo redo | forecolor backcolor | bullist numlist | outdent indent | ' +
    'media link charmap | print preview code',
  'extended_valid_elements': 'user[id], a[data-toggle|data-parent], span[*]',
  'paste_preprocess': tinymce.claroline.paste,
  'setup': tinymce.claroline.setup,
  'mentions': {
    'source': tinymce.claroline.mentionsSource,
    'render': tinymce.claroline.mentionsRender,
    'insert': tinymce.claroline.mentionsInsert,
    'delay': 200
  },
  'picker': {
    'openResourcesInNewTab': false
  },
  'codemirror': {
    'path': codemirrorPath
  }
}

tinymce.claroline.customInit = function (editor) {
  $.each(tinymce.claroline.init, function (key, func) {
    func(editor)
  })
}

/**
 * Initialization function for TinyMCE editors.
 */
tinymce.claroline.initialization = function () {
  $('textarea.claroline-tiny-mce:not(.tiny-mce-done)').each(function () {
    var element = $(this)
    var config = null

    var plugins = [
      'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
      'searchreplace wordcount visualblocks visualchars fullscreen',
      'insertdatetime media nonbreaking save table directionality',
      'template paste textcolor emoticons code -mention -accordion -codemirror'
    ]

    var toolbar1 = 'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | fullscreen displayAllButtons'

    $.each(tinymce.claroline.plugins, function (key, value) {
      if (value === true) {
        plugins.push(key)
        toolbar1 += ' ' + key
      }
    })

    tinymce.claroline.configuration.content_css = tinymce.claroline.configuration.content_css.concat(tinymce.claroline.css)
    tinymce.claroline.configuration.plugins = plugins
    tinymce.claroline.configuration.toolbar1 = toolbar1

    if (element.data('newTab') === 'yes') {
      config = _.extend({}, tinymce.claroline.configuration)
      config.picker.openResourcesInNewTab = true
    } else {
      config = tinymce.claroline.configuration
    }

    element.tinymce(config)
      .on('remove', function () {
        var editor = tinymce.get(element.attr('id'))
        if (editor) {
          editor.destroy()
        }
      })
      .addClass('tiny-mce-done')
  })
}

/** Events **/

$('body').bind('ajaxComplete', function () {
  tinymce.claroline.initialization()
})
  .on('click', '.mce-widget.mce-btn[aria-label="Fullscreen"]', function () {
    tinymce.claroline.toggleFullscreen(this)
    $(window).scrollTop($(this).parents('.mce-tinymce.mce-container.mce-panel').first().offset().top)
    window.dispatchEvent(new window.Event('resize'))
  })
  .bind('DOMSubtreeModified', function () {
    clearTimeout(tinymce.claroline.domChange)
    tinymce.claroline.domChange = setTimeout(tinymce.claroline.initialization, 10)
  })
  .on('click', 'form *[type=submit]', function () {
    tinymce.claroline.disableBeforeUnload = true
  })

$(document).ready(function () {
  tinymce.claroline.initialization()
})

$(window).on('beforeunload', function () {
  if (tinymce.claroline.checkBeforeUnload()) {
    return translator.trans('leave_this_page', {}, 'platform')
  }
})
