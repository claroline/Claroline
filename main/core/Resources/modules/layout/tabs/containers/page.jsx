
import {connectPage} from '#/main/core/layout/page/connect'
import {TabbedPage} from '#/main/core/layout/tabs/components/page.jsx'

/**
 * Connected container for tabbed pages.
 *
 * Connects the <TabbedPage> component to a redux store.
 * If you don't use redux in your implementation @see TabbedPage functional component.
 *
 * To use with `makePageReducer()`
 *
 * @param props
 * @constructor
 */
const TabbedPageContainer = connectPage()(TabbedPage)

export {
  TabbedPageContainer
}
