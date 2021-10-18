import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {GridSelection} from '#/main/app/content/grid/components/selection'

const ShareModal = (props) => {
  return (
    <Modal
      {...omit(props, 'url')}
      icon="fa fa-fw fa-share-alt"
      title={trans('share', {}, 'actions')}
      subtitle={props.title}
    >
      <GridSelection
        items={[
          {
            id: 'facebook',
            label: 'Facebook',
            icon: 'fa fa-fw fa-facebook',
            description: trans('share_on', {network: 'Facebook'}, 'icap_socialmedia')
          }, {
            id: 'linkedin',
            label: 'LinkedIn',
            icon: 'fa fa-fw fa-linkedin',
            description: trans('share_on', {network: 'LinkedIn'}, 'icap_socialmedia')
          }, {
            id: 'twitter',
            label: 'Twitter',
            icon: 'fa fa-fw fa-twitter',
            description: trans('share_on', {network: 'Twitter'}, 'icap_socialmedia')
          }
        ]}
        handleSelect={(type) => {
          let url
          switch (type.id) {
            case 'facebook':
              url = `https://www.facebook.com/sharer/sharer.php?u=${props.url}&src=sdkpreparse`
              break
            case 'linkedin':
              url = `https://www.linkedin.com/sharing/share-offsite/?url=${props.url}`
              break
            case 'twitter':
              url = `https://twitter.com/intent/tweet?text=${props.url}`
              break
          }

          window.open(url, '_blank').focus()
          props.fadeModal()
        }}
      />
    </Modal>
  )
}

ShareModal.propTypes = {
  title: T.string.isRequired,
  url: T.string.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ShareModal
}
