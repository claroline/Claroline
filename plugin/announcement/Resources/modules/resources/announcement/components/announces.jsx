import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t, trans} from '#/main/core/translation'

import {MODAL_CONFIRM, MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {actions} from './../actions'
import {select} from './../selectors'

import {AnnouncePost} from './announce-post.jsx'

const AnnouncesList = props =>
  <div>
    <div className="announces-sort">
      {t('list_sort_by')}
      <button
        type="button"
        className="btn btn-link"
        onClick={props.toggleSort}
      >
        {trans(1 === props.sortOrder ? 'from_older_to_newer':'from_newer_to_older', {}, 'announcement')}
      </button>
    </div>

    {props.posts.map(post =>
      <AnnouncePost
        {...post}
        key={post.id}
        removePost={() => props.removePost(props.aggregateId, post)}
        sendPost={() => props.sendPost(props.aggregateId, post)}
      />
    )}

    {1 !== props.pages &&
      <nav className="text-right">
        <div className="pagination-condensed btn-group">
          <button
            type="button"
            className="btn btn-pagination btn-previous"
            disabled={0 === props.currentPage}
            onClick={() => props.changePage(props.currentPage - 1)}
          >
            <span className="fa fa-angle-double-left" aria-hidden="true" />
            <span className="sr-only">
              {trans(1 === props.sortOrder ? 'older':'newer', {}, 'announcement')}
            </span>
          </button>

          <button
            type="button"
            className="btn btn-pagination btn-next"
            disabled={(props.pages - 1) === props.currentPage}
            onClick={() => props.changePage(props.currentPage + 1)}
          >
            {trans(1 === props.sortOrder ? 'newer':'older', {}, 'announcement')}
            <span className="fa fa-angle-double-right" aria-hidden="true" />
          </button>
        </div>
      </nav>
    }
  </div>

AnnouncesList.propTypes = {
  sortOrder: T.number.isRequired,
  currentPage: T.number.isRequired,
  pages: T.number.isRequired,
  aggregateId: T.string.isRequired,
  posts: T.arrayOf(T.shape({
    id: T.string.isRequired
  })).isRequired,
  toggleSort: T.func.isRequired,
  changePage: T.func.isRequired,
  sendPost: T.func.isRequired,
  removePost: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    sortOrder: select.sortOrder(state),
    currentPage: select.currentPage(state),
    pages: select.pages(state),
    aggregateId: select.aggregateId(state),
    posts: select.visibleSortedPosts(state)
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
    },
    toggleSort() {
      dispatch(actions.toggleAnnouncesSort())
    },
    changePage(page) {
      dispatch(actions.changeAnnouncesPage(page))
    }
  }
}

const Announces = connect(mapStateToProps, mapDispatchToProps)(AnnouncesList)

export {
  Announces
}
