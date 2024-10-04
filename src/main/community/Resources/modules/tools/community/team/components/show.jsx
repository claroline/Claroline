import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {Alert} from '#/main/app/components/alert'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {PageTabbedSection} from '#/main/app/page/components/tabbed-section'

import {MODAL_USERS} from '#/main/community/modals/users'
import {UserList} from '#/main/community/user/components/list'

import {Team as TeamTypes} from '#/main/community/team/prop-types'
import {TeamPage} from '#/main/community/team/components/page'
import {selectors} from '#/main/community/tools/community/team/store/selectors'
import {PageSection} from '#/main/app/page/components/section'
import {ContentHtml} from '#/main/app/content/components/html'
import {route} from '#/main/community/team/routing'

const TeamShow = (props) => {
  const full = !!get(props.team, 'restrictions.users') && props.team.users >= get(props.team, 'restrictions.users')

  return (
    <TeamPage
      path={props.path}
      team={props.team}
      reload={props.reload}
    >
      {get(props.team, 'meta.description') &&
        <PageSection size="md" className="pb-5">
          <ContentHtml className="lead">{get(props.team, 'meta.description')}</ContentHtml>
        </PageSection>
      }

      <PageSection size="md" className="bg-body-tertiary">
        {!full && get(props.team, 'registration.selfRegistration') &&
          <Alert type="info" className="my-3">
            {trans('team_self_registration_enabled', {}, 'community')}
          </Alert>
        }

        {full &&
          <Alert type="warning" className="my-3">
            {trans('team_full', {}, 'community')}
          </Alert>
        }

        <DetailsData
          className="mt-3"
          name={selectors.FORM_NAME}
          definition={[
            {
              title: trans('general'),
              primary: true,
              fields: [
                {
                  name: 'directory',
                  label: trans('directory', {}, 'resource'),
                  type: 'resource',
                  displayed: (team) => !isEmpty(team.directory)
                }
              ]
            }
          ]}
        />
      </PageSection>

      <PageSection size="md">
        {!props.hasTeam && !full && get(props.team, 'registration.selfRegistration') &&
          <Button
            className="btn btn-outline-primary w-100 mb-3"
            size="lg"
            type={CALLBACK_BUTTON}
            label={trans('self_register', {}, 'actions')}
            callback={() => props.selfRegister(props.team)}
            primary={true}
          />
        }

        {props.hasTeam && get(props.team, 'registration.selfUnregistration') &&
          <Button
            className="btn btn-outline-primary w-100 mb-3"
            size="lg"
            type={CALLBACK_BUTTON}
            label={trans('self_unregister', {}, 'actions')}
            callback={() => props.selfUnregister(props.team)}
            dangerous={true}
          />
        }
      </PageSection>

      <PageTabbedSection
        size="md"
        className="py-3 embedded-list-section"
        path={route(props.team, props.path)}
        tabs={[
          {
            path: '',
            exact: true,
            icon: 'fa fa-user',
            title: trans('users', {}, 'community'),
            render: () => (
              <UserList
                className="mt-3"
                path={props.path}
                name={`${selectors.FORM_NAME}.users`}
                url={['apiv2_team_list_users', {id: get(props.team, 'id'), role: 'user'}]}
                autoload={!!props.team.id}
                addAction={{
                  name: 'add-users',
                  type: MODAL_BUTTON,
                  icon: 'fa fa-fw fa-plus',
                  label: trans('add_users'),
                  tooltip: 'bottom',
                  disabled: full,
                  displayed: hasPermission('edit', props.team),
                  modal: [MODAL_USERS, {
                    url: ['apiv2_workspace_list_users', {id: props.contextData.id}],
                    selectAction: (selected) => ({
                      type: CALLBACK_BUTTON,
                      label: trans('add', {}, 'actions'),
                      callback: () => props.addUsers(props.team.id, selected)
                    })
                  }]
                }}
                delete={{
                  url: ['apiv2_team_unregister', {id: get(props.team, 'id'), role: 'user'}],
                  label: trans('unregister', {}, 'actions'),
                  displayed: () => hasPermission('edit', props.team)
                }}
                actions={undefined}
              />
            )
          }, {
            path: '/managers',
            icon: 'fa fa-user-tie',
            title: trans('managers', {}, 'community'),
            render: () => (
              <UserList
                className="mt-3"
                path={props.path}
                name={`${selectors.FORM_NAME}.managers`}
                url={['apiv2_team_list_users', {id: get(props.team, 'id'), role: 'manager'}]}
                autoload={!!props.team.id}
                addAction={{
                  name: 'add-managers',
                  type: MODAL_BUTTON,
                  icon: 'fa fa-fw fa-plus',
                  label: trans('add_managers'),
                  tooltip: 'bottom',
                  displayed: hasPermission('edit', props.team),
                  modal: [MODAL_USERS, {
                    url: ['apiv2_workspace_list_users', {id: props.contextData.id}],
                    selectAction: (selected) => ({
                      type: CALLBACK_BUTTON,
                      label: trans('add', {}, 'actions'),
                      callback: () => props.addManagers(props.team.id, selected)
                    })
                  }]
                }}
                delete={{
                  url: ['apiv2_team_unregister', {id: get(props.team, 'id'), role: 'manager'}],
                  label: trans('unregister', {}, 'actions'),
                  displayed: () => hasPermission('edit', props.team)
                }}
                actions={undefined}
              />
            )
          }
        ]}
      />
    </TeamPage>
  )
}

TeamShow.propTypes = {
  path: T.string.isRequired,
  contextData: T.object.isRequired,
  team: T.shape(
    TeamTypes.propTypes
  ),
  hasTeam: T.bool.isRequired,
  reload: T.func.isRequired,
  addUsers: T.func.isRequired,
  addManagers: T.func.isRequired,
  selfRegister: T.func.isRequired,
  selfUnregister: T.func.isRequired
}

export {
  TeamShow
}
