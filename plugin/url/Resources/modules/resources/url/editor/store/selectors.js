import {selectors as urlSelectors} from '#/plugin/url/resources/url/store/selectors'

const FORM_NAME = `${urlSelectors.STORE_NAME}.urlForm`

const url = (state) => urlSelectors.url(state)

export const selectors = {
  FORM_NAME,
  url
}
