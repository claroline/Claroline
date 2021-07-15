import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'
import {UserList} from '#/main/core/administration/community/user/components/user-list'

import {selectors} from '#/plugin/announcement/resources/announcement/modals/sending/store'

const SendingModal = (props) =>
  <Modal
    {...omit(props, 'aggregateId', 'announcementId', 'handleConfirm')}
    className="data-picker-modal"
    title={trans('announcement_sending', {}, 'announcement')}
    icon="fa fa-fw fa-paper-plane"
    bsSize="lg"
  >

    <ListData
      name={selectors.STORE_NAME+'.receivers'}
      fetch={{
        url: ['claro_announcement_validate', {aggregateId: props.aggregateId, id: props.announcementId}],
        autoload: false
      }}
      definition={UserList.definition}
      card={UserList.card}
      selectable={false}
      filterable={false}
      paginated={true}
      sortable={true}
    />

    <Button
      className="modal-btn"
      type={CALLBACK_BUTTON}
      label={trans('send', {}, 'actions')}
      primary={true}
      callback={() => {
        props.fadeModal()
      }}
    />
  </Modal>

SendingModal.propTypes = {
  aggregateId: T.string.isRequired,
  announcementId: T.string.isRequired,

  // from modal
  fadeModal: T.func.isRequired
}

export {
  SendingModal
}
