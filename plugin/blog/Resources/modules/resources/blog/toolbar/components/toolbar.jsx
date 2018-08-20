import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {getComponentByPanelLabel} from '#/plugin/blog/resources/blog/toolbar/utils'
import {selectors} from '#/plugin/blog/resources/blog/store'

const ToolsComponent = (props) =>
  <div>
    {props.panels && props.panels.map((panel, index) =>(
      <div key={index}>
        {panel.visibility &&
          React.createElement(getComponentByPanelLabel(panel.nameTemplate), {
            key: index
          })
        }
      </div>
    ))}
  </div>

ToolsComponent.propTypes = {
  blogId: T.string,
  panels: T.array
}

const Tools = connect(
  state => ({
    blogId: selectors.blog(state).data.id,
    panels: selectors.blog(state).data.options.data.widgetOrder
  })
)(ToolsComponent)

export {Tools}
