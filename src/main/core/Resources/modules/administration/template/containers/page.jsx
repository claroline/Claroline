import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {TemplatePage as TemplatePageComponent} from '#/main/core/administration/template/components/page'

const TemplatePage = connect(
  (state) => ({
    path: toolSelectors.path(state),
    currentContext: toolSelectors.context(state)
  })
)(TemplatePageComponent)

export {
  TemplatePage
}
