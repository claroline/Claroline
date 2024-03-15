import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ResourceMenu} from '#/main/core/resource'


const ScormMenu = props =>
  <ResourceMenu
    overview={false}
    actions={[
      {
        name: 'summary',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-sitemap',
        label: trans('summary'),
        target: `${props.path}/summary`
      }
    ]}
  />

ScormMenu.propTypes = {
  path: T.string.isRequired
}

export {
  ScormMenu
}
