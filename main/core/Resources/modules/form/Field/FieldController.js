import $ from 'jquery'

export default class FieldController {
  constructor() {
    this.name = this.field[0]
    this.options = this.field[2] || {}
    this.label = this.options.label !== undefined ? this.options.label : this.name
    this.translationDomain = this.options.translation_domain ? this.options.translation_domain: 'platform'
  }

  getTinymceConfiguration() {
    let tinymce = window.tinymce
    tinymce.claroline.init = tinymce.claroline.init || {}
    tinymce.claroline.plugins = tinymce.claroline.plugins || {}

    let plugins = [
      'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
      'searchreplace wordcount visualblocks visualchars fullscreen',
      'insertdatetime media nonbreaking save table directionality',
      'template paste textcolor emoticons code -accordion -mention -codemirror'
    ]
    let toolbar = 'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | fullscreen displayAllButtons'

    $.each(tinymce.claroline.plugins, (key, value) => {
      if ('autosave' != key &&  value === true) {
        plugins.push(key)
        toolbar += ' ' + key
      }
    })

    let config = {}

    for (const prop in tinymce.claroline.configuration) {
      if (tinymce.claroline.configuration.hasOwnProperty(prop)) {
        config[prop] = tinymce.claroline.configuration[prop]
      }
    }
    config.plugins = plugins
    config.toolbar1 = toolbar
    config.trusted = true
    config.format = 'html'

    if (this.options['disabled']) {
      config.readonly = true
    }

    return config
  }
}
