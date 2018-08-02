import {connect} from 'react-redux'

import {
  actions as listActions,
  select as listSelect
} from '#/main/app/content/list/store'

import {ListDataModal as ListDataModalComponent} from '#/main/app/modals/list/components/data'

const ListDataModal = connect(
  (state, ownProps) => ({
    selected: listSelect.selected(listSelect.list(state, ownProps.name)),
    selectedFull: ownProps.onlyId ? [] : listSelect.selectedFull(listSelect.list(state, ownProps.name))
  }),
  (dispatch, ownProps) => ({
    resetSelect() {
      dispatch(listActions.resetSelect(ownProps.name))
    }
  })
)(ListDataModalComponent)

export {
  ListDataModal
}
