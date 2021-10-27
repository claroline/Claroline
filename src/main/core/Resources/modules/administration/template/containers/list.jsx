import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {TemplateList as TemplateListComponent} from '#/main/core/administration/template/components/list'

const TemplateList = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(TemplateListComponent)

export {
  TemplateList
}
