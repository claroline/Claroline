import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'

import {MODAL_CONFIRM, MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {actions} from './../actions'

import {Announcement as AnnouncementTypes} from './../prop-types'
import {AnnouncePost} from './announce-post.jsx'

import {select} from './../selectors'

const AnnounceDetail = props =>
  <AnnouncePost
    active={true}
    sendPost={() => props.sendPost(props.aggregateId, props.announcement)}
    removePost={() => props.sendPost(props.aggregateId, props.announcement)}
    {...props.announcement}
  />

AnnounceDetail.propTypes = {
  aggregateId: T.string.isRequired,
  announcement: T.shape(
    AnnouncementTypes.propTypes
  ).isRequired,
  sendPost: T.func.isRequired,
  removePost: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    aggregateId: select.aggregateId(state),
    announcement: select.detail(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
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
  }
}

const Announce = connect(mapStateToProps, mapDispatchToProps)(AnnounceDetail)

export {
  Announce
}
