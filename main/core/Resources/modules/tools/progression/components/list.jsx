import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {url} from '#/main/app/api'

import {ProgressionItem as ProgressionItemType} from '#/main/core/tools/progression/prop-types'

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
      >
      </div>
    )}

    <div className={classes('progression-row-content', {'root-content': 0 === props.item.level})}>
      {props.item.openingUrl ?
        <a
          href={url(props.item.openingUrl)}
          className="progression-opening-url"
        >
          {props.item.name}
        </a> :
        props.item.name
      }
    </div>
  </li>

Row.propTypes = {
  item: T.shape(ProgressionItemType.propTypes).isRequired
}

const List = props =>
  <ul className="progression-list">
    {props.items.filter(item => item.level <= props.levelMax).map((item, itemIndex) =>
      <Row
        key={itemIndex}
        item={item}
      />
    )}
  </ul>

List.propTypes = {
  items: T.arrayOf(T.shape(ProgressionItemType.propTypes)).isRequired,
  levelMax: T.number.isRequired
}

List.defaultProps = {
  items: [],
  levelMax: 1
}

export {
  List
}
