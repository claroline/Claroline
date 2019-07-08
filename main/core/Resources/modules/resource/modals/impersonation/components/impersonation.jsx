import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

//import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'

//import {RoleCard} from '#/main/core/user/data/components/role-card'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

// todo implement (see Workspace impersonation)

const ImpersonationModal = props =>
  <Modal
    {...omit(props, 'resourceNode')}
    icon="fa fa-fw fa-mask"
    title={trans('view-as', {}, 'actions')}
    subtitle={props.resourceNode.name}
  >
    <div className="modal-body">
      TODO
    </div>
  </Modal>

ImpersonationModal.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  fadeModal: T.func.isRequired
}

export {
  ImpersonationModal
}
