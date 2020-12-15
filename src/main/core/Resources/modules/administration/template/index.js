import {reducer} from '#/main/core/administration/template/store'
import {TemplateTool} from '#/main/core/administration/template/containers/tool'
import {TemplateMenu} from '#/main/core/administration/template/components/menu'

export default {
  component: TemplateTool,
  menu: TemplateMenu,
  store: reducer
}
