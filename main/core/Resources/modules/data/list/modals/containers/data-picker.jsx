import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

import {actions as listActions} from '#/main/core/data/list/actions'
import {select as listSelect} from '#/main/core/data/list/selectors'

import {DataListProperty} from '#/main/core/data/list/prop-types'

const MODAL_DATA_PICKER = 'MODAL_DATA_PICKER'

const DataPicker = props =>
  <BaseModal
    {...props}
    className="data-picker-modal"
    bsSize="lg"
  >
    <DataListContainer
      name={props.name}
      fetch={props.fetch}
      definition={props.definition}
      card={props.card}
      display={props.display}
    />

    <button
      className="modal-btn btn btn-primary"
      disabled={0 === props.selected.length}
      onClick={() => {
        if (0 < props.selected.length) {
          props.fadeModal()
          
          props.handleSelect(props.onlyId ? props.selected : props.selectedFull)
          props.resetSelect()
        }
      }}
    >
      {props.confirmText}
    </button>
  </BaseModal>

DataPicker.propTypes = {
  name: T.string.isRequired,
  icon: T.string,
  title: T.string,
  confirmText: T.string,
  fetch: T.object,
  card: T.func, // It must be a react component.
  onlyId: T.bool,
  display: T.object,

  /**
   * Definition of the data properties.
   */
  definition: T.arrayOf(
    T.shape(DataListProperty.propTypes)
  ).isRequired,
  handleSelect: T.func.isRequired,

  fadeModal: T.func.isRequired,
  // retrieved from store
  selected: T.array.isRequired,
  selectedFull: T.arrayOf(T.object).isRequired,
  resetSelect: T.func.isRequired
}

DataPicker.defaultProps = {
  title: trans('objects_select_title'),
  confirmText: trans('objects_select_confirm'),
  icon: 'fa fa-fw fa-hand-pointer-o',
  onlyId: true
}

const DataPickerModal = connect(
  (state, ownProps) => ({
    selected: listSelect.selected(listSelect.list(state, ownProps.name)),
    selectedFull: ownProps.onlyId ? [] : listSelect.selectedFull(listSelect.list(state, ownProps.name))
  }),
  (dispatch, ownProps) => ({
    resetSelect() {
      dispatch(listActions.resetSelect(ownProps.name))
    }
  })
)(DataPicker)

export {
  MODAL_DATA_PICKER,
  DataPickerModal
}
