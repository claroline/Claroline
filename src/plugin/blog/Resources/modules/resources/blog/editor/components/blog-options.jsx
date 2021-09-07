import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import ButtonToolbar from 'react-bootstrap/lib/ButtonToolbar'

import {withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {FormData} from '#/main/app/content/form/containers/data'
import {FormSection} from '#/main/app/content/form/components/sections'
import {actions as formActions, selectors as formSelect} from '#/main/app/content/form/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {BlogOptions as BlogOptionsType} from '#/plugin/blog/resources/blog/prop-types'
import {ToolManager} from '#/plugin/blog/resources/blog/editor/components/tool-manager'
import {actions as toolbarActions} from '#/plugin/blog/resources/blog/toolbar/store'
import {selectors} from '#/plugin/blog/resources/blog/store'
import {constants} from '#/plugin/blog/resources/blog/constants'

const BlogOptionsComponent = props =>
  <section className="resource-section">
    {props.mode === constants.EDIT_OPTIONS &&
      <FormData
        title={trans('configure_blog', {}, 'icap_blog')}
        level={2}
        name={selectors.STORE_NAME + '.blog.data.options'}
        sections={[
          {
            id: 'display',
            icon: 'fa fa-fw fa-desktop',
            title: trans('display', {}, 'icap_blog'),
            fields: [
              {
                name: 'displayFullPosts',
                type: 'boolean',
                label: trans('icap_blog_options_form_display_full_posts', {}, 'icap_blog')
              },
              {
                name: 'displayPostViewCounter',
                type: 'boolean',
                label: trans('icap_blog_options_form_display_post_view_counter', {}, 'icap_blog')
              },{
                name: 'postPerPage',
                type: 'choice',
                label: trans('icap_blog_options_form_post_per_page', {}, 'icap_blog'),
                required: true,
                options: {
                  noEmpty: true,
                  condensed: true,
                  choices: constants.PAGE_SIZE
                }
              }
            ]
          },{
            id: 'moderation',
            icon: 'fa fa-fw fa-gavel',
            title: trans('moderation', {}, 'icap_blog'),
            fields: [
              {
                name: 'autoPublishPost',
                type: 'boolean',
                label: trans('icap_blog_options_form_auto_publish_post', {}, 'icap_blog')
              },{
                name: 'authorizeComment',
                type: 'boolean',
                label: trans('icap_blog_options_form_authorize_comment', {}, 'icap_blog'),
                linked: [
                  {
                    name: 'authorizeAnonymousComment',
                    type: 'boolean',
                    label: trans('icap_blog_options_form_authorize_anonymous_comment', {}, 'icap_blog'),
                    required: false,
                    displayed: props.options.authorizeComment
                  },{
                    name: 'commentModerationMode',
                    type: 'choice',
                    required: true,
                    help: trans('icap_blog_post_form_moderation_help', {}, 'icap_blog'),
                    label: trans('comment_moderation_mode', {}, 'icap_blog'),
                    displayed: props.options.authorizeComment,
                    options: {
                      noEmpty: true,
                      condensed: false,
                      choices: constants.COMMENT_MODERATION_MODE
                    }
                  }
                ]
              }
            ]
          },{
            id: 'tag-cloud',
            icon: 'fa fa-fw fa-cloud',
            title: trans('tagcloud', {}, 'icap_blog'),
            fields: [
              {
                name: 'tagCloud',
                type: 'choice',
                required: true,
                label: trans('icap_blog_options_form_tag_cloud', {}, 'icap_blog'),
                options: {
                  noEmpty: true,
                  condensed: true,
                  choices: constants.TAGCLOUD_TYPE
                }
              },{
                name: 'tagTopMode',
                type: 'boolean',
                label: trans('limit_to_tags', {}, 'icap_blog'),
                linked: [
                  {
                    name: 'maxTag',
                    type: 'number',
                    label: trans('limit_to_number', {}, 'icap_blog'),
                    required: true,
                    displayed: props.options.tagTopMode,
                    options: {
                      max: 50
                    }
                  }
                ]
              }
            ]
          },{
            id: 'widgets',
            icon: 'fa fa-fw fa-bars',
            title: trans('infobar', {}, 'icap_blog'),
            fields: [
              {
                name: 'infos',
                type: 'html',
                label: trans('infobar', {}, 'icap_blog'),
                options: {
                  workspace: props.workspace
                }
              }
            ]
          }
        ]}
      >
        <FormSection
          className='toolmanager'
          id="widgets"
          icon="fa fa-fw fa-wrench"
          title={trans('icap_blog_options_form_Order_Widget_Right', {}, 'icap_blog')}>
          <ToolManager />
        </FormSection>
        <ButtonToolbar>
          <Button
            disabled={!props.saveEnabled}
            primary={true}
            label={trans('save')}
            type={CALLBACK_BUTTON}
            className="btn"
            callback={() => {
              props.saveOptions(props.blogId, props.tagOptionsChanged)
            }}
          />
          <Button
            label={trans('icap_blog_options_form_init', {}, 'icap_blog')}
            type={CALLBACK_BUTTON}
            className="btn"
            callback={() => {
              props.cancel(props.history, props.path)
            }}
          />
        </ButtonToolbar>
      </FormData>
    }
  </section>

BlogOptionsComponent.propTypes = {
  path: T.string.isRequired,
  workspace: T.object,
  options: T.shape(BlogOptionsType.propTypes),
  mode: T.string,
  history: T.shape({}),
  blogId: T.string,
  cancel: T.func.isRequired,
  saveEnabled: T.bool.isRequired,
  tagOptionsChanged: T.bool.isRequired,
  saveOptions: T.func.isRequired
}

const BlogOptions = withRouter(connect(
  state => ({
    path: resourceSelectors.path(state),
    workspace: resourceSelectors.workspace(state),
    blogId: selectors.blog(state).data.id,
    options: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.' + constants.OPTIONS_EDIT_FORM_NAME)),
    mode: selectors.mode(state),
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME + '.' + constants.OPTIONS_EDIT_FORM_NAME)),
    tagOptionsChanged:formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.' + constants.OPTIONS_EDIT_FORM_NAME)).tagTopMode !== formSelect.originalData(formSelect.form(state, selectors.STORE_NAME + '.' + constants.OPTIONS_EDIT_FORM_NAME)).tagTopMode
    || formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.' + constants.OPTIONS_EDIT_FORM_NAME)).maxTag !== formSelect.originalData(formSelect.form(state, selectors.STORE_NAME + '.' + constants.OPTIONS_EDIT_FORM_NAME)).maxTag
  }),
  dispatch => ({
    cancel: (history, path) => {
      dispatch(
        formActions.cancelChanges(selectors.STORE_NAME + '.' + constants.OPTIONS_EDIT_FORM_NAME)
      )
      history.push(path)
    },
    saveOptions: (blogId, tagOptionsChanged) => {
      dispatch(
        formActions.saveForm(selectors.STORE_NAME + '.' + constants.OPTIONS_EDIT_FORM_NAME, ['apiv2_blog_options_update', {blogId: blogId}])
      ).then(
        () => {
          //if tag options changed
          if (tagOptionsChanged){
            dispatch(toolbarActions.getTags(blogId))
          }
        })
    }
  })
)(BlogOptionsComponent))

export {BlogOptions}
