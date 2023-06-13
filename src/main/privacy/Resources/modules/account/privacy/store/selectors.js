const STORE_NAME = 'accountPrivacy'

const selectPrivacyData = (state) => state[STORE_NAME].privacyData

export const selectors = {
  STORE_NAME,
  selectPrivacyData
}
