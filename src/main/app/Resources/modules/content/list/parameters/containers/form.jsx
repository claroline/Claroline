import {connect} from 'react-redux'

import {actions} from '#/main/app/content/form/store'

import {ListForm as ListFormComponent} from '#/main/app/content/list/parameters/components/form'

const ListForm = connect(
  null,
  (dispatch, ownProps) => ({
    updateProp(prop, value) {
      let updateTarget = prop
      if (ownProps.dataPart) {
        updateTarget = `${ownProps.dataPart}.${updateTarget}`
      }

      dispatch(actions.updateProp(ownProps.name, updateTarget, value))
    }
  })
)(ListFormComponent)

export {
  ListForm
}
