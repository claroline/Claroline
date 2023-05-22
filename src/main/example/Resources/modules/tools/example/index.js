
import {ExampleTool} from '#/main/example/tools/example/containers/tool'
import {ExampleMenu} from '#/main/example/tools/example/components/menu'
import {reducer} from '#/main/example/tools/example/store'

export default {
  component: ExampleTool,
  menu: ExampleMenu,
  store: reducer
}
