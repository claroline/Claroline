import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'

import {LocationForm} from '#/main/core/tools/locations//containers/form'

const LocationNew = (props) =>
  <ToolPage
    title={trans('new_location', {}, 'location')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_location', {}, 'location'),
        target: `${props.path}/new`,
        group: trans('management'),
        primary: true
      }
    ]}
  >
    <LocationForm />
  </ToolPage>

LocationNew.propTypes = {
  path: T.string.isRequired
}

export {
  LocationNew
}
