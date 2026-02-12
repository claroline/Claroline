import React, {forwardRef} from 'react'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'
import {copy as copyToClipboard} from '#/main/app/utils/clipboard'

import {CallbackButton} from '#/main/app/buttons/callback/components/button'

/**
 * Clipboard button.
 * Renders a component that will trigger a clipboard copy on click.
 */
const ClipboardButton = forwardRef((props, ref) => {
  return (
    <CallbackButton
      {...omit(props, 'copy')}
      ref={ref}
      callback={() => {
        const toCopy = props.copy()
        if (toCopy) {
          copyToClipboard(toCopy)
        }
      }}
    >
      {props.children}
    </CallbackButton>
  )
})

// for debug purpose, otherwise component is named after the HOC
ClipboardButton.displayName = 'ClipboardButton'

implementPropTypes(ClipboardButton, ButtonTypes, {
  /**
   * A function called on the button click.
   * It MUST return the data to add to the clipboard.
   */
  copy: T.func.isRequired
})

export {
  ClipboardButton
}
