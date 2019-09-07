import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors} from '#/plugin/analytics/tools/dashboard/store'
import {ProgressionItem as ProgressionItemType} from '#/plugin/analytics/tools/dashboard/prop-types'
import {ProgressionList} from '#/plugin/analytics/tools/dashboard/components/progression-list'

const ProgressionComponent = (props) =>
  <ProgressionList
    items={props.items}
    levelMax={props.levelMax}
  />

ProgressionComponent.propTypes = {
  items: T.arrayOf(T.shape(ProgressionItemType.propTypes)),
  levelMax: T.number
}

const Progression = connect(
  state => ({
    items: selectors.items(state),
    levelMax: selectors.levelMax(state)
  })
)(ProgressionComponent)

export {
  Progression
}
