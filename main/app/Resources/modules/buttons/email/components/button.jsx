import React from 'react'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'
import {UrlButton} from '#/main/app/buttons/url/components/button'

/**
 * Email button.
 * Renders a component that will open the standard user mailer on click.
 *
 * @param props
 * @constructor
 */
const EmailButton = props =>
  <UrlButton
    {...omit(props, 'email')}
    target={`mailto:${props.email}`}
  >
    {props.children || props.email}
  </UrlButton>

implementPropTypes(EmailButton, ButtonTypes, {
  email: T.string.isRequired
})

export {
  EmailButton
}
