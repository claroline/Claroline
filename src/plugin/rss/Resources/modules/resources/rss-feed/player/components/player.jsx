import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {Alert} from '#/main/app/components/alert'
import {hasPermission} from '#/main/app/security'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {selectors as resourceSelect} from '#/main/core/resource/store'
import {selectors} from '#/plugin/rss/resources/rss-feed/player/store/selectors'

const PlayerComponent = props => {

  if (0 === props.items.length) {
    return (
      <ContentPlaceholder
        size="lg"
        icon="fa fa-image"
        title={trans('no_item', {}, 'rss')}
      />
    )
  }

  return (
    <div className="feed-rss">
      {props.canEdit &&
        <Alert type="warning" title={trans('deprecated_resource', {}, 'platform')} className="component-container">
          {trans('deprecated_resource_message', {}, 'platform')}
        </Alert>
      }

      {props.items.map((item, index) =>
        <div className="feed-item" key={index}>
          <h4 className="feed-item-title">
            <a href={item.link} rel="noopener noreferrer" target="_blank">{item.title}</a>
            {/* {item.date} */}
          </h4>
          <p className="feed-item-desc" dangerouslySetInnerHTML={{ __html:item.description }}/>
        </div>
      )}
    </div>
  )
}

PlayerComponent.propTypes = {
  items: T.arrayOf(T.shape({
    // TODO
  })).isRequired,
  canEdit: T.bool.isRequired
}

const Player = connect(
  (state) => ({
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    items: selectors.items(state)
  })
)(PlayerComponent)

export {
  Player
}
