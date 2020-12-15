import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

// TODO : make dynamic
const tools = [
  {
    name: 'chat',
    icon: 'fa fa-fw fa-comments',
    label: 'Messages'
  }, {
    name: 'agenda',
    icon: 'fa fa-fw fa-calendar',
    label: 'Agenda'
  }, {
    name: 'tasks',
    icon: 'fa fa-fw fa-tasks',
    label: 'Tâches'
  }, {
    name: 'history',
    icon: 'fa fa-fw fa-history',
    label: 'Historique'
  }, {
    name: 'favorites',
    icon: 'fa fa-fw fa-star',
    label: 'Favoris'
  }, {
    name: 'notes',
    icon: 'fa fa-fw fa-sticky-note',
    label: 'Bloc-notes'
  }
]

const LayoutToolbar = props =>
  <nav className="app-toolbar">
    {tools.map(tool =>
      <Button
        key={tool.name}
        className="app-toolbar-link btn-link"
        type={CALLBACK_BUTTON}
        icon={tool.icon}
        label={tool.label}
        callback={() => props.open(tool.name)}
        tooltip="left"
        active={props.opened === tool.name}
      />
    )}
  </nav>

LayoutToolbar.propTypes = {
  opened: T.string,
  open: T.func.isRequired
}

export {
  LayoutToolbar
}
