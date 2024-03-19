import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {getTabs} from '#/main/evaluation/evaluation'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const EvaluationMenu = (props) =>
  <ToolMenu
    actions={getTabs(props.contextType, props.permissions).then((apps) => [
      {
        name: 'users-progression',
        type: LINK_BUTTON,
        label: trans('users_progression', {}, 'evaluation'),
        target: props.path+'/users'
      }
    ].concat(apps.map(app => ({
      name: app.name,
      type: LINK_BUTTON,
      label: trans(app.name, {}, 'evaluation'),
      target: `${props.path}/${app.name}`
    }))).concat([{
      name: 'parameters',
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-cog',
      label: trans('parameters'),
      target: props.path+'/parameters',
      displayed: props.canEdit && 'workspace' === props.contextType
    }]))}
  />

EvaluationMenu.propTypes = {
  path: T.string,
  canEdit: T.bool.isRequired,
  contextType: T.string.isRequired,
  permissions: T.object
}

export {
  EvaluationMenu
}
