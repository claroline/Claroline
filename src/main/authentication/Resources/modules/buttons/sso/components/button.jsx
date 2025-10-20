import React from 'react'
import classes from 'classnames'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON} from '#/main/app/buttons'

const SsoButton = props =>
  <Button
    className={classes('btn', props.className, props.primary ? 'btn-primary' : 'btn-body')}
    type={URL_BUTTON}
    size={props.primary ? 'lg' : undefined}

    {...omit(props)}
  />

implementPropTypes(SsoButton, ActionTypes, {
  primary: T.bool
})

export {
  SsoButton
}
