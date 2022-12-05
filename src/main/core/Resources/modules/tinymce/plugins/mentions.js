import $ from 'jquery'
import invariant from 'invariant'
import tinymce from 'tinymce/tinymce'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'

import {route} from '#/main/community/user/routing'

const AutoComplete = function (editor) {
  this.editor = editor

  this.query = ''
  this.hasFocus = true
  this.options = {
    delay: 500,
    delimiter: '@',
    queryBy: 'name',
    items: 10
  }

  this.renderInput()
  this.bindEvents()
}

AutoComplete.prototype = {
  constructor: AutoComplete,

  renderInput: function () {
    const rawHtml =  '<span id="autocomplete">' +
      '<span id="autocomplete-delimiter">' + this.options.delimiter + '</span>' +
      '<span id="autocomplete-searchtext"><span class="dummy">\uFEFF</span></span>' +
      '</span>'

    this.editor.execCommand('mceInsertContent', false, rawHtml)
    this.editor.focus()
    this.editor.selection.select(this.editor.selection.dom.select('span#autocomplete-searchtext span')[0])
    this.editor.selection.collapse(0)
  },

  bindEvents: function () {
    this.editor.on('keyup', this.editorKeyUpProxy = $.proxy(this.rteKeyUp, this))
    this.editor.on('keydown', this.editorKeyDownProxy = $.proxy(this.rteKeyDown, this))
    this.editor.on('click', this.editorClickProxy = $.proxy(this.rteClicked, this))

    $(this.editor.getWin()).on('scroll', this.rteScroll = $.proxy(function () { this.cleanUp(true) }, this))
    $('body').on('click', this.bodyClickProxy = $.proxy(this.rteLostFocus, this))
  },

  unbindEvents: function () {
    this.editor.off('keyup', this.editorKeyUpProxy)
    this.editor.off('keydown', this.editorKeyDownProxy)
    this.editor.on('click', this.editorClickProxy)
    $(this.editor.getWin()).off('scroll', this.rteScroll)
    $('body').off('click', this.bodyClickProxy)
  },

  rteKeyUp: function (e) {
    let item
    switch (e.which || e.keyCode) {
      case 40: //DOWN ARROW
      case 38: //UP ARROW
      case 16: //SHIFT
      case 17: //CTRL
      case 18: //ALT
        break

      case 8: //BACKSPACE
        if (this.query === '') {
          this.cleanUp(true)
        } else {
          this.lookup()
        }
        break

      case 9: //TAB
      case 13: //ENTER
        item = this.$dropdown !== undefined ? this.$dropdown.find('li.active') : []
        if (item.length) {
          this.select(item.data('item'))
          this.cleanUp(false)
        } else {
          this.cleanUp(true)
        }
        break

      case 27: //ESC
        this.cleanUp(true)
        break

      default:
        this.lookup()
        break
    }
  },

  rteKeyDown: function (e) {
    switch (e.which || e.keyCode) {
      case 9: //TAB
      case 13: //ENTER
      case 27: //ESC
        e.preventDefault()
        break

      case 38: //UP ARROW
        e.preventDefault()
        if (this.$dropdown !== undefined) {
          this.highlightPreviousResult()
        }
        break

      case 40: //DOWN ARROW
        e.preventDefault()
        if (this.$dropdown !== undefined) {
          this.highlightNextResult()
        }
        break
    }

    e.stopPropagation()
  },

  rteClicked: function (e) {
    if (this.hasFocus && $(e.target).parent().attr('id') !== 'autocomplete-searchtext') {
      this.cleanUp(true)
    }
  },

  rteLostFocus: function () {
    if (this.hasFocus) {
      this.cleanUp(true)
    }
  },

  lookup: function () {
    this.query = $.trim($(this.editor.getBody()).find('#autocomplete-searchtext').text()).replace('\ufeff', '')

    if (this.$dropdown === undefined) {
      this.show()
    }

    clearTimeout(this.searchTimeout)
    this.searchTimeout = setTimeout($.proxy(function () {
      this.search(this.query, $.proxy(this.process, this))
    }, this), this.options.delay)
  },

  search: function (query, process) {
    if (query && 0 < query.length) {
      fetch(url(['apiv2_user_list'], {
        filters: {
          [this.options.queryBy]: query
        },
        sortBy: this.options.queryBy,
        limit: this.options.items
      }), {
        credentials: 'include'
      })
        .then(response => {
          if (response.ok) {
            return response.json()
          }
        })
        .then(responseData => {
          if (responseData && responseData.data) {
            process(responseData.data)
          }
        })
        .catch(error => {
          // creates log error
          invariant(false, error.message)
          // displays generic error in ui
          this.editor.notificationManager.open({type: 'error', text: trans('error_occurred')})
        })
    }
  },

  show: function () {
    const rtePosition = $(this.editor.getContainer()).offset(),
      contentAreaPosition = $(this.editor.getContentAreaContainer()).position(),
      nodePosition = $(this.editor.dom.select('span#autocomplete')).position(),
      top = rtePosition.top + contentAreaPosition.top + nodePosition.top + $(this.editor.selection.getNode()).innerHeight() - $(this.editor.getDoc()).scrollTop() + 5,
      left = rtePosition.left + contentAreaPosition.left + nodePosition.left

    this.$dropdown = $(this.renderDropdown())
    this.$dropdown.css({
      top: top,
      left: left
    })

    $('body').append(this.$dropdown)

    this.$dropdown.on('click', $.proxy(this.autoCompleteClick, this))
  },

  process: function (items) {
    if (!this.hasFocus) {
      return
    }

    const result = []
    items.map(item => {
      const $element = $(this.render(item, this.query))
      $element.attr('data-item', JSON.stringify(item)) // todo store data in JS to avoid polluting DOM

      result.push($element[0].outerHTML)
    })

    if (result.length) {
      this.$dropdown.html(result.join('')).show()
    } else {
      this.$dropdown.hide()
    }
  },

  renderDropdown: function () {
    return (`
      <ul class="user-mentions-menu dropdown-menu"></ul>
    `)
  },

  highlight: function (text, query) {
    return text
      // replaces spaces by unbreakable ones to avoid trailing to be trimmed by highlight tag
      .replace(' ', '&nbsp;')
      .replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
        return `<strong class="highlight-search">${match}</strong>`
      })
  },

  render: function (item, query) {
    let avatar = '<span class="user-avatar fa fa-user-circle"></span>'
    if (item.picture) {
      avatar = `<img class="user-avatar" src="${asset(item.picture)}" alt="${item.name}" />`
    }

    return (`
      <li>
        <a role="button" class="user-mention-item" href="#">
            ${avatar}

            ${this.highlight(item.name, query)}&nbsp;
            <small>${this.highlight(item.username, query)}</small>
        </a>
      </li>
    `)
  },

  autoCompleteClick: function (e) {
    const item = $(e.target).closest('li').data('item')
    if (!$.isEmptyObject(item)) {
      this.select(item)
      this.cleanUp(false)
    }
    e.stopPropagation()
    e.preventDefault()
  },

  highlightPreviousResult: function () {
    let currentIndex = this.$dropdown.find('li.active').index()
    const index = (currentIndex === 0) ? this.$dropdown.find('li').length - 1 : --currentIndex

    this.$dropdown.find('li').removeClass('active').eq(index).addClass('active')
  },

  highlightNextResult: function () {
    let currentIndex = this.$dropdown.find('li.active').index()
    const index = (currentIndex === this.$dropdown.find('li').length - 1) ? 0 : ++currentIndex

    this.$dropdown.find('li').removeClass('active').eq(index).addClass('active')
  },

  select: function (item) {
    this.editor.focus()

    this.editor.dom.remove(
      this.editor.dom.select('span#autocomplete')[0]
    )
    this.editor.execCommand('mceInsertContent', false, this.insert(item) + '&nbsp;')
  },

  insert: function (item) {
    return (`
      <user id="${item.id}">
        <a class="user-mention" href="${'#' + route(item)}">@${item.name}</a>
      </user>
    `)
  },

  cleanUp: function (rollback) {
    this.unbindEvents()
    this.hasFocus = false

    if (this.$dropdown !== undefined) {
      this.$dropdown.remove()
      delete this.$dropdown
    }

    if (rollback) {
      var text = this.query,
        $selection = $(this.editor.dom.select('span#autocomplete')),
        replacement = $('<p>' + this.options.delimiter + text + '</p>')[0].firstChild,
        focus = $(this.editor.selection.getNode()).offset().top === ($selection.offset().top + (($selection.outerHeight() - $selection.height()) / 2))

      this.editor.dom.replace(replacement, $selection[0])

      if (focus) {
        this.editor.selection.select(replacement)
        this.editor.selection.collapse()
      }
    }
  }
}

tinymce.PluginManager.add('mentions', (editor) => {
  let autoComplete

  function prevCharIsSpace() {
    var isLink = $(editor.selection.getNode()).is('a'),
      start = editor.selection.getRng().startOffset,
      text = editor.selection.getRng().startContainer.textContent,
      character = text.substr(start - 1, 1)

    return !(isLink || !!$.trim(character).length)
  }

  editor.on('keypress', function (e) {
    const delimiterIndex = $.inArray(String.fromCharCode(e.which || e.keyCode), '@')

    if (delimiterIndex > -1 && prevCharIsSpace()) {
      if (autoComplete === undefined || (autoComplete.hasFocus !== undefined && !autoComplete.hasFocus)) {
        e.preventDefault()
        // Clone options object and set the used delimiter.
        autoComplete = new AutoComplete(editor)
      }
    }
  })
})
