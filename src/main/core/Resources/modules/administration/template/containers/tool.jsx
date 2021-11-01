import {connect} from 'react-redux'

import {TemplateTool as TemplateToolComponent} from '#/main/core/administration/template/components/tool'
import {actions} from '#/main/core/administration/template/store'

const TemplateTool = connect(
  null,
  (dispatch) => ({
    open(id) {
      dispatch(actions.open(id))
    }
  })
)(TemplateToolComponent)

export {
  TemplateTool
}
