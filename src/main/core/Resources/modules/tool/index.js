import {ToolPage} from '#/main/core/tool/components/page'
import {ToolMain as Tool} from '#/main/core/tool/components/main'
import {selectors} from '#/main/core/tool/store'

/**
 * Declare a new tool to the application.
 * NB1. Tool MUST be registered in the `plugin.js` file of its plugin.
 * NB2. Tool component tree MUST start with the `Tool` component
 */
function declareTool(ToolComponent, ToolEditorComponent) {
  return {
    component: ToolComponent,
    parameters: ToolEditorComponent
  }
}

// Declare public element of the tool module
export {
  Tool,
  ToolPage,
  selectors,
  declareTool
}
