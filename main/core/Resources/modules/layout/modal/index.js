import React from 'react'
import invariant from 'invariant'

import {withModal} from '#/main/core/layout/modal/withModal'

import {MessageModal} from './components/message'
import {ConfirmModal} from './components/confirm'
import {DeleteConfirmModal} from './components/delete-confirm'
import {UrlModal} from './components/url'
import {UserPickerModal} from './components/user-picker'
import {GenericTypePicker} from './components/generic-type-picker'
import {IframeModal} from './components/iframe'

export const MODAL_MESSAGE = 'MODAL_MESSAGE'
export const MODAL_CONFIRM = 'MODAL_CONFIRM'
export const MODAL_DELETE_CONFIRM = 'MODAL_DELETE_CONFIRM'
export const MODAL_GENERIC_TYPE_PICKER = 'MODAL_GENERIC_TYPE_PICKER'
export const MODAL_URL = 'MODAL_URL' // only for use with old Twig modals, will be deleted
export const MODAL_USER_PICKER = 'MODAL_USER_PICKER'
export const MODAL_IFRAME = 'MODAL_IFRAME' // todo : delete me

const modals = {
  [MODAL_MESSAGE]: MessageModal,
  [MODAL_CONFIRM]: ConfirmModal,
  [MODAL_DELETE_CONFIRM]: DeleteConfirmModal,
  [MODAL_URL]: UrlModal,
  [MODAL_USER_PICKER]: UserPickerModal, // todo : register it only in tools using it (users with no edit rights don't need it)
  [MODAL_GENERIC_TYPE_PICKER]: GenericTypePicker, // same here
  [MODAL_IFRAME]: IframeModal
}

function registerModal(type, component) {
  if (!modals[type]) {
    modals[type] = component
  }
}

function registerModals(types) {
  types.map(type => registerModal(type[0], type[1]))
}

function makeModal(type, props, fading, fadeCallback = () => true, hideCallback = () => true) {
  invariant(modals[type], `Unknown modal type "${type}"`)

  const baseProps = {
    show: !fading,
    fadeModal: () => fadeCallback(),
    hideModal:() => hideCallback()
  }

  return React.createElement(modals[type], Object.assign(baseProps, props))
}

// only for use with old Twig modals, will be deleted
function makeModalFromUrl(fading, hideCallback = () => true, url) {
  return fetch(url, {
    method: 'GET',
    credentials: 'include'
  }).then(response => response.text())
    .then(text => React.createElement(UrlModal, {
      show: !fading,
      hideModal:() => hideCallback(),
      content: text
    }))
}

export {
  withModal,
  makeModal,
  makeModalFromUrl,
  registerModal,
  registerModals
}
