import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Modal} from '#/main/app/overlay/modal/components/modal'
import {GridSelection} from '#/main/app/content/grid/components/selection'

const SelectionModal = props =>
  <Modal
    {...omit(props, 'items', 'handleSelect')}
  >
    <GridSelection
      items={props.items}
      handleSelect={(type) => {
        props.handleSelect(type)
        props.fadeModal()
      }}
    />
  </Modal>

SelectionModal.propTypes = {
  items: T.array.isRequired,
  fadeModal: T.func.isRequired,
  handleSelect: T.func.isRequired
}

export {
  SelectionModal
}
