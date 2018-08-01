import React from 'react'
import {WorkspaceForm} from '#/main/core/workspace/creation/components/form.jsx'
import {Routes} from '#/main/app/router'

const CreationForm = props => {

  let steps = [
    {
      path: '/workspaces/creation/form',
      title: 'form',
      component: WorkspaceForm,
      onEnter: props.createForm
    }
  ]

  return (
    <Routes routes={steps}/>
  )
}

export {CreationForm}
