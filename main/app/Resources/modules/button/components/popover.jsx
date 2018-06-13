import React from 'react'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {PopoverOverlay} from '#/main/app/overlay/popover/components/overlay'

import {Button as ButtonTypes} from '#/main/app/button/prop-types'
import {CallbackButton} from '#/main/app/button/components/callback'

const PopoverButton = (props) =>
  <PopoverOverlay
    {...props.popover}
    id={`${props.id}-popover`}
  >
    <CallbackButton
      {...omit(props, 'popover')}
      callback={() => true}
    >
      {props.children}
    </CallbackButton>
  </PopoverOverlay>

implementPropTypes(PopoverButton, ButtonTypes, {
  id: T.string.isRequired,
  popover: T.shape({
    className: T.string,
    label: T.string,
    position: T.oneOf(['top', 'bottom', 'left', 'right']),
    content: T.node.isRequired
  }).isRequired
})

export {
  PopoverButton
}
