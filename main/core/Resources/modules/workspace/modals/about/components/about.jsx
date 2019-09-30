import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {DetailsData} from '#/main/app/content/details/components/data'
import {ContentMeta} from '#/main/app/content/meta/components/meta'

import {route} from '#/main/core/workspace/routing'
import {WorkspaceMetrics} from '#/main/core/workspace/components/metrics'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

const AboutModal = props =>
  <Modal
    {...omit(props, 'workspace')}
    icon="fa fa-fw fa-info"
    title={trans('about')}
    subtitle={props.workspace.name}
    poster={props.workspace.poster ? props.workspace.poster.url : undefined}
  >
    <ContentMeta
      creator={get(props.workspace, 'meta.creator')}
      created={get(props.workspace, 'meta.created')}
      updated={get(props.workspace, 'meta.updated')}
    />

    <div className="modal-body">
      <WorkspaceMetrics
        workspace={props.workspace}
        level={5}
        width={80}
        height={80}
      />
    </div>

    <DetailsData
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
              name: 'code',
              label: trans('code'),
              type: 'string'
            }, {
              name: 'meta.model',
              label: 'Cet espace d\'activités n\'est pas un modèle.',
              type: 'boolean',
              hideLabel: true,
              options: {
                icon: 'fa fa-fw fa-briefcase',
                labelChecked: 'Cet espace d\'activités est un modèle.'
              }
            }, {
              name: 'meta.personal',
              label: 'Cet espace d\'activités n\'est pas un espace personnel.',
              type: 'boolean',
              hideLabel: true,
              options: {
                icon: 'fa fa-fw fa-user',
                labelChecked: 'Cet espace d\'activités est un espace personnel.'
              }
            }, {
              name: 'registration.selfRegistration',
              label: 'Les inscriptions sont gérées par les gestionnaires.',
              type: 'boolean',
              hideLabel: true,
              options: {
                icon: 'fa fa-fw fa-user-plus',
                labelChecked: 'Les inscriptions sont publiques.'
              }
            }, {
              name: 'registration.selfUnregistration',
              label: 'Les désinscriptions sont gérées par les gestionnaires.',
              type: 'boolean',
              hideLabel: true,
              options: {
                icon: 'fa fa-fw fa-user-times',
                labelChecked: 'Les désinscriptions sont publiques.'
              }
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
