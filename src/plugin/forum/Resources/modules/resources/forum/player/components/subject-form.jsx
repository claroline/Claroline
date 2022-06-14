import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'

import {withRouter} from '#/main/app/router'
import {withModal} from '#/main/app/overlays/modal/withModal'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {MODAL_ALERT} from '#/main/app/modals/alert'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions as formActions, selectors as formSelect} from '#/main/app/content/form/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {User as UserTypes} from '#/main/core/user/prop-types'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {UserAvatar} from '#/main/core/user/components/avatar'

import {selectors} from '#/plugin/forum/resources/forum/store'

const SubjectFormWrapper = (props) => {
  //this is a hack while we don't have the proper login redirection
  if (!props.user) {
    window.location.replace(url(['claro_security_login', {}, true]))
  }

  return(
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
                <Button
                  type={CALLBACK_BUTTON}
                  className="btn-link"
                  tooltip="bottom"
                  icon="fa fa-fw fa-times"
                  label={trans('cancel', {}, 'actions')}
                  callback={props.cancel}
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
  )
}

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
      (props.forum.moderation === 'PRIOR_ALL' || (props.forum.moderation === 'PRIOR_ONCE' && !props.isValidatedUser))) {
      props.showModal(MODAL_ALERT, {
        title: trans('moderated_posts', {}, 'forum'),
        message: trans('moderated_posts_explanation', {}, 'forum'),
        type: 'info'
      })
      props.createModeratedSubject(forumId, subjectId, props.forum.moderation)
    } else {
      props.createSubject(forumId, subjectId, props.path)
    }
  }

  return (
    <div>
      {props.bannedUser ?
        <div className="alert alert-danger">
          {trans('banned_user_warning', {}, 'forum')}
        </div> :
        <SubjectFormWrapper
          user={props.currentUser}
          callback={() => saveSubjectForm(props.forum.id, props.editingSubject, props.subject.id, props.forum.moderation)}
          cancel={() => props.history.push(`${props.path}/subjects/show/${props.subject.id}`)}
          editingSubject={props.editingSubject}
        >
          <FormData
            level={3}
            displayLevel={2}
            name={`${selectors.STORE_NAME}.subjects.form`}
            sections={[
              {
                title: trans('general'),
                primary: true,
                fields: [
                  {
                    name: 'title',
                    type: 'string',
                    label: trans('title'),
                    required: true
                  },
                  {
                    name: 'content',
                    type: 'html',
                    label: trans('post', {}, 'forum'),
                    required: true,
                    options: {
                      workspace: props.workspace
                    }
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
                    type: 'image'
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

const SubjectForm = withRouter(withModal(connect(
  state => ({
    path: resourceSelectors.path(state),
    currentUser: securitySelectors.currentUser(state),
    workspace: resourceSelectors.workspace(state),
    bannedUser: selectors.bannedUser(state),
    subject: formSelect.data(formSelect.form(state, `${selectors.STORE_NAME}.subjects.form`)),
    editingSubject: selectors.editingSubject(state),
    forum: selectors.forum(state),
    moderator: selectors.moderator(state),
    isValidatedUser: selectors.isValidatedUser(state)
  }),
  (dispatch, ownProps) => ({
    editSubject(forumId, subjectId) {
      dispatch(formActions.saveForm(`${selectors.STORE_NAME}.subjects.form`, ['apiv2_forum_subject_update', {id: subjectId}])).then(() => {
        ownProps.history.push(`${ownProps.path}/subjects/show/${subjectId}`)
      })
    },
    createModeratedSubject(forumId) {
      dispatch(formActions.saveForm(`${selectors.STORE_NAME}.subjects.form`, ['claroline_forum_api_forum_createsubject', {id: forumId}])).then(() => {
        ownProps.history.push(`${ownProps.path}/subjects`)
      })
    },
    createSubject(forumId, subjectId, path) {
      dispatch(formActions.saveForm(`${selectors.STORE_NAME}.subjects.form`, ['claroline_forum_api_forum_createsubject', {id: forumId}])).then(() => {
        ownProps.history.push(`${path}/subjects/show/${subjectId}`)
      })
    }
  })
)(SubjectFormComponent)))

export {
  SubjectForm
}
