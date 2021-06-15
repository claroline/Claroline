import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {DetailsData} from '#/main/app/content/details/components/data'

import {route} from '#/main/core/workspace/routing'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

const AboutModal = props =>
  <Modal
    {...omit(props, 'workspace')}
    icon="fa fa-fw fa-info"
    title={trans('about')}
    subtitle={props.workspace.name}
    poster={props.workspace.poster ? props.workspace.poster.url : undefined}
  >
    <DetailsData
      meta={true}
      data={props.workspace}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'url',
              type: 'url',
              label: trans('url', {}, 'data'),
              calculated: (workspace) => `${url(['claro_index', {}, true])}#${route(workspace)}`
            }, {
              name: 'meta.description',
              label: trans('description'),
              type: 'string'
            }, {
              name: 'contactEmail',
              label: trans('contact'),
              type: 'email',
              displayed: (workspace) => !!workspace.contactEmail
            }, {
              name: 'code',
              label: trans('code'),
              type: 'string'
            }, {
              name: 'meta.model',
              label: trans('workspace_model', {}, 'workspace'),
              type: 'boolean',
              hideLabel: true,
              displayed: get(props.workspace, 'meta.model'),
              options: {
                icon: 'fa fa-fw fa-briefcase'
              }
            }, {
              name: 'meta.personal',
              label: trans('workspace_personal', {}, 'workspace'),
              type: 'boolean',
              hideLabel: true,
              displayed: get(props.workspace, 'meta.personal'),
              options: {
                icon: 'fa fa-fw fa-user'
              }
            }, {
              name: 'meta.archived',
              label: trans('workspace_archived', {}, 'workspace'),
              type: 'boolean',
              hideLabel: true,
              displayed: get(props.workspace, 'meta.archived'),
              options: {
                icon: 'fa fa-fw fa-box'
              }
            }, {
              name: 'registration.selfRegistration',
              label: trans('workspace_manager_registration', {}, 'workspace'),
              type: 'boolean',
              hideLabel: true,
              options: {
                icon: 'fa fa-fw fa-user-plus',
                labelChecked: trans('workspace_public_registration', {}, 'workspace')
              }
            }, {
              name: 'registration.selfUnregistration',
              label: trans('workspace_manager_unregistration', {}, 'workspace'),
              type: 'boolean',
              hideLabel: true,
              options: {
                icon: 'fa fa-fw fa-user-times',
                labelChecked: trans('workspace_public_unregistration', {}, 'workspace')
              }
            }, {
              name: 'id',
              label: trans('id'),
              type: 'string',
              calculated: (workspace) => workspace.id + ' / ' + workspace.autoId
            }
          ]
        }
      ]}
    />
  </Modal>

AboutModal.propTypes = {
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired
}

export {
  AboutModal
}
