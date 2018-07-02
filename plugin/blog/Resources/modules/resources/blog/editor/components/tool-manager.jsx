import React from 'react'
import {connect} from 'react-redux'
import {ToolConfig} from '#/plugin/blog/resources/blog/editor/components/tool-config.jsx'
import {trans} from '#/main/core/translation'
import {PropTypes as T} from 'prop-types'
import differenceBy from 'lodash/differenceBy'

const ToolManagerComponent = props =>
  <div>
    <ul className="list-unstyled">
      {props.orderedPanels && props.orderedPanels.map((panel, index) =>(
        React.createElement(ToolConfig, {
          key: index,
          index: index,
          max: props.panels.length - 1,
          label: trans(panel.nameTemplate, {}, 'icap_blog'),
          templateName: panel.nameTemplate,
          visibility: panel.visibility,
          id: trans(panel.id, {}, 'icap_blog')
        })       
      ))}
      {props.panelDiff && props.panelDiff.map((panel, index) =>(
        React.createElement(ToolConfig, {
          key: index + props.orderedPanelsSize,
          index: index + props.orderedPanelsSize,
          max: props.panels.length - 1,
          label: trans(panel.nameTemplate, {}, 'icap_blog'),
          templateName: panel.nameTemplate,
          visibility: false,
          id: trans(panel.id, {}, 'icap_blog')
        })       
      ))}
    </ul>
  </div>

ToolManagerComponent.propTypes = {
  panels: T.array.isRequired,
  orderedPanels: T.array.isRequired,
  orderedPanelsSize: T.number.isRequired,
  panelDiff: T.array
}

const ToolManager = connect(
  state => ({
    orderedPanels: state.blog.data.options.data.widgetOrder,
    orderedPanelsSize: state.blog.data.options.data.widgetOrder.length,
    panels: state.blog.data.options.data.widgetList,
    panelDiff: differenceBy(state.blog.data.options.data.widgetList, state.blog.data.options.data.widgetOrder, 'id')
  })
)(ToolManagerComponent)

export {ToolManager}