import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {trans} from '#/main/core/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {DataDetails} from '#/main/core/data/details/components/details'
import {ContentMeta} from '#/main/app/content/meta/components/meta'

import {WorkspaceMetrics} from '#/main/core/workspace/components/metrics'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

const AboutModal = props =>
  <Modal
    {...omit(props, 'workspace')}
    icon="fa fa-fw fa-info"
    title={trans('about')}
    subtitle={props.workspace.name}
  >
    <ContentMeta
      meta={props.workspace.meta}
    />

    <div className="modal-body">
      <WorkspaceMetrics
        workspace={props.workspace}
        level={5}
        width={80}
        height={80}
      />
    </div>

    <DataDetails
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
              calculated: (workspace) => url(['claro_workspace_subscription_url_generate', {
                slug: workspace.meta.slug
              }, true])
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
              options: {
                icon: 'fa fa-fw fa-object-group',
                labelChecked: 'Cet espace d\'activités est un modèle.'
              }
            }, {
              name: 'meta.personal',
              label: 'Cet espace d\'activités n\'est pas un espace personnel.',
              type: 'boolean',
              options: {
                icon: 'fa fa-fw fa-user',
                labelChecked: 'Cet espace d\'activités est un espace personnel.'
              }
            }, {
              name: 'registration.selfRegistration',
              label: 'Les inscriptions sont gérées par les gestionnaires.',
              type: 'boolean',
              options: {
                icon: 'fa fa-fw fa-user-plus',
                labelChecked: 'Les inscriptions sont publiques.'
              }
            }, {
              name: 'registration.selfUnregistration',
              label: 'Les désinscriptions sont gérées par les gestionnaires.',
              type: 'boolean',
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
