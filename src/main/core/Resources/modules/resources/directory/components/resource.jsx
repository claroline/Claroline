import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {ResourcePage} from '#/main/core/resource/containers/page'

import {PlayerMain} from '#/main/core/resources/directory/player/containers/main'
import {EditorMain} from '#/main/core/resources/directory/editor/containers/main'
import {DirectorySummary} from '#/main/core/resources/directory/containers/summary'
import {Resource} from '#/main/core/resource'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'


const DirectoryResource = (props) =>
  <Resource
    {...omit(props, 'storageLock')}
    disabledActions={props.storageLock ? ['add', 'add_files', 'copy'] : []}
    menu={[
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
    pages={[
      {
        path: '/:all(all)?',
        exact: true,
        render(routeProps) {
          return (
            <PlayerMain
              all={routeProps.match.params.all}
            />
          )
        }
      }, {
        path: '/edit',
        component: EditorMain
      }, {
        path: '/summary',
        component: DirectorySummary
      }
    ]}
  />

DirectoryResource.propTypes = {
  storageLock: T.bool.isRequired,
  basePath: T.string.isRequired,
  path: T.string.isRequired,
  isRoot: T.bool.isRequired,
  canAdministrate: T.bool.isRequired
}

export {
  DirectoryResource
}