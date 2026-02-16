import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Tool} from '#/main/core/tool'
import {LINK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'

import {EventMain} from '#/plugin/cursus/tools/trainings/event/containers/main'
import {CatalogMain} from '#/plugin/cursus/tools/trainings/catalog/containers/main'
import {SessionMain} from '#/plugin/cursus/tools/trainings/session/containers/main'
import {TrainingsEditor} from '#/plugin/cursus/tools/trainings/editor/containers/main'
import {TrainingsOverview} from '#/plugin/cursus/tools/trainings/components/overview'
import {EventPresence} from '#/plugin/cursus/presence/components/event'
import {SignPresence} from '#/plugin/cursus/presence/components/signing'
// import {TrainingsDashboard} from '#/plugin/cursus/tools/trainings/dashboard/components/main'

const TrainingsTool = (props) =>
  <Tool
    {...props}
    menu={[
      {
        name: 'overview',
        type: LINK_BUTTON,
        label: trans('about'),
        target: props.path+'/',
        exact: true
      }, {
        name: 'catalog',
        type: LINK_BUTTON,
        label: trans('catalog', {}, 'cursus'),
        target: props.path+'/course',
        displayed: 'desktop' === props.contextType
      }, {
        name: 'sessions',
        type: MENU_BUTTON,
        label: trans('sessions', {}, 'cursus'),
        displayed: props.canRegister,
        indicator: true,
        menu: {
          align: 'end',
          items: [
            {
              name: 'sessions',
              type: LINK_BUTTON,
              label: trans('all_sessions', {}, 'cursus'),
              target: props.path + '/sessions',
              exact: true
            }, {
              name: 'participants',
              type: LINK_BUTTON,
              label: trans('participants'),
              target: props.path + '/sessions/participants'
            }, {
              name: 'tutors',
              type: LINK_BUTTON,
              label: trans('tutors', {}, 'cursus'),
              target: props.path + '/sessions/tutors'
            }
          ]
        }
      }, {
        name: 'events',
        type: MENU_BUTTON,
        label: trans('session_events', {}, 'cursus'),
        displayed: props.canRegister,
        indicator: true,
        menu: {
          align: 'end',
          items: [
            {
              name: 'events',
              type: LINK_BUTTON,
              label: trans('all_events', {}, 'cursus'),
              target: props.path + '/events',
              exact: true
            }, {
              name: 'participants',
              type: LINK_BUTTON,
              label: trans('participants'),
              target: props.path + '/events/participants'
            }, {
              name: 'tutors',
              type: LINK_BUTTON,
              label: trans('tutors', {}, 'cursus'),
              target: props.path + '/events/tutors'
            }
          ]
        }
      }
    ]}
    pages={[
      {
        path: '/',
        component: TrainingsOverview,
        exact: true
      }, {
        path: '/course',
        component: CatalogMain,
        disabled: 'desktop' !== props.contextType
      }, {
        path: '/sessions',
        component: SessionMain,
        disabled: !props.canRegister
      }, {
        path: '/events',
        component: EventMain
      }, {
        path: '/presence/:code',
        render: (routerProps) => (
          <SignPresence code={routerProps.match.params.code} path={`${props.path}/presence`} />
        )
      }, {
        path: '/presence',
        exact: true,
        render: () => (
          <EventPresence path={`${props.path}/presence`}/>
        )
      }
    ]}
    redirect={[
      {from: '/catalog', to: '/course', exact: true, disabled: 'desktop' !== props.contextType}, // ATTENTION: for retro-compatibility with 14.2 routes
      {from: '/catalog/:slug', to: '/course/:slug', disabled: 'desktop' !== props.contextType} // ATTENTION: for retro-compatibility with 14.2 routes
    ]}
    editor={TrainingsEditor}
    // dashboard={TrainingsDashboard}
  />

TrainingsTool.propTypes = {
  contextType: T.string.isRequired,
  authenticated: T.bool.isRequired,
  canEdit: T.bool.isRequired,
  canRegister: T.bool.isRequired,
  path: T.string.isRequired
}

export {
  TrainingsTool
}
