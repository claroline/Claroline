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
        label: trans('trash'),
        target: `${props.basePath}/trash`,
        displayed: props.isRoot && props.canAdministrate
        //exact: true
      }, {
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-sitemap',
        label: trans('Arborescence'),
        target: `${props.path}/summary`,
        displayed: props.isRoot
      }
    ]}
  />

DirectoryMenu.propTypes = {
  basePath: T.string.isRequired,
  path: T.string.isRequired,
  isRoot: T.bool.isRequired,
  canAdministrate: T.bool.isRequired
}

export {
  DirectoryMenu
}
