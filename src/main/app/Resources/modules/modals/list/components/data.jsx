import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {DataListProperty} from '#/main/app/content/list/prop-types'

// todo : use standard action as select

const ListDataModal = props =>
  <Modal
    {...omit(props, 'name', 'confirmText', 'fetch', 'card', 'onlyId', 'display', 'definition', 'handleSelect')}
    className="data-picker-modal"
    bsSize="lg"
    onExiting={() => props.resetSelect()}
  >
    <ListData
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
  </Modal>

ListDataModal.propTypes = {
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

ListDataModal.defaultProps = {
  title: trans('objects_select_title'),
  confirmText: trans('objects_select_confirm'),
  icon: 'fa fa-fw fa-hand-pointer',
  onlyId: true
}

export {
  ListDataModal
}
