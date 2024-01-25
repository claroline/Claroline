import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ResourcePage} from '#/main/core/resource/containers/page'

import {PlayerMain} from '#/main/core/resources/directory/player/containers/main'
import {EditorMain} from '#/main/core/resources/directory/editor/containers/main'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

const DirectoryResource = (props) =>
  <ResourcePage
    /*primaryAction="add"*/
    nav={[
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
    disabledActions={props.storageLock ? ['add', 'add_files', 'copy'] : []}
    routes={[
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
      }
    ]}
  />

DirectoryResource.propTypes = {
  storageLock: T.bool.isRequired
}

export {
  DirectoryResource
}