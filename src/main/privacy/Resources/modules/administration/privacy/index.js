import {PrivacyTool} from '#/main/privacy/administration/privacy/containers/tool'
import {PrivacyMenu} from '#/main/privacy/administration/privacy/components/menu'
import {reducer} from '#/main/privacy/administration/privacy/store'

export default {
  component: PrivacyTool,
  menu: PrivacyMenu,
  store: reducer
}
