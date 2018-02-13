import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t} from '#/main/core/translation'
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
    />

    <button
      className="modal-btn btn btn-primary"
      disabled={0 === props.selected.length}
      onClick={() => {
        if (0 <props.selected.length) {
          props.handleSelect(props.selected)
          props.resetSelect()
          props.fadeModal()
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
  card: T.func.isRequired,

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
  resetSelect: T.func.isRequired
}

DataPicker.defaultProps = {
  title: t('objects_select_title'),
  confirmText: t('objects_select_confirm'),
  icon: 'fa fa-fw fa-hand-pointer-o'
}

const DataPickerModal = connect(
  (state, ownProps) => ({
    selected: listSelect.selected(listSelect.list(state, ownProps.name))
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