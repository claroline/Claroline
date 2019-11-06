import {connect} from 'react-redux'

// import {withRouter} from '#/main/app/router'

import {makeId} from '#/main/core/scaffolding/id'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as parametersSelectors} from '#/main/core/administration/parameters/store'
import {actions} from '#/main/core/administration/parameters/icon/store'
import {IconSet as IconSetType} from '#/main/core/administration/parameters/prop-types'
import {IconsMain as IconsMainComponent} from '#/main/core/administration/parameters/icon/components/main'

const IconsMain = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    openIconSetForm(id = null) {
      const defaultProps = Object.assign({}, IconSetType.defaultProps, {
        id: makeId()
      })
      dispatch(actions.openIconSetForm(parametersSelectors.STORE_NAME+'.icons.current', defaultProps, id))
    },
    resetIconSetForm() {
      dispatch(actions.resetForm(parametersSelectors.STORE_NAME+'.icons.current'))
    }
  })
)(IconsMainComponent)

export {
  IconsMain
}
