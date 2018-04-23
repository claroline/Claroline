import React from 'react'
import {PropTypes as T} from 'prop-types'
import invariant from 'invariant'

// imports implemented button types
import {CallbackButton} from '#/main/app/button/components/callback'
import {DownloadButton} from '#/main/app/button/components/download'
import {EmailButton} from '#/main/app/button/components/email'
import {LinkButton} from '#/main/app/button/components/link'
import {PopoverButton} from '#/main/app/button/components/popover'
import {UrlButton} from '#/main/app/button/components/url'
import {AsyncButton} from '#/main/app/button/containers/async'
import {ModalButton} from '#/main/app/button/containers/modal'

// map types to components (this is just to avoid a big switch)
const ACTION_BUTTONS = {
  async: AsyncButton,
  callback: CallbackButton,
  download: DownloadButton,
  email: EmailButton,
  link: LinkButton,
  modal: ModalButton,
  popover: PopoverButton,
  url: UrlButton
}

/**
 * Renders the correct button component based on the type.
 *
 * @param props
 * @constructor
 */
const GenericButton = props => {
  invariant(undefined !== ACTION_BUTTONS[props.type], `You have requested a non existent button "${props.type}".`)

  return React.createElement(ACTION_BUTTONS[props.type], props)
}

GenericButton.propTypes = {
  type: T.oneOf([
    'async',
    'callback',
    'download',
    'email',
    'link',
    'modal',
    'url'
  ]).isRequired
}

export {
  GenericButton
}
