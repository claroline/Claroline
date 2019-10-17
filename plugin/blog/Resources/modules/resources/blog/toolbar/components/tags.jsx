import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {withRouter} from '#/main/app/router'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {ContentTags} from '#/main/app/content/components/tags'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors} from '#/plugin/blog/resources/blog/store/selectors'
import {constants} from '#/plugin/blog/resources/blog/constants'
import {cleanTag} from '#/plugin/blog/resources/blog/utils'
import {updateQueryParameters} from '#/plugin/blog/resources/blog/utils'

const TagsComponent = props =>
  <div key='redactors' className="panel panel-default">
    <div className="panel-heading">
      <h2 className="panel-title">{trans('tagcloud', {}, 'icap_blog')}</h2>
    </div>

    <div className="panel-body">
      {!isEmpty(props.tags) && props.tagMode !== constants.TAGCLOUD_TYPE_LIST &&
        <ContentTags
          tags={props.tags}
          minSize={12}
          maxSize={22}
          onClick={(tag) => {
            props.goHome(props.history, props.path)
            props.searchByTag(props.history, props.location.search, cleanTag(props.tagMode, tag))
          }}
        />
      }

      {!isEmpty(props.tags) && props.tagMode === constants.TAGCLOUD_TYPE_LIST &&
        <ul>
          {props.tags && Object.keys(props.tags).sort().map((tag, index) =>(
            <li key={index} className="list-unstyled">
              <CallbackButton
                className="link"
                callback={() => {
                  props.goHome(props.history, props.path)
                  props.searchByTag(props.history, props.location.search, tag)
                }}
              >
                {tag}
              </CallbackButton>
            </li>
          ))}
        </ul>
      }

      {isEmpty(props.tags) &&
        trans('no_tags', {}, 'icap_blog')
      }
    </div>
  </div>

TagsComponent.propTypes = {
  path: T.string.isRequired,
  searchByTag: T.func.isRequired,
  tags: T.object,
  tagMode: T.string,
  history: T.object,
  goHome: T.func.isRequired,
  maxSize: T.number,
  location: T.object
}

const Tags = withRouter(connect(
  state => ({
    path: resourceSelectors.path(state),
    tags: selectors.blog(state).data.options.data.tagCloud === constants.TAGCLOUD_TYPE_CLASSIC_NUM ? selectors.displayTagsFrequency(state) : selectors.blog(state).data.tags,
    tagMode: selectors.blog(state).data.options.data.tagCloud,
    maxSize: selectors.blog(state).data.options.data.maxTag
  }),
  () => ({
    searchByTag(history, queryString, tag) {
      history.push(updateQueryParameters(queryString, 'tag', tag))
    },
    goHome(history, path) {
      history.push(path)
    }
  })
)(TagsComponent))

export {Tags}
