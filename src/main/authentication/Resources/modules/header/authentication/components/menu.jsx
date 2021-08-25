import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

const AuthenticationMenu = (props) => {
  if (props.authenticated) {
    return null
  }

  return (
    <Fragment>
      <Button
        type={LINK_BUTTON}
        className="app-header-item app-header-btn"
        label={trans('login', {}, 'actions')}
        target="/login"
      />

      {props.registration &&
        <Button
          type={LINK_BUTTON}
          className="app-header-item app-header-btn"
          label={trans('create-account', {}, 'actions')}
          target="/registration"
        />
      }
    </Fragment>
  )
}

AuthenticationMenu.propTypes = {
  authenticated: T.bool.isRequired,
  registration: T.bool.isRequired
}

export {
  AuthenticationMenu
}