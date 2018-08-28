import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {withRouter} from '#/main/app/router'
import {trans} from '#/main/core/translation'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {User as UserTypes} from '#/main/core/user/prop-types'
import {TooltipAction} from '#/main/core/layout/button/components/tooltip-action' // todo : use Button
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {MODAL_ALERT} from '#/main/app/modals/alert'
import {currentUser} from '#/main/core/user/current'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {select} from '#/plugin/forum/resources/forum/store/selectors'

const SubjectFormWrapper = (props) =>
  <div>
    <div className='user-message-container user-message-form-container user-message-left'>
      <UserAvatar picture={props.user.picture} />

      <div className="user-message">
        <div className="user-message-meta">
          <div className="user-message-info">
            {props.user.name}
          </div>

          {(props.editingSubject && props.cancel) &&
            <div className="user-message-actions">
              <TooltipAction
                id="close"
                className="btn-link-default"
                position="bottom"
                icon="fa fa-fw fa-times"
                label={trans('cancel')}
                action={props.cancel}
              />
            </div>
          }
        </div>

        <div className="user-message-content embedded-form-section">
          {props.children}
        </div>

        <Button
          className="btn btn-block btn-save btn-emphasis"
          label={props.editingSubject ? trans('save') : trans('post_the_subject', {}, 'forum')}
          type={CALLBACK_BUTTON}
          callback={props.callback}
          primary={true}
        />
      </div>
    </div>
  </div>

SubjectFormWrapper.propTypes = {
  /**
   * The user who is creating the subject.
   *
   * @type {object}
   */
  user: T.shape(UserTypes.propTypes),
  /**
   * The action of the button
   *
   * @type {func}
   */
  callback: T.func.isRequired,
  /**
   * The content of the wrapper
   *
   * @type {node}
   */
  children: T.node.isRequired,

  cancel: T.func,
  editingSubject: T.bool.isRequired
}

const SubjectFormComponent = (props) => {
  const saveSubjectForm = (forumId, editingSubject, subjectId) => {
    if (editingSubject) {
      props.editSubject(forumId, subjectId)
    }
    else if (!props.moderator &&
      props.forum.moderation === 'PRIOR_ALL' ||
      props.forum.moderation === 'PRIOR_ONCE') {
      props.createModeratedSubject(forumId, subjectId, props.forum.moderation)
      props.showModal(MODAL_ALERT, {
        title: trans('moderated_posts', {}, 'forum'),
        message: trans('moderated_posts_explanation', {}, 'forum'),
        type: 'info'
      })
    } else {
      props.createSubject(forumId, subjectId)
    }
  }

  return (
    <div>
      {props.bannedUser ?
        <div className="alert alert-danger">
          {trans('banned_user_warning', {}, 'forum')}
        </div> :
        <SubjectFormWrapper
          user={currentUser()}
          callback={() => saveSubjectForm(props.forum.id, props.editingSubject, props.subject.id, props.forum.moderation)}
          cancel={() => props.history.push(`/subjects/show/${props.subject.id}`)}
          editingSubject={props.editingSubject}
        >
          <FormData
            level={3}
            displayLevel={2}
            name={`${select.STORE_NAME}.subjects.form`}
            className="content-container"
            sections={[
              {
                title: trans('general'),
                primary: true,
                fields: [
                  {
                    name: 'title',
                    type: 'string',
                    label: trans('forum_subject_title_form_title', {}, 'forum'),
                    required: true
                  },
                  {
                    name: 'content',
                    type: 'html',
                    label: trans('post', {}, 'forum'),
                    required: true
                  },
                  {
                    name: 'tags',
                    type: 'string',
                    label: trans('tags'),
                    help: trans('tag_form_help', {}, 'forum')
                  }
                ]
              }, {
                icon: 'fa fa-fw fa-desktop',
                title: trans('display_parameters'),
                fields: [
                  {
                    name: 'meta.sticky',
                    alias: 'sticked',
                    type: 'boolean',
                    label: trans('stick', {}, 'forum'),
                    help: trans('stick_explanation', {}, 'forum')
                  }, {
                    name: 'poster',
                    label: trans('poster'),
                    type: 'file',
                    options: {
                      ratio: '3:1'
                    }
                  }
                ]
              }
            ]}
          />
        </SubjectFormWrapper>
      }
    </div>
  )
}


const SubjectForm = withRouter(connect(
  state => ({
    bannedUser: select.bannedUser(state),
    subject: formSelect.data(formSelect.form(state, `${select.STORE_NAME}.subjects.form`)),
    editingSubject: select.editingSubject(state),
    forum: select.forum(state),
    moderator: select.moderator(state)
  }),
  (dispatch, ownProps) => ({
    editSubject(forumId, subjectId) {
      dispatch(formActions.saveForm(`${select.STORE_NAME}.subjects.form`, ['apiv2_forum_subject_update', {id: subjectId}])).then(() => {
        ownProps.history.push(`/subjects/show/${subjectId}`)
      })
    },
    createModeratedSubject(forumId) {
      dispatch(formActions.saveForm(`${select.STORE_NAME}.subjects.form`, ['claroline_forum_api_forum_createsubject', {id: forumId}])).then(() => {
        ownProps.history.push('/subjects')
      })
    },
    createSubject(forumId, subjectId) {
      dispatch(formActions.saveForm(`${select.STORE_NAME}.subjects.form`, ['claroline_forum_api_forum_createsubject', {id: forumId}])).then(() => {
        ownProps.history.push(`/subjects/show/${subjectId}`)
      })
    }
  })
)(SubjectFormComponent))

export {
  SubjectForm
}
