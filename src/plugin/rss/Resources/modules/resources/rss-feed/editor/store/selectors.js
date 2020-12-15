
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {selectors as baseSelectors} from '#/plugin/rss/resources/rss-feed/store/selectors'

const FORM_NAME = `${baseSelectors.STORE_NAME}.rssFeedForm`

const rssFeed = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

export const selectors = {
  FORM_NAME,
  rssFeed
}
