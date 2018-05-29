import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {DataDetails} from '#/main/core/data/details/components/details'
import {ContentMeta} from '#/main/app/content/meta/components/meta'
import {ContentPublicUrl} from '#/main/app/content/meta/components/public-url'

import {WorkspaceMetrics} from '#/main/core/workspace/components/metrics'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

const AboutModal = props =>
  <Modal
    {...omit(props, 'workspace')}
    icon="fa fa-fw fa-info"
    title={trans('about')}
    subtitle={props.workspace.name}
  >
    <ContentPublicUrl
      className="modal-link"
      url={['claro_workspace_subscription_url_generate', {slug: props.workspace.meta.slug}, true]}
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
                labelChecked: 'Cette espace d\'activités est un modèle.'
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

    <div className="modal-footer">
      <ContentMeta
        meta={props.workspace.meta}
      />
    </div>
  </Modal>

AboutModal.propTypes = {
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired
}

export {
  AboutModal
}
