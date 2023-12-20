import {PrivacyTool} from '#/main/privacy/administration/privacy/components/tool'
import {PrivacyMenu} from '#/main/privacy/administration/privacy/components/menu'
import {reducer} from '#/main/privacy/administration/privacy/store/reducer'

export default {
  component: PrivacyTool,
  menu: PrivacyMenu,
  store: reducer
}
