import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {Modal} from '#/main/app/overlays/modal/components/modal'
import {GridSelection} from '#/main/app/content/grid/components/selection'

const SelectionModal = props =>
  <Modal
    {...omit(props, 'items', 'handleSelect', 'selectAction')}
  >
    <GridSelection
      items={props.items}
      handleSelect={props.handleSelect ? (type) => {
        props.fadeModal()
        props.handleSelect(type)
      } : undefined}
      selectAction={props.selectAction ? (type) => {
        const action = props.selectAction(type)

        return merge({}, action, {
          onClick: (e) => {
            if (action.onClick) {
              action.onClick(e)
            }

            props.fadeModal()
          }
        })
      } : undefined}
    />
  </Modal>

SelectionModal.propTypes = {
  items: T.array.isRequired,
  fadeModal: T.func.isRequired,
  handleSelect: T.func, // for retro-compatibility only. Use selectAction
  selectAction: T.func
}

export {
  SelectionModal
}
