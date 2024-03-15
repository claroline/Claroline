import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {ResourceMenu} from '#/main/core/resource/containers/menu'

const DirectoryMenu = (props) =>
  <ResourceMenu
    actions={[
      {
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-home',
        label: trans('Corbeille'),
        target: `${props.path}/trash`,
        //exact: true
      }, {
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-sitemap',
        label: trans('Arborescence'),
        target: `${props.path}/summary`
      }
    ]}
  />

DirectoryMenu.propTypes = {
  path: T.string.isRequired
}

export {
  DirectoryMenu
}
