import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

const EventsPending = (props) =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('pending_registrations'),
      target: `${props.path}/pending`
    }]}
    subtitle={trans('pending_registrations')}
  >

  </ToolPage>

EventsPending.propTypes = {
  path: T.string.isRequired
}

export {
  EventsPending
}
