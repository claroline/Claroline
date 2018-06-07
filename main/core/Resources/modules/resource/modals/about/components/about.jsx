import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {DataDetails} from '#/main/core/data/details/components/details'
import {ContentMeta} from '#/main/app/content/meta/components/meta'
import {ContentPublicUrl} from '#/main/app/content/meta/components/public-url'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

// todo implement

const AboutModal = props =>
  <Modal
    {...omit(props, 'resourceNode')}
    icon="fa fa-fw fa-info"
    title={trans('about')}
    subtitle={props.resourceNode.name}
  >
    <ContentPublicUrl
      className="modal-link"
      url={['claro_resource_action', {
        resourceType: props.resourceNode.meta.type,
        action: 'open', // todo : get default from resource action list
        id: props.resourceNode.id
      }, true]}
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
              name: 'meta.description',
              label: trans('description'),
              type: 'string'
            }
          ]
        }
      ]}
    />

    <div className="modal-footer">
      <ContentMeta
        meta={props.resourceNode.meta}
      />
    </div>
  </Modal>

AboutModal.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired
}

export {
  AboutModal
}
