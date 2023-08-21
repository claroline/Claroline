import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentLoader} from '#/main/app/content/components/loader'
import {DetailsData} from '#/main/app/content/details/components/data'

import {route} from '#/main/core/workspace/routing'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

const AboutModal = props =>
  <Modal
    {...omit(props, 'workspaceId', 'workspace', 'get', 'reset')}
    icon="fa fa-fw fa-circle-info"
    title={trans('about')}
    subtitle={props.workspace ? get(props.workspace, 'name') : trans('loading')}
    poster={get(props.workspace, 'poster')}
    onEntering={() => props.get(props.workspaceId)}
    onExiting={() => props.reset()}
  >
    {!props.workspace &&
      <ContentLoader
        size="lg"
        description={trans('loading', {}, 'workspace')}
      />
    }

    {props.workspace &&
      <DetailsData
        meta={true}
        flush={true}
        data={props.workspace}
        definition={[
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
                name: 'code',
                label: trans('code'),
                type: 'string'
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
                label: trans('is_archived', {}, 'workspace'),
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
                name: 'organizations',
                type: 'organizations',
                label: trans('organizations')
              }, {
                name: 'id',
                label: trans('id'),
                type: 'string',
                calculated: (workspace) => workspace.id + ' / ' + workspace.autoId
              }
            ]
          }, {
            icon: 'fa fa-fw fa-award',
            title: trans('evaluation'),
            fields: [
              {
                name: 'evaluation.estimatedDuration',
                label: trans('estimated_duration'),
                type: 'number',
                options: {
                  unit: trans('minutes')
                }
              }
            ]
          }
        ]}
      />
    }
  </Modal>

AboutModal.propTypes = {
  workspaceId: T.string.isRequired,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ),
  get: T.func.isRequired,
  reset: T.func.isRequired
}

export {
  AboutModal
}
