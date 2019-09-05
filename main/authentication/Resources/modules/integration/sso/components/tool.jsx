import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

const SsoTool = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('sso', {}, 'integration'),
      target: `${props.path}/sso`
    }]}
    subtitle={trans('sso', {}, 'integration')}
    actions={[]}
  >
  </ToolPage>

SsoTool.propTypes = {
  path: T.string.isRequired
}

export {
  SsoTool
}
