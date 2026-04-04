import React from 'react'

import {Resource} from '#/main/core/resource'

import {ProjectEditor} from '#/plugin/project/resources/project/components/editor'
import {ProjectPlayer} from '#/plugin/project/resources/project/components/player'

const ProjectResource = (props) =>
  <Resource
    {...props}
    editor={ProjectEditor}
    pages={[
      {
        path: '/',
        exact: true,
        component: ProjectPlayer
      }
    ]}
  />

export {
  ProjectResource
}
