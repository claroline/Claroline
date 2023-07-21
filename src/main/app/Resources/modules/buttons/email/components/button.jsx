import React, {forwardRef} from 'react'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'
import {UrlButton} from '#/main/app/buttons/url/components/button'

/**
 * Email button.
 * Renders a component that will open the standard user mailer on click.
 */
const EmailButton = forwardRef((props, ref) =>
  <UrlButton
    {...omit(props, 'email')}
    ref={ref}
    target={`mailto:${props.email}`}
  >
    {props.children || props.email}
  </UrlButton>
)

// for debug purpose, otherwise component is named after the HOC
EmailButton.displayName = 'EmailButton'

implementPropTypes(EmailButton, ButtonTypes, {
  email: T.string.isRequired
})

export {
  EmailButton
}
