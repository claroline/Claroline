import {connect} from 'react-redux'

import {actions as listActions} from '#/main/app/content/list/store'

import {TemplateTool as TemplateToolComponent} from '#/main/core/administration/template/components/tool'
import {actions, selectors} from '#/main/core/administration/template/store'

const TemplateTool = connect(
  null,
  (dispatch) => ({
    open(id) {
      dispatch(actions.open(id))
    },
    invalidateList() {
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.templates'))
    }
  })
)(TemplateToolComponent)

export {
  TemplateTool
}
