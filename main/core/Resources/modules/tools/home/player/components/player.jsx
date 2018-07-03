import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'
import {WidgetGrid} from '#/main/core/widget/player/components/grid'

import {select} from '#/main/core/tools/home/selectors'

const PlayerComponent = props =>
  <WidgetGrid
    context={props.context}
    widgets={props.widgets}
  />

PlayerComponent.propTypes = {
  context: T.object.isRequired,
  widgets: T.arrayOf(T.shape(
    WidgetContainerTypes.propTypes
  )).isRequired
}

const Player = connect(
  (state) => ({
    context: select.context(state),
    widgets: select.widgets(state)
  })
)(PlayerComponent)

export {
  Player
}
