import {makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {TOOL_LOAD} from '#/main/core/tool/store'
import {
  PRIVACY_UPDATE_COUNTRY,
  PRIVACY_UPDATE_TERMS,
  PRIVACY_UPDATE_TERMS_ENABLED,
  PRIVACY_UPDATE_DPO
} from '#/main/privacy/administration/privacy/store/actions'

const reducer = makeReducer({}, {
  [makeInstanceAction(TOOL_LOAD, 'privacy')]: (state, action) => action.toolData.parameters,
  [PRIVACY_UPDATE_COUNTRY]: (state, action) => action.countryStorage,
  [PRIVACY_UPDATE_TERMS]: (state, action) => action.termsOfService,
  [PRIVACY_UPDATE_TERMS_ENABLED]: (state, action) => action.termsOfServiceEnabled,
  [PRIVACY_UPDATE_DPO]: (state, action) => action.dpo
})

export {reducer}
