import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {TemplateTool as TemplateToolComponent} from '#/main/core/administration/template/components/tool'
import {actions, selectors} from '#/main/core/administration/template/store'

const TemplateTool = withRouter(
  connect(
    (state) => ({
      defaultLocale: selectors.defaultLocale(state)
    }),
    (dispatch) => ({
      openForm(defaultLocale, id = null) {
        const defaultData = {
          lang: defaultLocale
        }
        dispatch(actions.openForm('template', defaultData, id))
      },
      resetForm(defaultLocale) {
        const defaultData = {
          lang: defaultLocale
        }
        dispatch(actions.resetForm('template', defaultData))
      }
    })
  )(TemplateToolComponent)
)

export {
  TemplateTool
}
