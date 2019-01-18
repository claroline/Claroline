import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {DetailsData} from '#/main/app/content/details/components/data'
import {ContentMeta} from '#/main/app/content/meta/components/meta'

import {ResourceType} from '#/main/core/resource/components/type'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

const AboutModal = props =>
  <Modal
    {...omit(props, 'resourceNode')}
    icon="fa fa-fw fa-info"
    title={trans('about')}
    subtitle={props.resourceNode.name}
  >
    <ContentMeta
      creator={get(props.resourceNode, 'meta.creator')}
      created={get(props.resourceNode, 'meta.created')}
      updated={get(props.resourceNode, 'meta.updated')}
    />

    <DetailsData
      data={props.resourceNode}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'meta.type',
              label: trans('type'),
              type: 'string',
              hideLabel: true,
              render: (resourceNode) => {
                const NodeType =
                  <ResourceType
                    name={resourceNode.meta.type}
                    mimeType={resourceNode.meta.mimeType}
                  />

                return NodeType
              }
            }, {
              name: 'url',
              type: 'url',
              label: trans('url', {}, 'data'),
              calculated: (resourceNode) => url(['claro_resource_show_short', {
                id: resourceNode.id
              }, true])
            }, {
              name: 'meta.description',
              label: trans('description'),
              type: 'string'
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
