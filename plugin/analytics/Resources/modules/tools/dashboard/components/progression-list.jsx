import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {LinkButton} from '#/main/app/buttons/link/components/button'

import {route as resourceRoute} from '#/main/core/resource/routing'

import {ProgressionItem as ProgressionItemType} from '#/plugin/analytics/tools/dashboard/prop-types'

const Row = props =>
  <li className="progression-row-container">
    <span className={classes('progression-row-icon', {
      'fa fa-check-circle-o': props.item.validated,
      'progression-row-no-icon': !props.item.validated
    })} />

    {0 < props.item.level && Array.from(Array(props.item.level).keys()).map(key =>
      <div
        key={`indent-${props.item.id}-${key}`}
        className="progression-indent"
      />
    )}

    <div className={classes('progression-row-content', {'root-content': 0 === props.item.level})}>
      <LinkButton
        className="progression-opening-url"
        target={resourceRoute(props.item)}
      >
        {props.item.name}
      </LinkButton>
    </div>
  </li>

Row.propTypes = {
  item: T.shape(ProgressionItemType.propTypes).isRequired
}

const ProgressionList = props =>
  <ul className="progression-list">
    {props.items.filter(item => null === props.levelMax || item.level <= props.levelMax).map((item, itemIndex) =>
      <Row
        key={itemIndex}
        item={item}
      />
    )}
  </ul>

ProgressionList.propTypes = {
  items: T.arrayOf(T.shape(ProgressionItemType.propTypes)).isRequired,
  levelMax: T.number
}

ProgressionList.defaultProps = {
  items: [],
  levelMax: 1
}

export {
  ProgressionList
}
