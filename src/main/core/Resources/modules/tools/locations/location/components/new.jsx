import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {LocationForm} from '#/main/core/tools/locations/location/containers/form'

const LocationNew = (props) =>
  <ToolPage
    path={[
      {
        type: LINK_BUTTON,
        label: trans('locations'),
        target: props.path + '/locations'
      }, {
        label: trans('new_location')
      }
    ]}
    title={trans('locations', {}, 'tools')}
    subtitle={trans('new_location')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_location'),
        target: `${props.path}/locations/new`,
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
