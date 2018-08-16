import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'

import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'

import {actions, selectors} from '#/plugin/announcement/resources/announcement/store'
import {Announcement as AnnouncementTypes} from '#/plugin/announcement/resources/announcement/prop-types'
import {AnnouncePost} from '#/plugin/announcement/resources/announcement/components/announce-post'

const AnnounceDetail = props =>
  <AnnouncePost
    {...props.announcement}
    active={true}
    sendPost={() => props.sendPost(props.aggregateId, props.announcement)}
    removePost={() => props.sendPost(props.aggregateId, props.announcement)}
    editable={props.editable}
    deletable={props.deletable}
  />

AnnounceDetail.propTypes = {
  aggregateId: T.string.isRequired,
  announcement: T.shape(
    AnnouncementTypes.propTypes
  ).isRequired,
  sendPost: T.func.isRequired,
  removePost: T.func.isRequired,
  editable: T.bool.isRequired,
  deletable: T.bool.isRequired
}

const Announce = connect(
  state => ({
    aggregateId: selectors.aggregateId(state),
    announcement: selectors.detail(state),
    editable: hasPermission('edit', resourceSelect.resourceNode(state)),
    deletable: hasPermission('delete', resourceSelect.resourceNode(state))
  }),
  dispatch => ({
    removePost(aggregateId, announcePost) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-trash-o',
          title: trans('remove_announce', {}, 'announcement'),
          question: trans('remove_announce_confirm', {}, 'announcement'),
          dangerous: true,
          handleConfirm: () => dispatch(actions.removeAnnounce(aggregateId, announcePost))
        })
      )
    },
    sendPost(aggregateId, announcePost) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          title: trans('send_announce', {}, 'announcement'),
          question: trans('send_announce_confirm', {}, 'announcement'),
          handleConfirm: () => dispatch(actions.sendAnnounce(aggregateId, announcePost))
        })
      )
    }
  })
)(AnnounceDetail)

export {
  Announce
}
