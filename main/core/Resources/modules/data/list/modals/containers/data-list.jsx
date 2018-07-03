import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/core/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'

import {DataListContainer} from '#/main/core/data/list/containers/data-list'
import {DataListProperty} from '#/main/core/data/list/prop-types'
import {select} from '#/main/core/data/list/selectors'
import {connect} from 'react-redux'

const MODAL_DATA_LIST = 'MODAL_DATA_LIST'

const DataListModal = props =>
  <Modal
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
      onClick={() => {
        props.fadeModal()
        props.handleSelect(props.selected)}
      }
    >
      {props.confirmText}
    </button>
  </Modal>

DataListModal.propTypes = {
  name: T.string.isRequired,
  icon: T.string,
  title: T.string,
  confirmText: T.string,
  fetch: T.object,
  card: T.func, // It must be a react component.
  onlyId: T.bool,
  display: T.object,
  selectors: T.array,
  selected: T.array.isRequired,

  /**
   * Definition of the data properties.
   */
  definition: T.arrayOf(
    T.shape(DataListProperty.propTypes)
  ).isRequired,
  handleSelect: T.func.isRequired,

  fadeModal: T.func.isRequired
}

DataListModal.defaultProps = {
  title: trans('objects_select_title'),
  confirmText: trans('select', {}, 'actions'),
  icon: 'fa fa-fw fa-hand-pointer-o',
  onlyId: true
}

const ConnectedDataListModal = connect(
  (state) => ({
    selected: select.selected(get(state, state.modal.props.name))
  })
)(DataListModal)

export {
  MODAL_DATA_LIST,
  ConnectedDataListModal as DataListModal
}
