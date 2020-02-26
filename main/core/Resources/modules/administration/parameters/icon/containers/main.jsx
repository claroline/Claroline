import {connect} from 'react-redux'

import {makeId} from '#/main/core/scaffolding/id'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {IconSet as IconSetType} from '#/main/core/administration/parameters/icon/prop-types'
import {IconsMain as IconsMainComponent} from '#/main/core/administration/parameters/icon/components/main'
import {actions, selectors} from '#/main/core/administration/parameters/icon/store'

const IconsMain = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    openIconSetForm(id = null) {
      const defaultProps = Object.assign({}, IconSetType.defaultProps, {
        id: makeId()
      })
      dispatch(actions.openIconSetForm(selectors.STORE_NAME+'.current', defaultProps, id))
    },
    resetIconSetForm() {
      dispatch(actions.resetForm(selectors.STORE_NAME+'.current'))
    }
  })
)(IconsMainComponent)

export {
  IconsMain
}
