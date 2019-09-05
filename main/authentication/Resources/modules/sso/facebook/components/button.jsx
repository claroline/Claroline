import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON} from '#/main/app/buttons'

//target={['hwi_oauth_service_redirect', {service: sso.service}]}

const FacebookButton = props =>
  <Button
    className="btn-link btn-block btn-emphasis facebook-connect btn-third-party-login"
    type={URL_BUTTON}
    label={props.display_name || trans('login_with_third_party_btn', {name: trans('facebook', {}, 'oauth')})}

    target=""
  />

FacebookButton.propTypes = {
  display_name: T.string
}

export {
  FacebookButton
}
