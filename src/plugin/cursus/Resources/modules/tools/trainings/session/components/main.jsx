import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router/components/routes'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentTabs} from '#/main/app/content/components/tabs'
import {ToolPage} from '#/main/core/tool/containers/page'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {SessionList} from '#/plugin/cursus/session/components/list'
import {selectors} from '#/plugin/cursus/tools/trainings/session/store/selectors'

const SessionMain = (props) =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('my_courses', {}, 'cursus'),
      target: `${props.path}/registered`
    }]}
    subtitle={trans('my_courses', {}, 'cursus')}
  >
    <header className="row content-heading">
      <ContentTabs
        sections={[
          {
            name: 'current',
            type: LINK_BUTTON,
            label: trans('Actives', {}, 'cursus'),
            target: `${props.path}/registered`,
            exact: true
          }, {
            name: 'ended',
            type: LINK_BUTTON,
            label: trans('Terminées', {}, 'cursus'),
            target: `${props.path}/registered/ended`
          }, {
            name: 'pending',
            type: LINK_BUTTON,
            label: trans('pending_registrations'),
            target: `${props.path}/registered/pending`
          }
        ]}
      />
    </header>

    <Routes
      path={`${props.path}/registered`}
      routes={[
        {
          path: '/',
          exact: true,
          onEnter: () => props.invalidateList(),
          render: () => (
            <SessionList
              path={props.path+'/catalog'}
              name={selectors.STORE_NAME}
              url={['apiv2_cursus_my_sessions_active']}
              actions={(rows) => [
                {
                  type: LINK_BUTTON,
                  icon: 'fa fa-fw fa-book',
                  label: trans('open-workspace', {}, 'actions'),
                  target: workspaceRoute(rows[0].workspace),
                  displayed: !!rows[0].workspace,
                  scope: ['object']
                }
              ]}
            />
          )
        }, {
          path: '/ended',
          onEnter: () => props.invalidateList(),
          render: () => (
            <SessionList
              path={props.path+'/catalog'}
              name={selectors.STORE_NAME}
              url={['apiv2_cursus_my_sessions_ended']}
              actions={(rows) => [
                {
                  name: 'open-workspace',
                  type: LINK_BUTTON,
                  icon: 'fa fa-fw fa-book',
                  label: trans('open-workspace', {}, 'actions'),
                  target: workspaceRoute(rows[0].workspace),
                  displayed: !!rows[0].workspace,
                  scope: ['object']
                }
              ]}
            />
          )
        }, {
          path: '/pending',
          onEnter: () => props.invalidateList(),
          render: () => (
            <SessionList
              path={props.path+'/catalog'}
              name={selectors.STORE_NAME}
              url={['apiv2_cursus_my_sessions_pending']}
            />
          )
        }
      ]}
    />
  </ToolPage>

SessionMain.propTypes = {
  path: T.string.isRequired,
  invalidateList: T.func.isRequired
}

export {
  SessionMain
}
