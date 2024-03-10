import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {constants as toolConstants} from '#/main/core/tool/constants'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const AuthenticationMenu = (props) =>
  <ToolMenu
    actions={[
      {
        name: 'parameters',
        type: LINK_BUTTON,
        label: trans('parameters'),
        target: `${props.path}/`,
        exact: true
      }, {
        name: 'ips',
        type: LINK_BUTTON,
        label: trans('ips', {}, 'integration'),
        target: `${props.path}/ips`
      }, {
        name: 'tokens',
        type: LINK_BUTTON,
        label: trans('tokens', {}, 'integration'),
        target: `${props.path}/tokens`
      }
    ]}
  />

AuthenticationMenu.propTypes = {
  path: T.string
}

export {
  AuthenticationMenu
}
