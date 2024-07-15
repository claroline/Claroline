import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tool} from '#/main/core/tool'

import {CatalogMain} from '#/plugin/cursus/tools/trainings/catalog/containers/main'
import {SessionMain} from '#/plugin/cursus/tools/trainings/session/containers/main'
import {EventMain} from '#/plugin/cursus/tools/trainings/event/containers/main'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

const TrainingsTool = (props) =>
  <Tool
    {...props}
    menu={[
      {
        name: 'catalog',
        type: LINK_BUTTON,
        label: trans('catalog', {}, 'cursus'),
        target: `${props.path}`
      }, {
        name: 'public',
        type: LINK_BUTTON,
        label: trans('public_events', {}, 'cursus'),
        target: props.path + '/events/public'
      }, {
        name: 'all',
        type: LINK_BUTTON,
        label: trans('all_events', {}, 'cursus'),
        target: props.path + '/events/all',
        displayed: props.authenticated && (props.canEdit || props.canRegister)
      }, {
        name: 'registered',
        type: LINK_BUTTON,
        label: trans('my_courses', {}, 'cursus'),
        target: `${props.path}/registered`,
        displayed: props.authenticated
      }, {
        name: 'registered-events',
        type: LINK_BUTTON,
        label: trans('my_events', {}, 'cursus'),
        target: props.path + '/events/registered',
        displayed: props.authenticated
      }
    ]}
    pages={[
      {
        path: '/',
        component: CatalogMain
      }, {
        path: '/registered',
        component: SessionMain,
        disabled: !props.authenticated
      }, {
        path: '/events',
        component: EventMain
      }
    ]}
  />

TrainingsTool.propTypes = {
  authenticated: T.bool.isRequired,
  canEdit: T.bool.isRequired,
  canRegister: T.bool.isRequired,
  path: T.string.isRequired
}

export {
  TrainingsTool
}
