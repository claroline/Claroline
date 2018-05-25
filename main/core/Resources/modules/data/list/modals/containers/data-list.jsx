import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

import {DataListProperty} from '#/main/core/data/list/prop-types'

const MODAL_DATA_LIST = 'MODAL_DATA_LISt'

const DataListModal = props =>
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
      onClick={() => {
        props.fadeModal()
        props.handleSelect()}
      }
    >
      {props.confirmText}
    </button>
  </BaseModal>

DataListModal.propTypes = {
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

  fadeModal: T.func.isRequired
}

DataListModal.defaultProps = {
  title: trans('objects_select_title'),
  confirmText: trans('objects_select_confirm'),
  icon: 'fa fa-fw fa-hand-pointer-o',
  onlyId: true
}

export {
  MODAL_DATA_LIST,
  DataListModal
}
