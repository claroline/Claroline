import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Tool} from '#/main/core/tool'
import {LINK_BUTTON} from '#/main/app/buttons'

import {EventMain} from '#/plugin/cursus/tools/trainings/event/containers/main'
import {CatalogMain} from '#/plugin/cursus/tools/trainings/catalog/containers/main'
import {SessionMain} from '#/plugin/cursus/tools/trainings/session/containers/main'
import {TrainingsEditor} from '#/plugin/cursus/tools/trainings/editor/containers/main'

const TrainingsTool = (props) =>
  <Tool
    {...props}
    menu={[
      {
        name: 'catalog',
        type: LINK_BUTTON,
        label: trans('catalog', {}, 'cursus'),
        target: props.path+'/course'
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
    redirect={[
      {from: '/', to: '/course', exact: true}
    ]}
    pages={[
      {
        path: '/course',
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
    editor={TrainingsEditor}
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
