import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/core/translation'
import {TagCloud} from '#/main/app/content/meta/components/tag-cloud'
import {actions as listActions} from '#/main/app/content/list/store'
import {actions as postActions} from '#/plugin/blog/resources/blog/post/store/actions'
import {selectors} from '#/plugin/blog/resources/blog/store'
import {constants} from '#/plugin/blog/resources/blog/constants'
import {cleanTag} from '#/plugin/blog/resources/blog/utils'
import isEmpty from 'lodash/isEmpty'
import {withRouter} from '#/main/app/router'

const TagsComponent = props =>
  <div key='redactors' className="panel panel-default">
    <div className="panel-heading">
      <h2 className="panel-title">{trans('blog_widget_tag_list_blog_form_tag_cloud', {}, 'icap_blog')}</h2>
    </div>
    <div className="panel-body">
      {!isEmpty(props.tags) ?
        (<TagCloud
          tags={props.tags}
          minSize={12}
          maxSize={22}
          onClick={(tag) => {
            props.goHome(props.history)
            props.searchByTag(cleanTag(props.tagMode, tag))
          }}
        />)
        : (
          trans('no_tags', {}, 'icap_blog')
        )
      }
    </div>
  </div>

TagsComponent.propTypes = {
  searchByTag: T.func.isRequired,
  tags: T.object,
  tagMode: T.string,
  history: T.object,
  goHome: T.func.isRequired,
  maxSize: T.number
}

const Tags = withRouter(connect(
  state => ({
    tags: selectors.blog(state).data.options.data.tagCloud === constants.TAGCLOUD_TYPE_CLASSIC_NUM ? selectors.displayTagsFrequency(state) : selectors.blog(state).data.tags,
    tagMode: selectors.blog(state).data.options.data.tagCloud,
    maxSize: selectors.blog(state).data.options.data.maxTag
  }),
  dispatch => ({
    searchByTag: (tag) => {
      dispatch(listActions.addFilter(selectors.STORE_NAME+'.posts', 'tags', tag))
      dispatch(postActions.initDataList())
    },
    goHome: (history) => {
      history.push('/')
    }
  })
)(TagsComponent))

export {Tags}
