import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'

import {MODAL_CONFIRM, MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {select as resourceSelect} from '#/main/core/resource/selectors'

import {actions} from '#/plugin/announcement/resources/announcement/actions'
import {select} from '#/plugin/announcement/resources/announcement/selectors'
import {Announcement as AnnouncementTypes} from '#/plugin/announcement/resources/announcement/prop-types'
import {AnnouncePost} from '#/plugin/announcement/resources/announcement/components/announce-post.jsx'

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
    aggregateId: select.aggregateId(state),
    announcement: select.detail(state),
    editable: resourceSelect.editable(state),
    deletable: resourceSelect.deletable(state)
  }),
  dispatch => ({
    removePost(aggregateId, announcePost) {
      dispatch(
        modalActions.showModal(MODAL_DELETE_CONFIRM, {
          title: trans('remove_announce', {}, 'announcement'),
          question: trans('remove_announce_confirm', {}, 'announcement'),
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
