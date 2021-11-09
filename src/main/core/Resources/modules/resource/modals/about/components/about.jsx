import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {DetailsData} from '#/main/app/content/details/components/data'

import {route} from '#/main/core/resource/routing'
import {ResourceIcon} from '#/main/core/resource/components/icon'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

const AboutModal = props =>
  <Modal
    {...omit(props, 'resourceNode')}
    icon="fa fa-fw fa-info"
    title={trans('about')}
    subtitle={props.resourceNode.name}
    poster={props.resourceNode.poster ? props.resourceNode.poster.url : undefined}
  >
    <DetailsData
      meta={true}
      data={props.resourceNode}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'meta.type',
              label: trans('type'),
              type: 'type',
              hideLabel: true,
              calculated: (resourceNode) => ({
                icon: <ResourceIcon mimeType={resourceNode.meta.mimeType} />,
                name: trans(resourceNode.meta.type, {}, 'resource'),
                description: trans(`${resourceNode.meta.type}_desc`, {}, 'resource')
              })
            }, {
              name: 'url',
              type: 'url',
              label: trans('url', {}, 'data'),
              calculated: (resourceNode) => `${url(['claro_index', {}, true])}#${route(resourceNode)}`
            }, {
              name: 'meta.description',
              label: trans('description'),
              type: 'string'
            }, {
              name: 'parent',
              label: trans('directory', {}, 'resource'),
              type: 'resource'
            }, {
              name: 'workspace',
              label: trans('workspace'),
              type: 'workspace'
            }, {
              name: 'id',
              label: trans('id'),
              type: 'string',
              calculated: (resourceNode) => resourceNode.id + ' / ' + resourceNode.autoId
            }
          ]
        }
      ]}
    />
  </Modal>

AboutModal.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired
}

export {
  AboutModal
}
