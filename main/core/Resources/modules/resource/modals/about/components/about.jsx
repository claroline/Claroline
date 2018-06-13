import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {trans} from '#/main/core/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {DataDetails} from '#/main/core/data/details/components/details'
import {ContentMeta} from '#/main/app/content/meta/components/meta'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

// todo implement

const AboutModal = props =>
  <Modal
    {...omit(props, 'resourceNode')}
    icon="fa fa-fw fa-info"
    title={trans('about')}
    subtitle={props.resourceNode.name}
  >
    <ContentMeta
      meta={props.resourceNode.meta}
    />

    <div className="modal-body">
      TODO some metrics about the resource ?
    </div>

    <DataDetails
      data={props.resourceNode}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'url',
              type: 'url',
              label: trans('url', {}, 'data'),
              calculated: (resourceNode) => url(['claro_resource_open', {
                resourceType: resourceNode.meta.type,
                node: resourceNode.id
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
