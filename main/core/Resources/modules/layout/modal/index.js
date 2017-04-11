import React from 'react'
import invariant from 'invariant'

import {MessageModal} from './components/message.jsx'
import {ConfirmModal} from './components/confirm.jsx'
import {DeleteConfirmModal} from './components/delete-confirm.jsx'

export const MODAL_MESSAGE = 'MODAL_MESSAGE'
export const MODAL_CONFIRM = 'MODAL_CONFIRM'
export const MODAL_DELETE_CONFIRM = 'MODAL_DELETE_CONFIRM'

const modals = {
  [MODAL_MESSAGE]: MessageModal,
  [MODAL_CONFIRM]: ConfirmModal,
  [MODAL_DELETE_CONFIRM]: DeleteConfirmModal
}

export function registerModalType(type, component) {
  invariant(!modals[type], `Modal type ${type} is already registered`)
  modals[type] = component
}

export function registerModalTypes(types) {
  types.map(type => registerModalType(type[0], type[1]))
}

export function makeModal(type, props, fading, fadeCallback = () => true, hideCallback = () => true) {
  invariant(modals[type], `Unknown modal type "${type}"`)

  const baseProps = {
    show: !fading,
    fadeModal: () => fadeCallback(),
    hideModal:() => hideCallback()
  }

  return React.createElement(modals[type], Object.assign(baseProps, props))
}
