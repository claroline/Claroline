import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {FormData} from '#/main/app/content/form/containers/data'
import {trans} from '#/main/core/translation'
import isEmpty from 'lodash/isEmpty'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

// todo : remove me
import ButtonToolbar from 'react-bootstrap/lib/ButtonToolbar'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {actions as toolbarActions} from '#/plugin/blog/resources/blog/toolbar/store'
import {PostType} from '#/plugin/blog/resources/blog/post/components/prop-types'
import {constants} from '#/plugin/blog/resources/blog/constants'
import {currentUser} from '#/main/core/user/current'
import {withRouter} from '#/main/app/router'
import {select} from '#/plugin/blog/resources/blog/selectors'

const loggedUser = currentUser()

// todo : use standard form buttons

const PostFormComponent = props =>
  <div>
    {(props.mode === constants.CREATE_POST || !isEmpty(props.post.data)) &&
      <FormData
        name={select.STORE_NAME + '.post_edit'}
        sections={[
          {
            id: 'Post',
            title: 'Post form',
            primary: true,
            fields: [
              {
                name: 'title',
                type: 'string',
                label: trans('icap_blog_post_form_title', {}, 'icap_blog'),
                required: true
              },{
                name: 'publicationDate',
                type: 'date',
                help: trans('icap_blog_post_form_publicationDate_help', {}, 'icap_blog'),
                label: trans('icap_blog_post_form_publicationDate', {}, 'icap_blog'),
                required: true,
                options: {
                  time: false
                }
              },{
                name: 'content',
                type: 'html',
                label: trans('icap_blog_post_form_content', {}, 'icap_blog'),
                required: true,
                options: {
                  minRows: 6
                }
              },{
                name: 'tags',
                type: 'string',
                help: trans('icap_blog_post_form_tags_help', {}, 'icap_blog'),
                label: trans('icap_blog_post_form_tags', {}, 'icap_blog'),
                options: {
                  minRows: 6
                }
              }
            ]
          }
        ]}
      >
        <ButtonToolbar>
          <Button
            disabled={!props.saveEnabled}
            primary={true}
            label={trans('save')}
            type={CALLBACK_BUTTON}
            className="btn"
            callback={() => {
              props.save(props.blogId, props.mode, props.postId, props.history, props.originalTags)
            }}
          />
          <Button
            label={trans('cancel')}
            type={CALLBACK_BUTTON}
            className="btn"
            callback={() => {
              props.cancel(props.history)
            }}
          />
        </ButtonToolbar>
      </FormData>
    }
  </div>

PostFormComponent.propTypes = {
  mode: T.string,
  blogId: T.string,
  postId: T.string,
  history: T.shape({}),
  originalTags: T.string,
  goHome: T.bool,
  saveEnabled: T.bool,
  post: T.shape(PostType.propTypes).isRequired,
  save: T.func.isRequired,
  cancel: T.func.isRequired
}

const PostForm = withRouter(connect(
  state => ({
    mode: select.mode(state),
    blogId: select.blog(state).data.id,
    originalTags: formSelect.originalData(formSelect.form(state, select.STORE_NAME + '.post_edit')).tags,
    postId: !isEmpty(state.post_edit) ? state.post_edit.data.id : null,
    post: select.postEdit(state),
    goHome: select.goHome(state),
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, select.STORE_NAME + '.post_edit'))
  }), dispatch => ({
    save: (blogId, mode, postId, history, originalTags) => {
      if (mode === constants.CREATE_POST){
        dispatch(
          formActions.saveForm(select.STORE_NAME + '.' + constants.POST_EDIT_FORM_NAME, ['apiv2_blog_post_new', {blogId: blogId}])
        ).then((response) => {
          if (response && !isEmpty(response.tags)){
            //update tag list
            dispatch(toolbarActions.addTags('', response.tags))
          }
          //upate author list
          dispatch(toolbarActions.addAuthor(loggedUser, response.tags))
          history.push('/')
        })
      }else if (mode === constants.EDIT_POST && postId !== null){
        dispatch(
          formActions.saveForm(select.STORE_NAME + '.' + constants.POST_EDIT_FORM_NAME, ['apiv2_blog_post_update', {blogId: blogId, postId: postId}])
        ).then((response) => {
          if (response && originalTags !== response.tags){
            //update tag list
            dispatch(toolbarActions.addTags(originalTags, response.tags))
          }
          history.push('/')
        })
      }
    },
    cancel: (history) => {
      dispatch(
        formActions.cancelChanges(select.STORE_NAME + '.' + constants.POST_EDIT_FORM_NAME)
      )
      history.push('/')
    }

  })
)(PostFormComponent))

export {PostForm}
