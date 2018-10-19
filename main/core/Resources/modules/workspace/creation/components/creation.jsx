import React from 'react'

import {Routes} from '#/main/app/router'

import {WorkspaceForm} from '#/main/core/workspace/creation/components/form'

const CreationForm = props =>
  <Routes
    routes={[
      {
        path: '/workspaces/creation/form',
        title: 'form',
        component: WorkspaceForm,
        onEnter: props.createForm
      }
    ]}
  />

export {
  CreationForm
}
