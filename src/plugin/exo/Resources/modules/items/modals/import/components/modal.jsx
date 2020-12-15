import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {ItemList} from '#/plugin/exo/items/components/list'

const ImportModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'name', 'selected', 'selectAction', 'resetSelect')}
      icon="fa fa-fw fa-upload"
      className="data-picker-modal"
      bsSize="lg"
      title={trans('import')}
      onExiting={props.resetSelect}
    >
      <ItemList
        name={props.name}
      />

      <Button
        label={trans('import', {}, 'actions')}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={0 === props.selected.length}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

ImportModal.propTypes = {
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,

  // from store
  name: T.string.isRequired,
  selected: T.arrayOf(T.object).isRequired,
  resetSelect: T.func.isRequired
}

export {
  ImportModal
}
