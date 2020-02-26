import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {EmptyPlaceholder} from '#/main/app/content/components/placeholder'

import {selectors} from '#/plugin/rss/resources/rss-feed/player/store/selectors'

const PlayerComponent = props => {

  if (0 === props.items.length) {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-image"
        title={trans('no_item', {}, 'rss')}
      />
    )
  }

  return (
    <div className="feed-rss">
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
  })).isRequired
}

const Player = connect(
  (state) => ({
    items: selectors.items(state)
  })
)(PlayerComponent)

export {
  Player
}
