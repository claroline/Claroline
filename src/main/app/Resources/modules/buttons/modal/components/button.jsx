import React, {forwardRef} from 'react'
import {useDispatch} from 'react-redux'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

import {actions} from '#/main/app/overlays/modal/store/actions'

/**
 * Modal button.
 * Renders a component that will open a modal on click.
 *
 * NB. it requires the `modal` reducer in your store to work.
 */
const ModalButton = forwardRef((props, ref) => {
  const dispatch = useDispatch()

  return (
    <CallbackButton
      {...omit(props, 'modal')}
      ref={ref}
      callback={() => dispatch(actions.showModal(...props.modal))}
    >
      {props.children}
    </CallbackButton>
  )
})

// for debug purpose, otherwise component is named after the HOC
ModalButton.displayName = 'ModalButton'

implementPropTypes(ModalButton, ButtonTypes, {
  /**
   * The modal to open.
   *
   * modal[0] : modal type
   * modal[1] : modal props
   *
   * @type {Array}
   */
  modal: T.arrayOf((propValue, key, componentName, location, propFullName) => {
    let error
    if (0 === key && typeof propValue[key] !== 'string') {
      // modal type MUST be a string
      error = `Invalid prop \`${propFullName}\` of type \`${typeof propValue[key]}\` supplied to \`${componentName}\`, expected \`string\`.`
    } else if (1 === key && propValue[key] && typeof propValue[key] !== 'object') {
      // modal props MUST be an object if provided
      error = `Invalid prop \`${propFullName}\` of type \`${typeof propValue[key]}\` supplied to \`${componentName}\`, expected \`object\`.`
    } else if (1 < key) {
      // unknown key
      error = `Unknown prop \`${propFullName}\` on \`${componentName}\` component. Remove this prop from the element.`
    }

    if (error) {
      return new Error(error)
    }
  }).isRequired
})

export {
  ModalButton
}
