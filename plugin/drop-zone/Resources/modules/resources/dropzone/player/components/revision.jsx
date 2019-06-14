import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'

import {DropType, Revision as RevisionType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {select} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {actions} from '#/plugin/drop-zone/resources/dropzone/player/actions'
import {Documents} from '#/plugin/drop-zone/resources/dropzone/components/documents'
import {Comments} from '#/plugin/drop-zone/resources/dropzone/player/components/comments'

const RevisionComponent = props => props.revision && props.drop ?
  <section className="resource-section revision-panel">
    <h2>{trans('revision', {}, 'dropzone')}</h2>

    <table className="revision-table table table-responsive table-bordered">
      <tbody>
        <tr>
          <th>{trans('creator')}</th>
          <td>{props.revision.creator ? `${props.revision.creator.firstName} ${props.revision.creator.lastName}` : trans('unknown')}</td>
        </tr>
        <tr>
          <th>{trans('creation_date')}</th>
          <td>{displayDate(props.revision.creationDate, false, true)}</td>
        </tr>
      </tbody>
    </table>

    <Documents
      documents={props.revision.documents}
      {...props}
    />

    <hr/>

    <Comments
      comments={props.drop.comments}
      dropId={props.drop.id}
      title={trans('drop_comments', {}, 'dropzone')}
      saveComment={props.saveDropComment}
    />

    <hr className="revision-comments-separator"/>

    <Comments
      comments={props.revision.comments}
      revisionId={props.revision.id}
      title={trans('revision_comments', {}, 'dropzone')}
      saveComment={props.saveRevisionComment}
    />
  </section> :
  <div>
  </div>

RevisionComponent.propTypes = {
  revision: T.shape(RevisionType.propTypes),
  drop: T.shape(DropType.propTypes),
  saveDropComment: T.func.isRequired,
  saveRevisionComment: T.func.isRequired
}

const Revision = connect(
  (state) => ({
    revision: select.revision(state),
    drop: select.currentDrop(state)
  }),
  (dispatch) => ({
    saveDropComment(comment) {
      dispatch(actions.saveDropComment(comment))
    },
    saveRevisionComment(comment) {
      dispatch(actions.saveRevisionComment(comment))
    }
  })
)(RevisionComponent)

export {
  Revision
}
