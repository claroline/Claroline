import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {actions as formActions} from '#/main/core/data/form/actions'
import {Button} from '#/main/app/action/components/button'
import ButtonToolbar from 'react-bootstrap/lib/ButtonToolbar'
import {trans} from '#/main/core/translation'
import {BlogOptionsType} from '#/plugin/blog/resources/blog/editor/components/prop-types'
import {ToolManager} from '#/plugin/blog/resources/blog/editor/components/tool-manager.jsx'
import {constants} from '#/plugin/blog/resources/blog/constants.js'
import {FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {withRouter} from '#/main/app/router'

const BlogOptionsComponent = props =>
  <section className="resource-section">
    <h2 className="h-first">{trans('configure_blog', {}, 'icap_blog')}</h2>
    {props.mode === constants.EDIT_OPTIONS &&
      <FormContainer
        level={2}
        name="blog.data.options"
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
                label: trans('infobar', {}, 'icap_blog')
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
            label={trans('icap_blog_options_form_init', {}, 'icap_blog')}
            type="callback"
            className="btn"
            callback={() => {
              props.cancel(props.history)
            }}
          />
        </ButtonToolbar>
      </FormContainer>
    }
  </section>
    
BlogOptionsComponent.propTypes = {
  options: T.shape(BlogOptionsType.propTypes),
  cancel: T.func.isRequired,
  mode: T.string,
  history: T.shape({}),
  saveEnabled: T.bool.isRequired
}

const BlogOptions = withRouter(connect(
  state => ({
    options: formSelect.data(formSelect.form(state, constants.OPTIONS_EDIT_FORM_NAME)),
    mode: state.mode,
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, constants.OPTIONS_EDIT_FORM_NAME))
  }), 
  dispatch => ({
    cancel: (history) => {
      dispatch(
        formActions.cancelChanges(constants.OPTIONS_EDIT_FORM_NAME)
      )
      history.push('/')
    }
  })
)(BlogOptionsComponent))

export {BlogOptions}